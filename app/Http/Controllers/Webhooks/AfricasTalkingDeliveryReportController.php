<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAfricasTalkingDeliveryReport;
use App\Models\SmsDeliveryReport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AfricasTalkingDeliveryReportController extends Controller
{
    public function receive(Request $request): Response
    {
        /*
         * Africa's Talking sends this callback as
         * application/x-www-form-urlencoded.
         */
        $validated = $request->validate([
            'id' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:40'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'networkCode' => ['nullable', 'string', 'max:32'],
            'failureReason' => ['nullable', 'string'],
            'retryCount' => ['nullable', 'integer', 'min:0'],
        ]);

        $payload = [
            'id' => trim($validated['id']),
            'status' => trim($validated['status']),
            'phoneNumber' => $validated['phoneNumber'] ?? null,
            'networkCode' => $validated['networkCode'] ?? null,
            'failureReason' => $validated['failureReason'] ?? null,
            'retryCount' => (int) ($validated['retryCount'] ?? 0),
        ];

        /*
         * Generate an idempotency key from the callback data.
         * Repeated identical callbacks will not create duplicate records.
         */
        $eventKey = hash(
            'sha256',
            json_encode($payload, JSON_UNESCAPED_SLASHES)
        );

        $report = SmsDeliveryReport::firstOrCreate(
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

        if ($report->wasRecentlyCreated) {
            ProcessAfricasTalkingDeliveryReport::dispatch($report->id);
        }

        Log::info('Africa\'s Talking SMS delivery report received.', [
            'message_id' => $report->message_id,
            'status' => $report->status,
            'report_id' => $report->id,
        ]);

        return response('Received', 200);
    }
}