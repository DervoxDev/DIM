<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Team extends Model
    {
        use HasFactory;

        protected $fillable = [
            'name',
            'email',
            'phone',
            'address',
            'image_path',
        ];

        // Add this line to always load the subscription relationship
        protected $with = ['subscription'];

        public function users(): HasMany
        {
            return $this->hasMany(User::class);
        }

        public function subscription(): HasOne
        {
            return $this->hasOne(Subscription::class);
        }
        public function products(): HasMany
        {
            return $this->hasMany(Product::class);
        }
        // ... keep your other methods ...

        // Add these accessors to make it easier to access subscription data
        public function getSubscriptionTypeAttribute()
        {
            return $this->subscription->subscription_type ?? null;
        }

        public function getSubscriptionExpiredDateAttribute()
        {
            return $this->subscription->subscription_expiredDate ?? null;
        }
    }
