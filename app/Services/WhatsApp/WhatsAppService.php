<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class WhatsAppService
{
    /**
     * Send a WhatsApp message to a single recipient.
     *
     * Meta's Cloud API sends one recipient per API request.
     */
    public function send(
        string $recipient,
        WhatsAppMessage $message,
    ): array {
        $recipient = $this->normalizeRecipient($recipient);

        $accessToken = config('whatsapp.access_token');
        $phoneNumberId = config('whatsapp.phone_number_id');
        $baseUrl = config('whatsapp.base_url');
        $graphVersion = config('whatsapp.graph_version');

        /*
        |--------------------------------------------------------------------------
        | Validate Configuration
        |--------------------------------------------------------------------------
        */

        if (empty($accessToken)) {
            throw new RuntimeException(
                'WhatsApp access token is not configured.'
            );
        }

        if (empty($phoneNumberId)) {
            throw new RuntimeException(
                'WhatsApp phone number ID is not configured.'
            );
        }

        if (empty($baseUrl) || empty($graphVersion)) {
            throw new RuntimeException(
                'WhatsApp Graph API configuration is incomplete.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Build API Endpoint
        |--------------------------------------------------------------------------
        */

        $endpoint = sprintf(
            '%s/%s/%s/messages',
            rtrim($baseUrl, '/'),
            trim($graphVersion, '/'),
            $phoneNumberId,
        );

        /*
        |--------------------------------------------------------------------------
        | Build Request Payload
        |--------------------------------------------------------------------------
        */

        $payload = array_merge(
            [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $recipient,
            ],
            $message->toArray(),
        );

        /*
        |--------------------------------------------------------------------------
        | Send WhatsApp Message
        |--------------------------------------------------------------------------
        */

        $response = Http::timeout(
            (int) config('whatsapp.timeout', 30)
        )
            ->acceptJson()
            ->asJson()
            ->withToken($accessToken)
            ->post($endpoint, $payload);

        /*
        |--------------------------------------------------------------------------
        | Handle API Errors
        |--------------------------------------------------------------------------
        */

        if ($response->failed()) {
            $error = $response->json('error');

            $errorMessage = $error['message']
                ?? $response->body()
                ?: 'Unknown WhatsApp API error.';

            $errorCode = $error['code'] ?? null;

            throw new RuntimeException(
                'WhatsApp API request failed'
                . ($errorCode ? " [Code: {$errorCode}]" : '')
                . ": {$errorMessage}",
                (int) ($errorCode ?? 0),
            );
        }

        $result = $response->json();

        if (!is_array($result) || empty($result['messages'][0]['id'])) {
            throw new RuntimeException(
                'WhatsApp API returned an unexpected response.'
            );
        }

        return $result;
    }

    /**
     * Normalize and validate a WhatsApp recipient number.
     *
     * Accepts numbers such as:
     * +254712345678
     * 254712345678
     * 254 712 345 678
     */
    protected function normalizeRecipient(string $recipient): string
    {
        $recipient = trim($recipient);

        if ($recipient === '') {
            throw new InvalidArgumentException(
                'WhatsApp recipient number cannot be empty.'
            );
        }

        // Remove common phone number formatting characters.
        $recipient = preg_replace('/[\s()+.-]/', '', $recipient);

        if (
            !is_string($recipient)
            || !preg_match('/^[1-9][0-9]{7,14}$/', $recipient)
        ) {
            throw new InvalidArgumentException(
                'Invalid WhatsApp recipient number. Use international format with country code.'
            );
        }

        return $recipient;
    }
}
