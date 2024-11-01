<?php
    // app/Models/PlanFeature.php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Factories\HasFactory;

    class PlanFeature extends Model
    {
        use HasFactory;

        protected $fillable = [
            'plan_id',
            'feature_name',
            'feature_value',
            'feature_type'
        ];

        public function plan(): BelongsTo
        {
            return $this->belongsTo(Plan::class);
        }

        public function getValueAttribute()
        {
            return match ($this->feature_type) {
                'boolean' => $this->feature_value === 'true',
                'numeric' => (int) $this->feature_value,
                default => $this->feature_value,
            };
        }
    }
