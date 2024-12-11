<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('Create');
        });

        static::updated(function ($model) {
            $model->logActivity('Update', $model->getOriginal());
        });

        static::deleted(function ($model) {
            $model->logActivity('Delete');
        });
    }

    public function logActivity(
        string $event, 
        array $oldValues = null, 
        array $customDescription = null
    ) {
        $user = Auth::user();

        $description = $customDescription 
            ?? $this->generateDescription($event);

        $modelIdentifier = method_exists($this, 'getLogIdentifier') 
            ? $this->getLogIdentifier() 
            : ($this->name ?? $this->title ?? "ID: {$this->id}");

        $userIdentifier = $user 
            ? ($user->name ?? $user->email ?? "User ID: {$user->id}") 
            : 'Unknown User';

        ActivityLog::create([
            'log_type' => strtolower(class_basename(static::class)) . "_$event",
            'model_type' => get_class($this),
            'model_identifier' => $modelIdentifier,
            'user_identifier' => $userIdentifier,
            'user_email' => $user?->email,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $this->toArray(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
    }

    protected function generateDescription(string $event): string
    {
        $modelName = strtolower(class_basename(static::class));
        return ucfirst("{$modelName} {$event} with identifier: {$this->getLogIdentifier()}");
    }

    public function getLogIdentifier(): string
    {
        return $this->name ?? $this->title ?? "ID: {$this->id}";
    }
}