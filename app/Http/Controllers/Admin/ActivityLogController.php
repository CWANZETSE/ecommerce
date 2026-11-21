<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogFormatter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs with filtering and pagination.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'string', 'exists:users,id'],
            'action' => ['nullable', 'string', 'max:50'],
            'subject_type' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        // Normalize search input.
        $filters['search'] = trim($filters['search'] ?? '');

        // Build the activity logs query.
        $logs = ActivityLog::query()
            ->with('user:id,name')

            // General search.
            ->when(
                $filters['search'] !== '',
                function ($query) use ($filters) {
                    $search = $filters['search'];

                    $query->where(function ($q) use ($search) {
                        $q->where('description', 'like', "%{$search}%")
                            ->orWhere('action', 'like', "%{$search}%")
                            ->orWhere('subject_type', 'like', "%{$search}%")
                            ->orWhere('subject_id', 'like', "%{$search}%")
                            ->orWhere('ip', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                }
            )

            // Filter by user.
            ->when(
                !empty($filters['user_id']),
                fn ($query) => $query->where('user_id', $filters['user_id'])
            )

            // Filter by action.
            ->when(
                !empty($filters['action']),
                fn ($query) => $query->where('action', $filters['action'])
            )

            // Filter by record type.
            ->when(
                !empty($filters['subject_type']),
                fn ($query) => $query->where('subject_type', $filters['subject_type'])
            )

            // Convert selected dates from the application timezone to UTC.
            // This assumes created_at timestamps are stored in UTC.
            ->when(
                !empty($filters['date_from']),
                function ($query) use ($filters) {
                    $dateFrom = Carbon::parse(
                        $filters['date_from'],
                        config('app.timezone')
                    )->startOfDay()->utc();

                    $query->where('created_at', '>=', $dateFrom);
                }
            )

            ->when(
                !empty($filters['date_to']),
                function ($query) use ($filters) {
                    $dateTo = Carbon::parse(
                        $filters['date_to'],
                        config('app.timezone')
                    )->endOfDay()->utc();

                    $query->where('created_at', '<=', $dateTo);
                }
            )

            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        // Format activity log records for frontend display.
        // Original database records remain unchanged.
        $formatter = new ActivityLogFormatter();

        $logs->getCollection()->transform(function ($log) use ($formatter) {
            $log->from_records = $formatter->format($log->from_records);
            $log->to_records = $formatter->format($log->to_records);
            $log->properties = $formatter->format($log->properties);

            return $log;
        });

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,

            // Users who have activity log records.
            'users' => User::query()
                ->whereIn(
                    'id',
                    ActivityLog::query()
                        ->select('user_id')
                        ->whereNotNull('user_id')
                        ->distinct()
                )
                ->orderBy('name')
                ->get(['id', 'name']),

            // Available actions.
            'actions' => ActivityLog::query()
                ->whereNotNull('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),

            // Available record types.
            'subjectTypes' => ActivityLog::query()
                ->whereNotNull('subject_type')
                ->distinct()
                ->orderBy('subject_type')
                ->pluck('subject_type'),

            // Return active filter values to Vue.
            'filters' => [
                'search' => $filters['search'],
                'user_id' => $filters['user_id'] ?? '',
                'action' => $filters['action'] ?? '',
                'subject_type' => $filters['subject_type'] ?? '',
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ],
        ]);
    }
}
