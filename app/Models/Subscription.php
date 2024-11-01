<?php
    // app/Models/Subscription.php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Carbon\Carbon;

    class Subscription extends Model
    {
        use HasFactory;

        protected $fillable = [
            'team_id',
            'plan_id',
            'subscription_type',
            'subscription_startDate',
            'subscription_expiredDate',
            'status'
        ];

        protected $casts = [
            'subscription_startDate' => 'date',
            'subscription_expiredDate' => 'date',
        ];

        public function team(): BelongsTo
        {
            return $this->belongsTo(Team::class);
        }

        public function plan(): BelongsTo
        {
            return $this->belongsTo(Plan::class);
        }

        public function isExpired(): bool
        {
            return $this->subscription_expiredDate < Carbon::today();
        }

        public function isActive(): bool
        {
            return !$this->isExpired() && $this->status === 'active';
        }

        public function daysUntilExpiration(): int
        {
            return Carbon::today()->diffInDays($this->subscription_expiredDate, false);
        }

        public function markAsExpired(): void
        {
            $this->update(['status' => 'expired']);
            $this->team->update(['is_active' => false]);
        }

        public function cancel(): void
        {
            $this->update(['status' => 'cancelled']);
        }

        public function hasFeature(string $featureName)
        {
            return $this->plan?->hasFeature($featureName);
        }

        public function getFeatureValue(string $featureName)
        {
            return $this->plan?->getFeatureValue($featureName);

        }
        public function getSubscriptionTypeAttribute()
        {
            return $this->plan?->name;
        }
        public function createTrialSubscription($teamId)
        {
            $trialPlan = Plan::where('name', 'Trial')->first();

            if ($trialPlan) {
                return $this->create([
                    'team_id' => $teamId,
                    'plan_id' => $trialPlan->id,
                    'subscription_type' => $trialPlan->name,
                    'subscription_startDate' => now(),
                    'subscription_expiredDate' => $trialPlan->calculateExpirationDate(now()),
                    'status' => 'active',
                ]);
            }
            throw new \Exception("Trial plan not found.");
        }
    }
