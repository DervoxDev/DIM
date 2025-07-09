<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Team;
use App\Helpers\CountryHelper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country_id' => [
                'required', // Now required
                'string',
                'size:3', // Must be exactly 3 characters (ISO3 code)
                function ($attribute, $value, $fail) {
                    if (!CountryHelper::isValidCountryCode($value)) {
                        $fail(__('validation.The selected country is invalid.'));
                    }
                },
            ],
            'terms' => ['required', 'accepted'],
        ], [
            'terms.required' => __('validation.You must accept the terms and conditions.'),
            'terms.accepted' => __('validation.You must accept the terms and conditions.'),
            'name.required' => __('validation.The name field is required.'),
            'email.required' => __('validation.The email field is required.'),
            'email.unique' => __('validation.The email has already been taken.'),
            'password.required' => __('validation.The password field is required.'),
            'country_id.required' => __('validation.The country field is required.'),
            'country_id.size' => __('validation.The country code must be exactly 3 characters.'),
        ]);

        // Convert country_id to uppercase for consistency
        $countryId = strtoupper($request->country_id);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country_id' => $countryId, // Fixed: was 'country', now 'country_id'
        ]);
        
        $user->assignRole('team_admin');

        // Generate a unique team name for the user
        function generateUniqueTeamName($userName)
        {
            $baseName = $userName . "'s Team";
            $teamName = $baseName;
            $counter = 1;

            // Check for uniqueness and generate a new name if needed
            while (Team::where('name', $teamName)->exists()) {
                $teamName = $baseName . ' ' . $counter;
                $counter++;
            }

            return $teamName;
        }

        // Create a team for the user
        $teamName = generateUniqueTeamName($user->name);
        $team = Team::create(['name' => $teamName]);
        
        // Associate the user with the team
        $user->team_id = $team->id;
        $user->save();
        
        $trialPlan = Plan::where('name', 'Trial')->first();
        if ($trialPlan) {
            $team->subscription()->create([
                'plan_id' => $trialPlan->id,
                'subscription_type' => $trialPlan->name,
                'subscription_startDate' => now(),
                'subscription_expiredDate' => $trialPlan->calculateExpirationDate(now()),
                'status' => 'active',
            ]);
        } else {
            throw new \Exception("Trial plan not found.");
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('analytics.dashboard', absolute: false));
    }
}
