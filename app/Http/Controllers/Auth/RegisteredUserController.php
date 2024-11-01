<?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use App\Models\User;
    use App\Models\Plan;
    use App\Models\Team;
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
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
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
                    'subscription_type' => $trialPlan->name, // Add this line to set subscription_type
                    'subscription_startDate' => now(),
                    'subscription_expiredDate' => $trialPlan->calculateExpirationDate(now()),
                    'status' => 'active',
                ]);
            } else {
                throw new \Exception("Trial plan not found.");
            }


            event(new Registered($user));



            Auth::login($user);

            return redirect(route('dashboard', absolute: false));
        }
    }
