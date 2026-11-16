<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
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
        // 1. Verify callback secret
        $expected = config('mpesa.callback_secret');
        $provided = $request->query('key');

        if (! $expected || ! is_string($provided) || ! hash_equals($expected, $provided)) {
            Log::warning('M-Pesa callback rejected: invalid secret', ['ip' => $request->ip()]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Rejected'], 403);
        }

        $payload = $request->all();

        // 2. Extract CheckoutRequestID and result
        $stkCallback = $payload['Body']['stkCallback'] ?? null;
        if (! $stkCallback) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Malformed'], 400);
        }

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = (int) ($stkCallback['ResultCode'] ?? -1);
        $resultDesc = $stkCallback['ResultDesc'] ?? '';

        if (! $checkoutRequestId) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Missing CheckoutRequestID'], 400);
        }

        // 3. Find the payment. Idempotency: if already succeeded, return 200.
        $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();

        if (! $payment) {
            Log::warning('M-Pesa callback for unknown CheckoutRequestID', ['id' => $checkoutRequestId]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Unknown'], 404);
        }

        if ($payment->status->value === 'succeeded') {
            // Duplicate callback — acknowledge without reprocessing
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

        // 5. Process success or failure
        if ($resultCode === 0) {
            $this->handleSuccess($payment, $stkCallback);
        } else {
            $this->handleFailure($payment, $resultCode, $resultDesc);
        }

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
            Log::error('M-Pesa callback success without receipt', ['payment_id' => $payment->id]);

            return;
        }

        $order = DB::transaction(function () use ($payment, $receipt) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if ($locked->status->value === 'succeeded') {
                return null;
            }

            $locked->markSucceeded($receipt);
            $locked->order->markPaid($receipt);

            foreach ($locked->order->items as $item) {
                if ($item->variation) {
                    $this->inventory->recordSale($item->variation, $item->quantity, $locked->order);
                }
            }

            return $locked->order->fresh();
        });

        if ($order) {
            try {
                Notification::route('mail', $order->customer_email)
                    ->notify(new OrderPaidNotification($order));
            } catch (\Throwable $e) {
                Log::warning('OrderPaidNotification failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function handleFailure(Payment $payment, int $resultCode, string $resultDesc): void
    {
        $order = DB::transaction(function () use ($payment, $resultCode, $resultDesc) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if ($locked->status->value !== 'pending' && $locked->status->value !== 'processing') {
                return;
            }

            $locked->markFailed("{$resultCode}: {$resultDesc}");
            // Order remains pending; customer can retry or the reconciliation job will clean up
        });

        if ($order) {
            try {
                Notification::route('mail', $order->customer_email)
                    ->notify(new PaymentFailedNotification(
                        $order,
                        $resultDesc ?: 'Payment was not completed.',
                    ));
            } catch (\Throwable $e) {
                Log::warning('PaymentFailedNotification failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function redact(array $payload): array
    {
        // Mask phone numbers in stored payloads
        $json = json_encode($payload);
        $json = preg_replace('/"(254\d{9})"/', '"254****"'.substr('$1', -3).'"', $json ?? '{}');

        return json_decode($json, true) ?? [];
    }
}
