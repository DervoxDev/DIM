<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use App\Models\Subscription;
    use Illuminate\Support\Facades\Mail;
    use App\Mail\SubscriptionExpiredNotice;
    class ExpireSubscriptions extends Command
    {
        protected $signature = 'subscriptions:expire';
        protected $description = 'Send expiration notices and expire subscriptions';

        public function handle()
        {
            // Expire subscriptions that have passed their expiration date
            $expiredSubscriptions = Subscription::where('status', 'active')
                                                ->whereDate('subscription_expiredDate', '<', now())
                                                ->get();

            foreach ($expiredSubscriptions as $subscription) {
                try {
                    $user = $subscription->team->user;

                    // Send expired subscription notice
                    Mail::to($user->email)->send(
                        new SubscriptionExpiredNotice($subscription)
                    );

                    // Update subscription and team status
                    $subscription->update(['status' => 'expired']);
                    $subscription->team->update(['is_active' => false]);
                } catch (\Exception $e) {
                    \Log::error("Error processing expired subscription for team {$subscription->team_id}: " . $e->getMessage());
                }
            }

            $this->info("Sent {$expiredSubscriptions->count()} expiration notices");
            $this->info("Expired {$expiredSubscriptions->count()} subscriptions");

            \Log::info("Sent {$expiredSubscriptions->count()} expiration notices");
            \Log::info("Expired {$expiredSubscriptions->count()} subscriptions");

            return Command::SUCCESS;
        }
    }
