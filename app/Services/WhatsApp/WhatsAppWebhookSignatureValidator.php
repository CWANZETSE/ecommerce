<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Request;

class WhatsAppWebhookSignatureValidator
{
    //     For every incoming POST request:
    // 1. Laravel retrieves the raw request body using $request->getContent().
    // 2. It retrieves your Meta App Secret from configuration.
    // 3. It calculates the expected HMAC-SHA256 signature.
    // 4. It extracts the signature supplied in the request header.
    // 5. hash_equals() compares both signatures safely.
    // We use the raw request body because even a small change to the JSON content produces a different signature.

    // Important: Do not decode and re-encode the JSON before validating the signature.
    /**
     * Validate the signature sent by Meta. signature validation before accepting any POST webhook event. Meta signs webhook POST requests using HMAC-SHA256 and your App Secret.
     */
    public function isValid(Request $request): bool
    {
        $appSecret = config('whatsapp.app_secret');

        if (empty($appSecret)) {
            return false;
        }

        $signature = $request->header('X-Hub-Signature-256');

        if (
            ! is_string($signature) ||
            ! preg_match('/^sha256=([a-f0-9]{64})$/i', $signature, $matches)
        ) {
            return false;
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $request->getContent(),
            $appSecret
        );

        return hash_equals($expectedSignature, strtolower($matches[1]));
    }
}
