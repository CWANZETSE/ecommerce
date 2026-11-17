<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Notifications\OrderPaidNotification;
use App\Notifications\PaymentFailedNotification;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MpesaCallbackController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    public function handle(Request $request): JsonResponse
    {
        Log::info('M-Pesa callback: RECEIVED', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'has_query_key' => $request->has('key'),
            'query_key_prefix' => $request->query('key') ? substr($request->query('key'), 0, 6) : null,
            'payload_size' => strlen($request->getContent()),
        ]);

        // 1. Verify callback secret
        $expected = config('mpesa.callback_secret');
        $provided = $request->query('key');

        if (! $expected || ! is_string($provided) || ! hash_equals($expected, $provided)) {
            Log::warning('M-Pesa callback: REJECTED — invalid secret', [
                'ip' => $request->ip(),
                'expected_set' => ! empty($expected),
                'provided_set' => ! empty($provided),
                'match' => $expected && $provided ? hash_equals($expected, $provided) : false,
            ]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Rejected'], 403);
        }

        Log::info('M-Pesa callback: secret verified');

        $payload = $request->all();

        // 2. Extract CheckoutRequestID and result
        $stkCallback = $payload['Body']['stkCallback'] ?? null;
        if (! $stkCallback) {
            Log::warning('M-Pesa callback: MALFORMED — no Body.stkCallback', [
                'payload_keys' => array_keys($payload),
                'body_keys' => isset($payload['Body']) ? array_keys($payload['Body']) : null,
            ]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Malformed'], 400);
        }

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = (int) ($stkCallback['ResultCode'] ?? -1);
        $resultDesc = $stkCallback['ResultDesc'] ?? '';
        $merchantRequestId = $stkCallback['MerchantRequestID'] ?? null;

        Log::info('M-Pesa callback: parsed', [
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => $merchantRequestId,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
        ]);

        if (! $checkoutRequestId) {
            Log::warning('M-Pesa callback: MISSING CheckoutRequestID');

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Missing CheckoutRequestID'], 400);
        }

        // 3. Find the payment
        $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();

        if (! $payment) {
            Log::warning('M-Pesa callback: UNKNOWN CheckoutRequestID — no matching payment', [
                'checkout_request_id' => $checkoutRequestId,
                'recent_payments' => Payment::latest()->limit(5)->pluck('checkout_request_id')->all(),
            ]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Unknown'], 404);
        }

        Log::info('M-Pesa callback: payment matched', [
            'payment_id' => $payment->id,
            'payment_ulid' => $payment->ulid,
            'order_id' => $payment->order_id,
            'order_number' => $payment->order?->order_number,
            'current_status' => $payment->status->value,
        ]);

        if ($payment->status->value === 'succeeded') {
            Log::info('M-Pesa callback: DUPLICATE — payment already succeeded, acknowledging', [
                'payment_id' => $payment->id,
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted (duplicate)']);
        }

        // 4. Record the callback attempt
        PaymentAttempt::create([
            'payment_id' => $payment->id,
            'event' => 'callback',
            'request_payload' => $this->redact($payload),
            'response_payload' => null,
            'status_code' => 200,
        ]);

        Log::info('M-Pesa callback: PaymentAttempt recorded', ['payment_id' => $payment->id]);

        // 5. Process success or failure
        if ($resultCode === 0) {
            Log::info('M-Pesa callback: routing to SUCCESS handler', ['payment_id' => $payment->id]);
            $this->handleSuccess($payment, $stkCallback);
        } else {
            Log::info('M-Pesa callback: routing to FAILURE handler', [
                'payment_id' => $payment->id,
                'result_code' => $resultCode,
            ]);
            $this->handleFailure($payment, $resultCode, $resultDesc);
        }

        Log::info('M-Pesa callback: COMPLETE — returning 200 to Safaricom', [
            'payment_id' => $payment->id,
            'result_code' => $resultCode,
        ]);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    private function handleSuccess(Payment $payment, array $stkCallback): void
    {
        $items = $stkCallback['CallbackMetadata']['Item'] ?? [];
        $receipt = null;
        foreach ($items as $item) {
            if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                $receipt = $item['Value'] ?? null;
                break;
            }
        }

        if (! $receipt) {
            Log::error('M-Pesa callback: SUCCESS but NO RECEIPT in CallbackMetadata', [
                'payment_id' => $payment->id,
                'metadata_items' => array_map(fn ($i) => $i['Name'] ?? '?', $items),
            ]);

            return;
        }

        Log::info('M-Pesa callback: receipt extracted', [
            'payment_id' => $payment->id,
            'receipt' => $receipt,
        ]);

        $order = DB::transaction(function () use ($payment, $receipt) {
            Log::debug('M-Pesa callback: opening transaction for payment lock', ['payment_id' => $payment->id]);

            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if ($locked->status->value === 'succeeded') {
                Log::info('M-Pesa callback: inside lock — already succeeded, skipping', ['payment_id' => $payment->id]);

                return null;
            }

            $locked->markSucceeded($receipt);
            $locked->order->markPaid($receipt);

            Log::info('M-Pesa callback: payment + order marked paid', [
                'payment_id' => $locked->id,
                'order_id' => $locked->order_id,
                'order_number' => $locked->order->order_number,
                'receipt' => $receipt,
            ]);

            foreach ($locked->order->items as $item) {
                if ($item->variation) {
                    $this->inventory->recordSale($item->variation, $item->quantity, $locked->order);
                    Log::debug('M-Pesa callback: stock deducted', [
                        'variation_id' => $item->variation->id,
                        'quantity' => $item->quantity,
                        'order_id' => $locked->order_id,
                    ]);
                }
            }
            // ✅ Clear the original cart — payment is confirmed
            if ($locked->order->cart_id) {
                Cart::whereKey($locked->order->cart_id)
                    ->first()
                    ?->items()
                    ->delete();
            }

            return $locked->order->fresh();
        });

        if ($order) {
            Log::info('M-Pesa callback: transaction committed, sending notification', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            try {
                Notification::route('mail', $order->customer_email)
                    ->notify(new OrderPaidNotification($order));

                Log::info('M-Pesa callback: OrderPaidNotification queued/sent', [
                    'order_id' => $order->id,
                    'email' => $order->customer_email,
                ]);
            } catch (\Throwable $e) {
                Log::warning('M-Pesa callback: OrderPaidNotification failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function handleFailure(Payment $payment, int $resultCode, string $resultDesc): void
    {
        Log::info('M-Pesa callback: handling failure', [
            'payment_id' => $payment->id,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
        ]);

        $order = DB::transaction(function () use ($payment, $resultCode, $resultDesc) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if ($locked->status->value !== 'pending' && $locked->status->value !== 'processing') {
                Log::info('M-Pesa callback: inside lock — payment no longer pending/processing, skipping', [
                    'payment_id' => $locked->id,
                    'status' => $locked->status->value,
                ]);

                return null;
            }

            $locked->markFailed("{$resultCode}: {$resultDesc}");

            Log::info('M-Pesa callback: payment marked failed', [
                'payment_id' => $locked->id,
                'failure_reason' => "{$resultCode}: {$resultDesc}",
            ]);

            return $locked->order->fresh();
        });

        if ($order) {
            try {
                Notification::route('mail', $order->customer_email)
                    ->notify(new PaymentFailedNotification(
                        $order,
                        $resultDesc ?: 'Payment was not completed.',
                    ));

                Log::info('M-Pesa callback: PaymentFailedNotification sent', [
                    'order_id' => $order->id,
                    'email' => $order->customer_email,
                ]);
            } catch (\Throwable $e) {
                Log::warning('M-Pesa callback: PaymentFailedNotification failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function redact(array $payload): array
    {
        $json = json_encode($payload);
        $json = preg_replace('/"(254\d{9})"/', '"254****"'.substr('$1', -3).'"', $json ?? '{}');

        return json_decode($json, true) ?? [];
    }
}
