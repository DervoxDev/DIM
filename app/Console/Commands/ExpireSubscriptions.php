<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionExpiredNotice;
use App\Mail\SubscriptionExpirationNotice;
use Carbon\Carbon;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Send expiration notices and expire subscriptions';

    public function handle()
    {
        // Fetch subscriptions that are about to expire within the next 3 days
        $expiringSubscriptions = Subscription::where('status', 'active')
            ->whereDate('subscription_expiredDate', '>=', now())
            ->whereDate('subscription_expiredDate', '<=', now()->addDays(3))
            ->get();

        foreach ($expiringSubscriptions as $subscription) {
            \Log::info("Checking expiring subscription: {$subscription->id} for team {$subscription->team_id}");

            try {
                // Skip if we shouldn't send a notification yet
                if (!$subscription->shouldSendNotification()) {
                    \Log::info("Skipping notification for subscription {$subscription->id} - too soon since last notification");
                    continue;
                }

                $team = $subscription->team;
                $user = $team->users()->first();

                if ($user) {
                    // Calculate days remaining correctly
                    $daysRemaining = Carbon::now()->startOfDay()->diffInDays(
                        Carbon::parse($subscription->subscription_expiredDate)->startOfDay(),
                        false
                    );

                    \Log::info("Sending expiration notice to: {$user->email} ({$daysRemaining} days remaining)");

                    Mail::to($user->email)->send(new SubscriptionExpirationNotice($subscription, $daysRemaining));
                    
                    // Update the last notification timestamp
                    $subscription->updateNotificationSent();

                    \Log::info("Expiration notice successfully sent to: {$user->email}");
                } else {
                    \Log::warning("No user found for team {$team->id}");
                }
            } catch (\Exception $e) {
                \Log::error("Error processing expiration notice for team {$subscription->team_id}: " . $e->getMessage());
            }
        }

        // Handle expired subscriptions (this part remains largely the same)
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->whereDate('subscription_expiredDate', '<', now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            \Log::info("Processing expired subscription: {$subscription->id} for team {$subscription->team_id}");

            try {
                $team = $subscription->team;
                $user = $team->users()->first();

                if ($user && !$subscription->last_notification_sent_at?->isToday()) {
                    Mail::to($user->email)->send(new SubscriptionExpiredNotice($subscription));
                    $subscription->updateNotificationSent();
                    \Log::info("Expired notice successfully sent to: {$user->email}");
                }

                $subscription->markAsExpired();
            } catch (\Exception $e) {
                \Log::error("Error processing expired subscription for team {$subscription->team_id}: " . $e->getMessage());
            }
        }

        $this->info("Processed {$expiringSubscriptions->count()} expiring subscriptions.");
        $this->info("Processed {$expiredSubscriptions->count()} expired subscriptions.");

        return Command::SUCCESS;
    }
}
