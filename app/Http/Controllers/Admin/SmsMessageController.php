<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsMessageLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SmsMessageController extends Controller
{
    /**
     * Africa's Talking submission status codes.
     *
     * These describe the provider's response to the submission,
     * not delivery to the recipient's handset.
     */
    private const STATUS_CODES = [
        100 => 'Processed',
        101 => 'Sent',
        102 => 'Queued',
        401 => 'Risk Hold',
        402 => 'Invalid Sender ID',
        403 => 'Invalid Phone Number',
        404 => 'Unsupported Number Type',
        405 => 'Insufficient Balance',
        406 => 'User in Blacklist',
        407 => 'Could Not Route',
        409 => 'Do Not Disturb Rejection',
        500 => 'Internal Server Error',
        501 => 'Gateway Error',
        502 => 'Rejected by Gateway',
    ];

    private const STATUSES = [
        'pending',
        'processed',
        'sent',
        'queued',
        'failed',
    ];

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,processed,sent,queued,failed'],
        ]);

        $messages = SmsMessageLog::query()
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = trim($request->string('search')->toString());

                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery
                        ->where('recipient', 'like', "%{$search}%")
                        ->orWhere('message_id', 'like', "%{$search}%")
                        ->orWhere('notification_type', 'like', "%{$search}%")
                        ->orWhere('failure_reason', 'like', "%{$search}%");
                });
            })
            ->when(
                $request->filled('status'),
                fn (Builder $query) => $query->where('status', $request->status)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (SmsMessageLog $message) => [
                'id' => $message->id,
                'recipient' => $message->recipient,
                'notification_type' => $message->notification_type,
                'message_id' => $message->message_id,
                'status' => $message->status,
                'status_code' => $message->status_code,
                'status_description' => $this->statusDescription($message->status_code),
                'failure_reason' => $message->failure_reason,
                'created_at' => $message->created_at?->format('D d-m-Y H:i:s'),
                'sent_at' => $message->sent_at?->format('D d-m-Y H:i:s'),
            ]);

        return Inertia::render('Admin/AfricasTalking/sms/Index', [
            'messages' => $messages,

            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
            ],

            'statuses' => collect(self::STATUSES)->map(fn ($status) => [
                'value' => $status,
                'label' => ucfirst($status),
            ])->values(),

            'stats' => [
                'total' => SmsMessageLog::count(),

                // Provider submission responses, not handset delivery.
                'accepted' => SmsMessageLog::whereIn('status', [
                    'processed',
                    'sent',
                    'queued',
                ])->count(),

                'failed' => SmsMessageLog::where('status', 'failed')->count(),

                'pending' => SmsMessageLog::where('status', 'pending')->count(),
            ],
        ]);
    }

    public function show(SmsMessageLog $smsMessageLog): Response
    {
        return Inertia::render('Admin/AfricasTalking/sms/Show', [
            'message' => [
                'id' => $smsMessageLog->id,
                'recipient' => $smsMessageLog->recipient,
                'notification_type' => $smsMessageLog->notification_type,
                'notification_id' => $smsMessageLog->notification_id,
                'message_id' => $smsMessageLog->message_id,
                'status' => $smsMessageLog->status,
                'status_code' => $smsMessageLog->status_code,
                'status_description' => $this->statusDescription($smsMessageLog->status_code),
                'cost' => $smsMessageLog->cost,
                'failure_reason' => $smsMessageLog->failure_reason,
                'provider_response' => $smsMessageLog->provider_response,
                'created_at' => $smsMessageLog->created_at?->format('D d-m-Y H:i:s'),
                'sent_at' => $smsMessageLog->sent_at?->format('D d-m-Y H:i:s'),
                'failed_at' => $smsMessageLog->failed_at?->format('D d-m-Y H:i:s'),
            ],
        ]);
    }

    private function statusDescription(?int $statusCode): ?string
    {
        if ($statusCode === null) {
            return null;
        }

        return self::STATUS_CODES[$statusCode] ?? 'Unknown provider status';
    }
}