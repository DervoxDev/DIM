<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Team;
class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'team_id',
        'subscription_type',
        'subscription_startDate',
        'subscription_expiredDate',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
