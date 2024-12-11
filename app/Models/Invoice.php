<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'invoiceable_type',
        'invoiceable_id',
        'reference_number',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'status',
        'issue_date',
        'due_date',
        'notes',
        'meta_data',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'meta_data' => 'json',
    ];

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function invoiceable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Helper methods
    public function markAsSent()
    {
        $this->status = 'sent';
        $this->save();
    }

    public function markAsPaid()
    {
        $this->status = 'paid';
        $this->save();
    }

    public function generateReference()
    {
        $prefix = $this->invoiceable_type === 'App\Models\Sale' ? 'INV' : 'BILL';
        $this->reference_number = $prefix . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        $this->save();
    }
}