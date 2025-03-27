<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

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
        'if_number',
        'rc_number',
        'cnss_number',
        'tp_number',
        'nis_number',
        'nif_number',
        'ai_number',
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
    
    public function getStatementTransactions($startDate, $endDate)
    {
        return DB::table('sales')
            ->leftJoin('cash_transactions', 'sales.id', '=', 'cash_transactions.transactionable_id')
            ->where('sales.client_id', $this->id)
            ->where(function($query) {
                $query->where('cash_transactions.transactionable_type', 'App\Models\Sale')
                      ->orWhereNull('cash_transactions.transactionable_type');
            })
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'sales.sale_date as date',
                'sales.reference_number',
                'sales.total_amount',
                'sales.paid_amount',
                'cash_transactions.amount as payment_amount',
                'cash_transactions.type as transaction_type',
                'cash_transactions.transaction_date'
            )
            ->orderBy('sales.sale_date', 'asc')
            ->get()
            ->map(function ($record) {
                return [
                    'date' => $record->date,
                    'reference' => $record->reference_number,
                    'type' => 'Sale',
                    'amount' => $record->total_amount,
                    'payment_amount' => $record->payment_amount ?? 0,
                    'remaining_amount' => $record->total_amount - $record->paid_amount
                ];
            });
    }

    public function getOpeningBalance($date)
    {
        $salesBalance = DB::table('sales')
            ->where('client_id', $this->id)
            ->where('sale_date', '<', $date)
            ->sum('total_amount');

        $paymentsBalance = DB::table('cash_transactions')
            ->join('sales', 'cash_transactions.transactionable_id', '=', 'sales.id')
            ->where('sales.client_id', $this->id)
            ->where('cash_transactions.transactionable_type', 'App\Models\Sale')
            ->where('cash_transactions.transaction_date', '<', $date)
            ->sum('cash_transactions.amount');

        return $salesBalance - $paymentsBalance;
    }

    public function getCurrentBalance()
    {
        $salesBalance = DB::table('sales')
            ->where('client_id', $this->id)
            ->sum('total_amount');

        $paymentsBalance = DB::table('cash_transactions')
            ->join('sales', 'cash_transactions.transactionable_id', '=', 'sales.id')
            ->where('sales.client_id', $this->id)
            ->where('cash_transactions.transactionable_type', 'App\Models\Sale')
            ->sum('cash_transactions.amount');

        return $salesBalance - $paymentsBalance;
    }

    public function getTotalSales($startDate, $endDate)
    {
        return $this->sales()
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum('total_amount');
    }

    public function getTotalPayments($startDate, $endDate)
    {
        return $this->sales()
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum('paid_amount');
    }

    public function getOutstandingBalance($startDate, $endDate)
    {
        return $this->sales()
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum(DB::raw('total_amount - paid_amount'));
    }
}
