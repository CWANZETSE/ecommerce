<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\Payment\MpesaGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReconcileMpesaPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $paymentId) {}

    public function handle(MpesaGateway $gateway): void
    {
        $payment = Payment::whereKey($this->paymentId)->first();
        if (! $payment || $payment->status->value !== 'processing') {
            return;
        }

        $checkoutId = $payment->checkout_request_id;
        if (! $checkoutId) {
            return;
        }

        $status = $gateway->queryStatus($checkoutId);

        if ($status === null) {
            // Query failed; leave the payment processing, let the scheduler retry
            return;
        }

        $resultCode = (int) ($status['ResultCode'] ?? -1);
        $resultDesc = $status['ResultDesc'] ?? '';

        if ($resultCode === 0) {
            // Payment succeeded but callback never arrived (or arrived late)
            $receipt = $status['MpesaReceiptNumber'] ?? null;
            if ($receipt) {
                $payment->markSucceeded($receipt);
                $payment->order->markPaid($receipt);

                foreach ($payment->order->items as $item) {
                    if ($item->variation) {
                        app(\App\Services\InventoryService::class)
                            ->recordSale($item->variation, $item->quantity, $payment->order);
                    }
                }
            }
        } elseif (in_array($resultCode, [1032, 1037, 1031, 2001, 1001], true)) {
            // Definitively failed — cancel the order
            $payment->markFailed("{$resultCode}: {$resultDesc}");
            $payment->order->update([
                'payment_status' => \App\Enums\OrderPaymentStatus::Cancelled,
                'cancelled_at' => now(),
            ]);
            $payment->order->recordStatus('pending', 'cancelled', 'payment', $resultDesc);
            $order = $payment->order;

            $order->loadMissing('user');

            $customer = $order->user;

            if (!$customer) {
                throw new \RuntimeException(
                    "Customer account not found for order {$order->order_number}."
                );
            }

            $customer->notify(
                new \App\Notifications\PaymentFailedNotification(
                    $order,
                    $resultDesc
                )
            );
        }
        // Any other code: leave pending for manual review
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ReconcileMpesaPayment failed permanently', [
            'payment_id' => $this->paymentId,
            'error' => $e->getMessage(),
        ]);
    }
}