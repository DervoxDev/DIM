<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'cash_source_id',
        'transactionable_type',
        'transactionable_id',
        'amount',
        'type',
        'reference_number',
        'transfer_destination_id',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function cashSource()
    {
        return $this->belongsTo(CashSource::class);
    }

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function transferDestination()
    {
        return $this->belongsTo(CashSource::class, 'transfer_destination_id');
    }

    // Scopes
    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }

    public function scopeTransfers($query)
    {
        return $query->where('type', 'transfer');
    }
}
