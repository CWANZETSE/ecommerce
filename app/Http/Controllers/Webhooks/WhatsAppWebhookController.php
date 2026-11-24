<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsAppWebhookEvent;
use App\Models\WhatsAppWebhookEvent;
use App\Services\WhatsApp\WhatsAppWebhookSignatureValidator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use JsonException;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private WhatsAppWebhookSignatureValidator $signatureValidator
    ) {}

    /**
     * Verify the webhook endpoint with Meta.
     */
    public function verify(Request $request): Response
    {
        $mode = $this->getHubParameter($request, 'hub.mode');

        $verifyToken = $this->getHubParameter($request, 'hub.verify_token');

        $challenge = $this->getHubParameter($request, 'hub.challenge');

        $configuredToken = config('whatsapp.webhook_verify_token');

        if (
            $mode === 'subscribe' &&
            ! empty($configuredToken) &&
            $verifyToken !== null &&
            hash_equals($configuredToken, $verifyToken) &&
            $challenge !== null
        ) {
            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Receive webhook events from Meta.
     */
    public function receive(Request $request): Response
    {
        Log::info('WhatsApp webhook POST received.', [
            'content_type' => $request->header('Content-Type'),
            'has_signature' => $request->hasHeader('X-Hub-Signature-256'),
            'payload_size' => strlen($request->getContent()),
        ]);
        if (! $this->signatureValidator->isValid($request)) {
            Log::warning('WhatsApp webhook signature validation failed.', [
                'ip' => $request->ip(),
                'payload_size' => strlen($request->getContent()),
            ]);

            return response('Invalid signature', 401);
        }

        try {
            $payload = json_decode(
                $request->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            return response('Invalid JSON payload', 400);
        }

        if (
            ! is_array($payload) ||
            ($payload['object'] ?? null) !== 'whatsapp_business_account'
        ) {
            return response('Invalid webhook payload', 400);
        }

        $this->storeStatusEvents($payload);

        Log::info('WhatsApp webhook event processed.', [
            'object' => $payload['object'],
            'entries_count' => count($payload['entry'] ?? []),
        ]);

        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Retrieve Meta's hub parameters.
     */
    private function getHubParameter(Request $request, string $parameter): ?string
    {
        $value = $request->query($parameter);

        if ($value !== null) {
            return (string) $value;
        }

        $normalizedParameter = str_replace('.', '_', $parameter);

        $value = $request->query($normalizedParameter);

        if ($value !== null) {
            return (string) $value;
        }

        $rawQuery = $request->server('QUERY_STRING', '');

        foreach (explode('&', $rawQuery) as $segment) {
            if ($segment === '') {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $segment, 2), 2, '');

            if (rawurldecode($key) === $parameter) {
                return rawurldecode(str_replace('+', ' ', $value));
            }
        }

        return null;
    }

    private function storeStatusEvents(array $payload): void
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $statuses = $change['value']['statuses'] ?? [];

                foreach ($statuses as $statusData) {
                    $wamid = $statusData['id'] ?? null;
                    $status = $statusData['status'] ?? null;

                    if (
                        ! is_string($wamid) ||
                        $wamid === '' ||
                        ! in_array($status, [
                            'sent',
                            'delivered',
                            'read',
                            'failed',
                        ], true)
                    ) {
                        continue;
                    }

                    $eventKey = hash(
                        'sha256',
                        json_encode($statusData, JSON_THROW_ON_ERROR)
                    );

                    $event = WhatsAppWebhookEvent::firstOrCreate(
                        ['event_key' => $eventKey],
                        [
                            'wamid' => $wamid,
                            'event_status' => $status,
                            'payload' => [
                                'object' => 'whatsapp_business_account',
                                'entry' => [
                                    [
                                        'changes' => [
                                            [
                                                'field' => 'messages',
                                                'value' => [
                                                    'statuses' => [$statusData],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'processing_status' => 'pending',
                        ]
                    );

                    if ($event->processing_status === 'pending') {
                        ProcessWhatsAppWebhookEvent::dispatch($event->id);
                    }
                }
            }
        }
    }
}
