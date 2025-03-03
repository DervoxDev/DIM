<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('team_admin')) {
            return $this->teamAdminDashboard($user);
        }

        // Default dashboard for other roles
        return view('dashboard');
    }
    private function teamAdminDashboard($user)
    {
        $team = $user->team;

        if (!$team) {
            // Handle the case where the user does not have an associated team
            return view('dashboard')->with([
                'team' => null,
                'workers' => collect(),
                'subscription' => null,
                'message' => 'No team associated with your account.'
            ]);
        }

        $workers = $team->users()->where('id', '!=', $user->id)->get();
        $subscription = $team->subscription;

        return view('dashboard', compact('team', 'workers', 'subscription'));
    }
}
