<?php
namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLoggerService
{
    public static function log(
        string $logType, 
        ?string $modelType = null, 
        ?string $modelIdentifier = null, 
        ?array $oldValues = null, 
        ?array $newValues = null, 
        ?string $description = null
    ) {
        $user = Auth::user();
        return ActivityLog::create([
            'log_type' => $logType,
            'model_type' => $modelType,
            'model_identifier' => $modelIdentifier,
            'user_identifier' => $user ? ($user->name ?? $user->email) : 'Unknown User',
            'user_email' => $user?->email,
            'description' => $description ?? $logType,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
    }

    public static function query()
    {
        return ActivityLog::query();
    }
}