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
        bool $enqueue = true,
    ): array {
        $recipients = is_array($recipients)
            ? $recipients
            : [$recipients];

        $recipients = array_values(array_filter(
            array_map(
                fn ($recipient) => trim((string) $recipient),
                $recipients
            ),
            fn ($recipient) => $recipient !== ''
        ));

        if (empty($recipients)) {
            throw new RuntimeException(
                'At least one SMS recipient is required.'
            );
        }

        $username = config('africastalking.username');
        $apiKey = config('africastalking.api_key');

        if (empty($username)) {
            throw new RuntimeException(
                'Africa\'s Talking username is not configured.'
            );
        }

        if (empty($apiKey)) {
            throw new RuntimeException(
                'Africa\'s Talking API key is not configured.'
            );
        }

        $senderId ??= config('africastalking.sender_id');

        if (empty($senderId)) {
            throw new RuntimeException(
                'Africa\'s Talking sender ID is not configured.'
            );
        }

        $endpoint = config('africastalking.endpoint');

        if (empty($endpoint)) {
            throw new RuntimeException(
                'Africa\'s Talking SMS endpoint is not configured.'
            );
        }

        $payload = [
            'username' => $username,
            'phoneNumbers' => implode(',', $recipients),
            'message' => $message,
            'senderId' => $senderId,
            'enqueue' => $enqueue ? 1 : 0,
        ];

        $response = Http::timeout(
            config('africastalking.timeout', 15)
        )
            ->withHeaders([
                'Apikey' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->asJson()
            ->post($endpoint, $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                'Africa\'s Talking SMS request failed: '
                . $response->body()
            );
        }

        $result = $response->json();

        if (
            !is_array($result)
            || !isset($result['SMSMessageData'])
        ) {
            throw new RuntimeException(
                'Invalid response received from Africa\'s Talking: '
                . $response->body()
            );
        }

        return $result;
    }
}
