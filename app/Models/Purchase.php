<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'supplier_id',
        'cash_source_id',
        'reference_number',
        'total_amount',
        'paid_amount',
        'tax_amount',
        'discount_amount',
        'payment_status',
        'status',
        'purchase_date',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'purchase_date' => 'date',
        'due_date' => 'date',
    ];

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function cashSource()
    {
        return $this->belongsTo(CashSource::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function transactions()
    {
        return $this->morphMany(CashTransaction::class, 'transactionable');
    }

    public function invoice()
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    // Helper methods
    public function addPayment($amount, $cashSource)
    {
        if ($amount > ($this->total_amount - $this->paid_amount)) {
            throw new \Exception('Payment amount exceeds remaining balance');
        }

        $this->paid_amount += $amount;
        $this->payment_status = $this->paid_amount >= $this->total_amount ? 'paid' : 'partial';
        $this->save();

        // Create transaction
        return $this->transactions()->create([
            'team_id' => $this->team_id,
            'cash_source_id' => $cashSource->id,
            'amount' => $amount,
            'type' => 'purchase_payment',
            'transaction_date' => now(),
            'description' => "Payment for purchase #{$this->reference_number}",
        ]);
    }

    public function calculateTotals()
    {
        $this->total_amount = $this->items->sum('total_price');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->save();
    }
}