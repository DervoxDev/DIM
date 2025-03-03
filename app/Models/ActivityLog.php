<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ActivityLog extends Model
{
    protected $fillable = [
        'log_type',
        'model_type',
        'model_identifier',
        'user_identifier',
        'user_email',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_email', 'email');
    }

    public function getModelAttribute()
    {
        if ($this->model_type && class_exists($this->model_type)) {
            return $this->model_type::find($this->id);
        }
        return null;
    }
}