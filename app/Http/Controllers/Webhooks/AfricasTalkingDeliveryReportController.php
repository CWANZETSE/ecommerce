<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAfricasTalkingDeliveryReport;
use App\Models\SmsDeliveryReport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AfricasTalkingDeliveryReportController extends Controller
{
    public function receive(Request $request): Response
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:40'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'networkCode' => ['nullable', 'string', 'max:32'],
            'failureReason' => ['nullable', 'string', 'max:5000'],
            'retryCount' => ['nullable', 'integer', 'min:0'],
        ]);

        $payload = [
            'id' => trim($validated['id']),
            'status' => trim($validated['status']),
            'phoneNumber' => isset($validated['phoneNumber'])
                ? trim($validated['phoneNumber'])
                : null,
            'networkCode' => isset($validated['networkCode'])
                ? trim($validated['networkCode'])
                : null,
            'failureReason' => $validated['failureReason'] ?? null,
            'retryCount' => (int) ($validated['retryCount'] ?? 0),
        ];

        if ($payload['id'] === '' || $payload['status'] === '') {
            return response('Invalid callback', 422);
        }

        /*
         * Identical callback payloads produce the same event key.
         */
        $eventKey = hash(
            'sha256',
            json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)
        );

        [$report, $shouldDispatch] = DB::transaction(
            function () use ($eventKey, $payload): array {
                $report = SmsDeliveryReport::query()->firstOrCreate(
                    ['event_key' => $eventKey],
                    [
                        'message_id' => $payload['id'],
                        'status' => $payload['status'],
                        'phone_number' => $payload['phoneNumber'],
                        'network_code' => $payload['networkCode'],
                        'failure_reason' => $payload['failureReason'],
                        'retry_count' => $payload['retryCount'],
                        'payload' => $payload,
                        'processing_status' => 'pending',
                    ]
                );

                $shouldDispatch = $report->wasRecentlyCreated;

                /*
                 * If a previous job exhausted its retries, allow an
                 * identical callback to make the report retryable.
                 */
                if (
                    !$shouldDispatch &&
                    $report->processing_status === 'failed'
                ) {
                    $report->update([
                        'processing_status' => 'pending',
                        'processed_at' => null,
                        'processing_error' => null,
                    ]);

                    $shouldDispatch = true;
                }

                return [$report, $shouldDispatch];
            }
        );

        if ($shouldDispatch) {
            ProcessAfricasTalkingDeliveryReport::dispatch($report->id);
        }

        Log::info(
            'Africa\'s Talking SMS delivery report received.',
            [
                'message_id' => $report->message_id,
                'status' => $report->status,
                'report_id' => $report->id,
                'processing_status' => $report->processing_status,
            ]
        );

        return response('Received', 200);
    }
}
