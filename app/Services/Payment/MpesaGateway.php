<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Starnerz\LaravelDaraja\Facades\Daraja;

class MpesaGateway
{
    /**
     * Initiate STK Push and return the Daraja response.
     * Normalizes the phone to 2547XXXXXXXX before sending.
     */
    public function initiate(Payment $payment, string $phone, string $accountReference): array
    {
        $normalized = $this->normalizePhone($phone);
        $amount = (int) round((float) $payment->amount);

        $response = Daraja::stk()->push(
            phone: $normalized,
            amount: $amount,
            accountReference: $accountReference,
            description: 'Order payment',
        );

        return $this->toArray($response);
    }

    /**
     * Query Daraja for the status of a CheckoutRequestID.
     * Used by the reconciliation job for timeouts.
     */
    public function queryStatus(string $checkoutRequestId): ?array
    {
        try {
            $response = Daraja::stk()->query($checkoutRequestId);
            return $this->toArray($response);
        } catch (\Throwable $e) {
            Log::warning('M-Pesa status query failed', [
                'checkout_request_id' => $checkoutRequestId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Normalize Kenyan phone formats to 2547XXXXXXXX.
     * Accepts: 0712345678, +254712345678, 254712345678, with spaces.
     */
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '254' . substr($digits, 1);
        } elseif (str_starts_with($digits, '7') && strlen($digits) === 9) {
            $digits = '254' . $digits;
        } elseif (str_starts_with($digits, '254')) {
            // already normalized
        } elseif (str_starts_with($digits, '+254')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 12) {
            throw new \InvalidArgumentException("Invalid Kenyan phone number: {$phone}");
        }

        return $digits;
    }

    private function toArray(mixed $response): array
    {
        if (is_array($response)) {
            return $response;
        }
        if (method_exists($response, 'toArray')) {
            return $response->toArray();
        }
        return (array) $response;
    }
}