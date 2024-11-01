<?php
    // app/Models/Plan.php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Carbon\Carbon;

    class Plan extends Model
    {
        use HasFactory;

        protected $fillable = [
            'name',
            'description',
            'price',
            'duration_type',
            'duration_value',
            'is_active'
        ];

        protected $casts = [
            'price' => 'decimal:2',
            'duration_value' => 'integer',
            'is_active' => 'boolean'
        ];

        public function features(): HasMany
        {
            return $this->hasMany(PlanFeature::class);
        }

        public function subscriptions(): HasMany
        {
            return $this->hasMany(Subscription::class);
        }

        public function calculateExpirationDate(Carbon $startDate): ?Carbon
        {
            if ($this->duration_type === 'lifetime') {
                return null;
            }

            return match ($this->duration_type) {
                'monthly' => $startDate->addMonths($this->duration_value),
                'yearly' => $startDate->addYears($this->duration_value),
                'days' => $startDate->addDays($this->duration_value), // For trial duration
                default => $startDate,
            };
        }

        public function getFormattedPriceAttribute(): string
        {
            return '$' . number_format($this->price, 2);
        }

        public function getFormattedDurationAttribute(): string
        {
            if ($this->duration_type === 'lifetime') {
                return 'Lifetime';
            }

            return match ($this->duration_type) {
                'monthly' => $this->duration_value === 1 ? 'Monthly' : $this->duration_value . ' Months',
                'yearly' => $this->duration_value === 1 ? 'Yearly' : $this->duration_value . ' Years',
                'days' => $this->duration_value === 7 ? '1 Week Trial' : $this->duration_value . ' Days',
                default => 'Unknown duration'
            };
        }

        public function hasFeature(string $featureName): bool
        {
            return $this->features()->where('feature_name', $featureName)->exists();
        }

        public function getFeatureValue(string $featureName)
        {
            $feature = $this->features()->where('feature_name', $featureName)->first();
            if (!$feature) {
                return null;
            }

            // Convert feature value based on type
            return match ($feature->feature_type) {
                'boolean' => $feature->feature_value === 'true',
                'numeric' => (int) $feature->feature_value,
                default => $feature->feature_value,
            };
        }
    }
