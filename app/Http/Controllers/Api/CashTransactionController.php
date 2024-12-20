<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashTransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->team) {
            return response()->json([
                'error' => true,
                'message' => 'No team found for the user'
            ], 404);
        }

        $query = CashTransaction::where('team_id', $user->team->id)
                               ->with(['cashSource', 'transferDestination']);

        // Date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('transaction_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Transaction type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Cash source filter
        if ($request->has('cash_source_id')) {
            $query->where('cash_source_id', $request->cash_source_id);
        }

        // Amount range filter
        if ($request->has('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->has('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Search by reference number or description
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('reference_number', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort_by', 'transaction_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $transactions = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'transactions' => $transactions
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $transaction = CashTransaction::where('team_id', $user->team->id)
                                    ->with(['cashSource', 'transferDestination', 'transactionable'])
                                    ->find($id);

        if (!$transaction) {
            return response()->json([
                'error' => true,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'transaction' => $transaction
        ]);
    }

    public function getBySource(Request $request, $sourceId)
    {
        $user = $request->user();

        // Validate if the cash source belongs to the team
        $transactions = CashTransaction::where('team_id', $user->team->id)
                                     ->where('cash_source_id', $sourceId)
                                     ->with(['cashSource', 'transferDestination'])
                                     ->orderBy('transaction_date', 'desc')
                                     ->paginate($request->input('per_page', 15));

        return response()->json([
            'transactions' => $transactions
        ]);
    }

    public function getSummary(Request $request)
    {
        $user = $request->user();
    
        if (!$user->team) {
            \Log::error('No team found for user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
    
            return response()->json([
                'error' => true,
                'message' => 'No team found for the user'
            ], 404);
        }
    
        try {
            // Parse and validate dates with explicit error handling
            try {
                $startDate = $request->has('start_date') 
                    ? Carbon::parse($request->start_date)
                    : now()->startOfMonth();
                
                $endDate = $request->has('end_date')
                    ? Carbon::parse($request->end_date)
                    : now()->endOfMonth();
            } catch (\Exception $e) {
                \Log::error('Date parsing error', [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'error' => $e->getMessage()
                ]);
    
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid date format'
                ], 400);
            }
    
            // Log the request parameters
            \Log::info('Processing summary request', [
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $endDate->toDateTimeString(),
                'team_id' => $user->team->id,
                'user_id' => $user->id
            ]);
    
            // Check if there are any transactions in the date range
            $hasTransactions = CashTransaction::where('team_id', $user->team->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->exists();
    
            // Prepare the summary data
            $summary = [
                'period' => [
                    'start' => $startDate->toDateTimeString(),
                    'end' => $endDate->toDateTimeString()
                ],
                
                'total_deposits' => CashTransaction::where('team_id', $user->team->id)
                    ->where('type', 'deposit')
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->sum('amount') ?? 0,
    
                'total_withdrawals' => CashTransaction::where('team_id', $user->team->id)
                    ->where('type', 'withdrawal')
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->sum('amount') ?? 0,
    
                'total_transfers' => CashTransaction::where('team_id', $user->team->id)
                    ->where('type', 'transfer')
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->sum('amount') ?? 0,
    
                'transactions_by_type' => CashTransaction::where('team_id', $user->team->id)
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->select(
                        'type',
                        DB::raw('COUNT(*) as count'),
                        DB::raw('COALESCE(SUM(amount), 0) as total_amount')
                    )
                    ->groupBy('type')
                    ->get(),
    
                'transactions_by_source' => CashTransaction::where('team_id', $user->team->id)
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->join('cash_sources', 'cash_transactions.cash_source_id', '=', 'cash_sources.id')
                    ->select(
                        'cash_sources.name',
                        DB::raw('COUNT(*) as count'),
                        DB::raw('COALESCE(SUM(amount), 0) as total_amount')
                    )
                    ->groupBy('cash_sources.id', 'cash_sources.name')
                    ->get(),
    
                'daily_totals' => CashTransaction::where('team_id', $user->team->id)
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->select(
                        DB::raw('DATE(transaction_date) as date'),
                        DB::raw('COALESCE(SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END), 0) as deposits'),
                        DB::raw('COALESCE(SUM(CASE WHEN type = "withdrawal" THEN amount ELSE 0 END), 0) as withdrawals'),
                        DB::raw('COALESCE(SUM(CASE WHEN type = "transfer" THEN amount ELSE 0 END), 0) as transfers')
                    )
                    ->groupBy(DB::raw('DATE(transaction_date)'))
                    ->orderBy('date')
                    ->get()
            ];
    
            // Calculate some additional metrics
            $summary['net_flow'] = $summary['total_deposits'] - $summary['total_withdrawals'];
            $summary['has_transactions'] = $hasTransactions;
            $summary['transaction_count'] = collect($summary['transactions_by_type'])->sum('count');
    
            // Log successful summary generation
            \Log::info('Summary generated successfully', [
                'team_id' => $user->team->id,
                'user_id' => $user->id,
                'period' => $summary['period'],
                'transaction_count' => $summary['transaction_count']
            ]);
    
            // Record this activity
            ActivityLog::create([
                'log_type' => 'Report',
                'model_type' => 'CashTransaction',
                'user_identifier' => $user->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => 'Generated cash transactions summary report',
                'new_values' => [
                    'period' => $summary['period'],
                    'transaction_count' => $summary['transaction_count']
                ]
            ]);
    
            return response()->json([
                'success' => true,
                'summary' => $summary,
                'meta' => [
                    'generated_at' => now()->toDateTimeString(),
                    'timezone' => config('app.timezone'),
                    'period' => [
                        'start' => $startDate->toDateTimeString(),
                        'end' => $endDate->toDateTimeString()
                    ]
                ]
            ]);
    
        } catch (\Exception $e) {
            // Log the detailed error
            \Log::error('Error generating transaction summary', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'team_id' => $user->team->id,
                'start_date' => $startDate->toDateTimeString() ?? null,
                'end_date' => $endDate->toDateTimeString() ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
    
            // Log error activity
            ActivityLog::create([
                'log_type' => 'Error',
                'model_type' => 'CashTransaction',
                'user_identifier' => $user->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => 'Error generating cash transactions summary report',
                'new_values' => [
                    'error' => $e->getMessage(),
                    'start_date' => $startDate->toDateTimeString() ?? null,
                    'end_date' => $endDate->toDateTimeString() ?? null
                ]
            ]);
    
            return response()->json([
                'error' => true,
                'message' => 'Error generating transaction summary',
                'debug_message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    
    
}