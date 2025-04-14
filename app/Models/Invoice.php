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
        'type', // Quote or Invoice
        'total_amount',
        'tax_amount',
        'discount_amount',
        'status', // draft, completed, cancelled
        'payment_status', // unpaid, partial, paid
        'is_email_sent', // boolean
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
        'is_email_sent' => 'boolean',
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
    
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
    // Document status scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Payment status scopes
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    // Type scopes
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Status helpers - document workflow only
    public function markAsCompleted()
    {
        $this->status = 'completed';  // Don't change payment_status
        $this->save();
    }

    public function markAsCancelled()
    {
        $this->status = 'cancelled';  // Don't change payment_status
        $this->save();
    }

    // Payment status helpers - payment only
    public function markAsPaid()
    {
        $this->payment_status = 'paid';  // Don't change document status
        $this->save();
    }

    public function markAsPartiallyPaid($amount = null)
    {
        $this->payment_status = 'partial';  // Don't change document status
        if ($amount) {
            $metaData = $this->meta_data ?? [];
            $metaData['paid_amount'] = $amount;
            $this->meta_data = $metaData;
        }
        $this->save();
    }

    public function markAsUnpaid()
    {
        $this->payment_status = 'unpaid';  // Don't change document status
        $this->save();
    }

    // Email status helper - email only
    public function markAsEmailSent()
    {
        $this->is_email_sent = true;  // Don't change other statuses
        $this->save();
    }

    public function generateReference()
    {
        $prefix = $this->type === 'quote' ? 'DEVIS' : 'INV';
        $this->reference_number = $prefix . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        $this->save();
    }

    // Check if this is a quote
    public function isQuote()
    {
        return $this->type === 'quote';
    }

    // Check if this is an invoice
    public function isInvoice()
    {
        return $this->type === 'invoice';
    }
}
