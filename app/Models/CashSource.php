<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashSource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'type',
        'balance',
        'initial_balance',
        'description',
        'account_number',
        'bank_name',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
    ];

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function deposit($amount, $description = null)
    {
        $this->balance += $amount;
        $this->save();

        return $this->transactions()->create([
            'team_id' => $this->team_id,
            'amount' => $amount,
            'type' => 'deposit',
            'description' => $description,
            'transaction_date' => now(),
        ]);
    }

    public function withdraw($amount, $description = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient funds');
        }

        $this->balance -= $amount;
        $this->save();

        return $this->transactions()->create([
            'team_id' => $this->team_id,
            'amount' => $amount,
            'type' => 'withdrawal',
            'description' => $description,
            'transaction_date' => now(),
        ]);
    }

    public function transfer($amount, CashSource $destination, $description = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient funds for transfer');
        }

        $this->withdraw($amount, "Transfer to {$destination->name}: $description");
        $destination->deposit($amount, "Transfer from {$this->name}: $description");

        return true;
    }
}