<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the users that belong to the team.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the subscription associated with the team.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Get all teams with their associated users and subscription.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllTeamsWithUsersAndSubscription()
    {
        return self::with(['users', 'subscription'])->get();
    }

    /**
     * Get a specific team by ID with its associated users and subscription.
     *
     * @param int $id
     * @return Team|null
     */
    public static function getTeamWithUsersAndSubscription($id)
    {
        return self::with(['users', 'subscription'])->find($id);
    }
}
