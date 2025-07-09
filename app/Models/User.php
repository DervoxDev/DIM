<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Team;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use PragmaRX\Countries\Package\Countries;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser 
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'team_id',
        'role',
        'country_id'  // Added for country functionality
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Existing relationships and methods
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin' && $this->hasRole('admin')) {
           return true;
        }
        return false;
    }

    // Country and Currency functionality
    
    /**
     * Get user's country information
     */
    public function getCountryAttribute()
    {
        if ($this->country_id) {
            return Countries::where('cca3', $this->country_id)->first();
        }
        return null;
    }
    
    /**
     * Get user's currency with hydration
     */
    public function getCurrencyAttribute()
    {
        $country = $this->country;
        if ($country) {
            $country = $country->hydrateCurrencies();
            $currencies = $country->currencies;
            if ($currencies && $currencies->count() > 0) {
                return $currencies->first();
            }
        }
        return null;
    }
    
    /**
     * Get currency symbol (e.g., "$", "€", "£")
     */
    public function getCurrencySymbolAttribute()
    {
        $currency = $this->currency;
        if ($currency && isset($currency->units) && isset($currency->units->major)) {
            return $currency->units->major->symbol;
        }
        return '$'; // Default to USD symbol
    }
    
    /**
     * Get currency code (e.g., "USD", "EUR", "GBP")
     */
    public function getCurrencyCodeAttribute()
    {
        $currency = $this->currency;
        if ($currency && isset($currency->iso)) {
            return $currency->iso->code;
        }
        return 'USD'; // Default to USD
    }

    /**
     * Get currency name (e.g., "US Dollar", "Euro")
     */
    public function getCurrencyNameAttribute()
    {
        $currency = $this->currency;
        if ($currency && isset($currency->name)) {
            return $currency->name;
        }
        return 'US Dollar'; // Default currency name
    }

    /**
     * Get country flag emoji
     */
    public function getCountryFlagAttribute()
    {
        $country = $this->country;
        return $country ? $country->flag->emoji : '🇺🇸'; // Default to US flag
    }

    /**
     * Get country calling code (e.g., "+1", "+33")
     */
    public function getCountryCallingCodeAttribute()
    {
        $country = $this->country;
        return $country && $country->idd ? $country->idd->root . ($country->idd->suffixes[0] ?? '') : '+1';
    }

    /**
     * Get country timezone
     */
    public function getTimezoneAttribute()
    {
        $country = $this->country;
        if ($country && isset($country->timezones)) {
            return is_array($country->timezones) ? $country->timezones[0] : $country->timezones;
        }
        return 'UTC'; // Default timezone
    }

    /**
     * Get country name
     */
    public function getCountryNameAttribute()
    {
        $country = $this->country;
        return $country ? $country->name->common : 'United States'; // Default country name
    }

    /**
     * Get formatted user location for display
     */
    public function getLocationDisplayAttribute()
    {
        return $this->country_flag . ' ' . $this->country_name;
    }
}
