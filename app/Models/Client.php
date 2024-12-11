<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'balance',
        'payment_terms',
        'tax_number',
        'notes',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function updateBalance($amount, $type = 'add')
    {
        $this->balance = $type === 'add' 
            ? $this->balance + $amount 
            : $this->balance - $amount;
        $this->save();
    }
}