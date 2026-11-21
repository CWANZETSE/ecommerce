<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LogService
{
    public function log(
        string $action,
        Model $subject,
        array $properties = [],
        ?string $description = null,
        ?array $fromRecords = null,
        ?array $toRecords = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => class_basename($subject),
            'subject_id' => $subject->getKey(),
            'properties' => $properties,
            'from_records' => $fromRecords,
            'to_records' => $toRecords,
            'ip' => request()->ip(),
        ]);
    }
}