<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
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

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
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

    public function getStatementTransactions($startDate, $endDate)
    {
        return DB::table('purchases')
            ->leftJoin('cash_transactions', 'purchases.id', '=', 'cash_transactions.transactionable_id')
            ->where('purchases.supplier_id', $this->id)
            ->where(function($query) {
                $query->where('cash_transactions.transactionable_type', 'App\Models\Purchase')
                      ->orWhereNull('cash_transactions.transactionable_type');
            })
            ->whereBetween('purchases.purchase_date', [$startDate, $endDate])
            ->select(
                'purchases.purchase_date as date',
                'purchases.reference_number',
                'purchases.total_amount',
                'purchases.paid_amount',
                'cash_transactions.amount as payment_amount',
                'cash_transactions.type as transaction_type',
                'cash_transactions.transaction_date'
            )
            ->orderBy('purchases.purchase_date', 'asc')
            ->get()
            ->map(function ($record) {
                return [
                    'date' => $record->date,
                    'reference' => $record->reference_number,
                    'type' => 'Purchase',
                    'amount' => $record->total_amount,
                    'payment_amount' => $record->payment_amount ?? 0,
                    'remaining_amount' => $record->total_amount - $record->paid_amount
                ];
            });
    }

    public function getOpeningBalance($date)
    {
        $purchasesBalance = DB::table('purchases')
            ->where('supplier_id', $this->id)
            ->where('purchase_date', '<', $date)
            ->sum('total_amount');

        $paymentsBalance = DB::table('cash_transactions')
            ->join('purchases', 'cash_transactions.transactionable_id', '=', 'purchases.id')
            ->where('purchases.supplier_id', $this->id)
            ->where('cash_transactions.transactionable_type', 'App\Models\Purchase')
            ->where('cash_transactions.transaction_date', '<', $date)
            ->sum('cash_transactions.amount');

        return $purchasesBalance - $paymentsBalance;
    }

    public function getCurrentBalance()
    {
        $purchasesBalance = DB::table('purchases')
            ->where('supplier_id', $this->id)
            ->sum('total_amount');

        $paymentsBalance = DB::table('cash_transactions')
            ->join('purchases', 'cash_transactions.transactionable_id', '=', 'purchases.id')
            ->where('purchases.supplier_id', $this->id)
            ->where('cash_transactions.transactionable_type', 'App\Models\Purchase')
            ->sum('cash_transactions.amount');

        return $purchasesBalance - $paymentsBalance;
    }

    public function getTotalPurchases($startDate, $endDate)
    {
        return $this->purchases()
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('total_amount');
    }

    public function getTotalPayments($startDate, $endDate)
    {
        return $this->purchases()
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('paid_amount');
    }

    public function getOutstandingBalance($startDate, $endDate)
    {
        return $this->purchases()
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->sum(DB::raw('total_amount - paid_amount'));
    }


}