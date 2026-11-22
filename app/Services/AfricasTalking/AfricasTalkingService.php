<?php

namespace App\Services\AfricasTalking;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AfricasTalkingService
{
    public function send(
        array|string $recipients,
        string $message,
        ?string $senderId = null,
        bool $enqueue = false,
    ): array {
        $recipients = is_array($recipients)
            ? $recipients
            : [$recipients];

        $recipients = array_values(array_filter($recipients));

        if (empty($recipients)) {
            throw new RuntimeException(
                'At least one SMS recipient is required.'
            );
        }

        if (empty(config('africastalking.username'))) {
            throw new RuntimeException(
                'Africa\'s Talking username is not configured.'
            );
        }

        if (empty(config('africastalking.api_key'))) {
            throw new RuntimeException(
                'Africa\'s Talking API key is not configured.'
            );
        }

        $payload = [
            'username' => config('africastalking.username'),
            'to' => implode(',', $recipients),
            'message' => $message,
            'enqueue' => $enqueue,
        ];

        $senderId ??= config('africastalking.sender_id');

        if ($senderId) {
            $payload['from'] = $senderId;
        }

        $response = Http::timeout(
            config('africastalking.timeout', 15)
        )
            ->withHeaders([
                'Apikey' => config('africastalking.api_key'),
                'Accept' => 'application/json',
            ])
            ->asForm()
            ->post(
                rtrim(config('africastalking.base_url'), '/')
                    . '/version1/messaging',
                $payload
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Africa\'s Talking SMS request failed: '
                . $response->body()
            );
        }

        $result = $response->json();

        if (!isset($result['SMSMessageData'])) {
            throw new RuntimeException(
                'Invalid response received from Africa\'s Talking.'
            );
        }

        return $result;
    }
}
