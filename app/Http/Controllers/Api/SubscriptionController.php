<?php

    namespace App\Http\Controllers\Api;

    use App\Http\Controllers\Controller;
    use App\Models\Subscription;
    use App\Models\Team;

    use Illuminate\Http\Request;

    class SubscriptionController extends Controller
    {
        /**
         * Get the current subscription status for the authenticated user's team
         */
        public function getStatus(Request $request)
        {
            $user = $request->user();

            // Ensure user has an associated team
            if (!$user->team) {
                return response()->json([
                    'error' => true,
                    'message' => 'No team found for the user'
                ], 404);
            }

            $subscription = Subscription::where('team_id', $user->team->id)
                                        ->with('plan')
                                        ->first();

            if (!$subscription) {
                return response()->json([
                    'error' => true,
                    'message' => 'No active subscription found'
                ], 404);
            }

            return response()->json([
                'subscription' => [
                    'type' => $subscription->subscription_type,
                    'status' => $subscription->status,
                    'start_date' => $subscription->subscription_startDate,
                    'expiration_date' => $subscription->subscription_expiredDate,
                    'days_until_expiration' => $subscription->daysUntilExpiration(),
                    'is_active' => $subscription->isActive(),
                    'plan_details' => $subscription->plan
                ]
            ]);
        }
    }
