<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessageLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WhatsAppMessageController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', WhatsAppMessageLog::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                'string',
                Rule::in([
                    'pending',
                    'sent',
                    'delivered',
                    'read',
                    'failed',
                ]),
            ],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50])],
        ]);

        $query = WhatsAppMessageLog::query();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('recipient', 'like', "%{$search}%")
                    ->orWhere('wamid', 'like', "%{$search}%")
                    ->orWhere('notification_type', 'like', "%{$search}%")
                    ->orWhere('template_name', 'like', "%{$search}%")
                    ->orWhere('error_message', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $perPage = (int) ($filters['per_page'] ?? 10);

        $messages = $query
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (WhatsAppMessageLog $message) => [
                'id' => $message->id,
                'recipient' => $message->recipient,
                'wamid' => $message->wamid,
                'notification_type' => $message->notification_type
                    ? class_basename($message->notification_type)
                    : null,
                'message_type' => $message->message_type,
                'template_name' => $message->template_name,
                'status' => $message->status,
                'error_code' => $message->error_code,
                'error_message' => $message->error_message,
                'sent_at' => $message->sent_at?->toISOString(),
                'delivered_at' => $message->delivered_at?->toISOString(),
                'read_at' => $message->read_at?->toISOString(),
                'failed_at' => $message->failed_at?->toISOString(),
                'created_at' => $message->created_at?->toISOString(),
            ]);

        $statusCounts = WhatsAppMessageLog::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Admin/WhatsAppMessages/Index', [
            'messages' => $messages,

            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? 'all',
                'from' => $filters['from'] ?? '',
                'to' => $filters['to'] ?? '',
                'per_page' => $perPage,
            ],

            'statistics' => [
                'total' => WhatsAppMessageLog::count(),
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'sent' => (int) ($statusCounts['sent'] ?? 0),
                'delivered' => (int) ($statusCounts['delivered'] ?? 0),
                'read' => (int) ($statusCounts['read'] ?? 0),
                'failed' => (int) ($statusCounts['failed'] ?? 0),
            ],
        ]);
    }

    public function show(WhatsAppMessageLog $whatsappMessageLog): Response
    {
        $this->authorize('view', $whatsappMessageLog);

        return Inertia::render('Admin/WhatsAppMessages/Show', [
            'message' => [
                'id' => $whatsappMessageLog->id,
                'recipient' => $whatsappMessageLog->recipient,
                'wamid' => $whatsappMessageLog->wamid,
                'notification_id' => $whatsappMessageLog->notification_id,
                'notification_type' => $whatsappMessageLog->notification_type
                    ? class_basename($whatsappMessageLog->notification_type)
                    : null,
                'message_type' => $whatsappMessageLog->message_type,
                'template_name' => $whatsappMessageLog->template_name,
                'status' => $whatsappMessageLog->status,
                'error_code' => $whatsappMessageLog->error_code,
                'error_message' => $whatsappMessageLog->error_message,
                'provider_response' => $whatsappMessageLog->provider_response,
                'sent_at' => $whatsappMessageLog->sent_at?->toISOString(),
                'delivered_at' => $whatsappMessageLog->delivered_at?->toISOString(),
                'read_at' => $whatsappMessageLog->read_at?->toISOString(),
                'failed_at' => $whatsappMessageLog->failed_at?->toISOString(),
                'created_at' => $whatsappMessageLog->created_at?->toISOString(),
                'updated_at' => $whatsappMessageLog->updated_at?->toISOString(),
            ],
        ]);
    }
}
