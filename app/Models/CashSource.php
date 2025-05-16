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
        'is_default_general',
        'is_default_sales',
        'is_default_purchases',
        'is_default_payments',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
        'is_default_general' => 'boolean',
        'is_default_sales' => 'boolean',
        'is_default_purchases' => 'boolean',
        'is_default_payments' => 'boolean',
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
    public function deposit($amount, $description = null, $paymentMethod = null)
    {
        $this->balance += $amount;
        $this->save();
    
        return $this->transactions()->create([
            'team_id' => $this->team_id,
            'amount' => $amount,
            'type' => 'deposit',
            'payment_method' => $paymentMethod, // Add payment method
            'description' => $description,
            'transaction_date' => now(),
            'reference_number' => 'DEP-' . time(),
            'transactionable_type' => 'App\Models\CashSource',
            'transactionable_id' => $this->id,
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
        'reference_number' => 'WIT-' . time(), // Generate a reference number
        'transactionable_type' => 'App\Models\CashSource', // Default type
        'transactionable_id' => $this->id, // Reference to self
    ]);
}

// And update the transfer method as well
public function transfer($amount, CashSource $destination, $description = null)
{
    if ($this->balance < $amount) {
        throw new \Exception('Insufficient funds for transfer');
    }

    // Create withdrawal transaction
    $withdrawalTx = $this->transactions()->create([
        'team_id' => $this->team_id,
        'amount' => $amount,
        'type' => 'transfer',
        'description' => "Transfer to {$destination->name}: $description",
        'transaction_date' => now(),
        'reference_number' => 'TRF-' . time(),
        'transactionable_type' => 'App\Models\CashSource',
        'transactionable_id' => $this->id,
        'transfer_destination_id' => $destination->id
    ]);

    // Update balances
    $this->balance -= $amount;
    $this->save();

    $destination->balance += $amount;
    $destination->save();

    return $withdrawalTx;
}
public function setAsDefault($context = 'general')
{
    $column = 'is_default_' . $context;
    
    // First, unset any existing defaults for this context in the team
    self::where('team_id', $this->team_id)
        ->where('id', '!=', $this->id)
        ->where($column, true)
        ->update([$column => false]);
    
    // Then set this one as default
    $this->$column = true;
    $this->save();
    
    return $this;
}

public static function getDefaultForTeam($teamId, $context = 'general')
{
    $column = 'is_default_' . $context;
    return self::where('team_id', $teamId)
        ->where($column, true)
        ->where('status', 'active')  // Only consider active cash sources
        ->first();
}
}