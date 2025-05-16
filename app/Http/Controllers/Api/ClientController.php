<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ClientController extends Controller
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

        $query = Client::where('team_id', $user->team->id);

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                  ->orWhere('tax_number', 'like', "%{$searchTerm}%")
                  ->orWhere('if_number', 'like', "%{$searchTerm}%")
                  ->orWhere('rc_number', 'like', "%{$searchTerm}%")
                  ->orWhere('cnss_number', 'like', "%{$searchTerm}%")
                  ->orWhere('nif_number', 'like', "%{$searchTerm}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Balance filter
        if ($request->has('has_balance')) {
            $query->where('balance', '>', 0);
        }

        // Sort
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $clients = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'clients' => $clients
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->team) {
            return response()->json([
                'error' => true,
                'message' => 'No team found for the user'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'if_number' => 'nullable|string',
            'rc_number' => 'nullable|string',
            'cnss_number' => 'nullable|string',
            'tp_number' => 'nullable|string',
            'nis_number' => 'nullable|string',
            'nif_number' => 'nullable|string',
            'ai_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $client = new Client($request->all());
            $client->team_id = $user->team->id;
            $client->status = 'active';
            $client->balance = 0;
            $client->save();

            ActivityLog::create([
                'log_type' => 'Create',
                'model_type' => "Client",
                'model_id' => $client->id,
                'model_identifier' => $client->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Created client {$client->name}",
                'new_values' => $client->toArray()
            ]);

            return response()->json([
                'message' => 'Client created successfully',
                'client' => $client
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error creating client'
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $client = Client::where('team_id', $user->team->id)
                       ->with(['sales' => function($query) {
                           $query->latest()->take(5);
                       }])
                       ->find($id);

        if (!$client) {
            return response()->json([
                'error' => true,
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'client' => $client
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
    
            \Log::info('Updating client', [
                'client_id' => $id,
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);
    
            $client = Client::where('team_id', $user->team->id)->find($id);
    
            if (!$client) {
                \Log::warning('Client not found', [
                    'client_id' => $id,
                    'team_id' => $user->team->id
                ]);
                
                return response()->json([
                    'error' => true,
                    'message' => 'Client not found'
                ], 404);
            }
    
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'payment_terms' => 'nullable|string',
                'tax_number' => 'nullable|string',
                'if_number' => 'nullable|string',
                'rc_number' => 'nullable|string',
                'cnss_number' => 'nullable|string',
                'tp_number' => 'nullable|string',
                'nis_number' => 'nullable|string',
                'nif_number' => 'nullable|string',
                'ai_number' => 'nullable|string',
                'notes' => 'nullable|string',
                'status' => 'nullable|in:active,inactive',
            ]);
    
            if ($validator->fails()) {
                \Log::warning('Validation failed', [
                    'client_id' => $id,
                    'errors' => $validator->errors()->toArray()
                ]);
    
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            try {
                $oldData = $client->toArray();
                
                \Log::info('Before update', [
                    'client_id' => $id,
                    'old_data' => $oldData
                ]);
    
                $client->update($request->all());
    
                \Log::info('After update', [
                    'client_id' => $id,
                    'new_data' => $client->toArray()
                ]);
    
                ActivityLog::create([
                    'log_type' => 'Update',
                    'model_type' => "Client",
                    'model_id' => $client->id,
                    'model_identifier' => $client->name,
                    'user_identifier' => $user?->name,
                    'user_id' => $user->id,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Updated client {$client->name}",
                    'old_values' => $oldData,
                    'new_values' => $client->toArray()
                ]);
    
                \Log::info('Client updated successfully', [
                    'client_id' => $id,
                    'client_name' => $client->name
                ]);
    
                return response()->json([
                    'message' => 'Client updated successfully',
                    'client' => $client
                ]);
    
            } catch (\Exception $e) {
                \Log::error('Error updating client', [
                    'client_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
    
                return response()->json([
                    'error' => true,
                    'message' => 'Error updating client: ' . $e->getMessage()
                ], 500);
            }
    
        } catch (\Exception $e) {
            \Log::error('Unexpected error in update method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }
    

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $client = Client::where('team_id', $user->team->id)->find($id);

        if (!$client) {
            return response()->json([
                'error' => true,
                'message' => 'Client not found'
            ], 404);
        }

        try {
            $clientData = $client->toArray();
            $client->delete();

            ActivityLog::create([
                'log_type' => 'Delete',
                'model_type' => "Client",
                'model_id' => $id,
                'model_identifier' => $client->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Deleted client {$client->name}",
                'old_values' => $clientData
            ]);

            return response()->json([
                'message' => 'Client deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error deleting client'
            ], 500);
        }
    }

    public function getSales(Request $request, $id)
    {
        $user = $request->user();

        $client = Client::where('team_id', $user->team->id)->find($id);

        if (!$client) {
            return response()->json([
                'error' => true,
                'message' => 'Client not found'
            ], 404);
        }

        $sales = $client->sales()
                       ->with(['items.product'])
                       ->orderBy('sale_date', 'desc')
                       ->paginate(15);

        return response()->json([
            'sales' => $sales
        ]);
    }

    // public function getTransactions(Request $request, $id)
    // {
    //     $user = $request->user();

    //     $client = Client::where('team_id', $user->team->id)->find($id);

    //     if (!$client) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Client not found'
    //         ], 404);
    //     }

    //     $transactions = $client->sales()
    //                          ->with('transactions')
    //                          ->get()
    //                          ->pluck('transactions')
    //                          ->flatten()
    //                          ->sortByDesc('transaction_date')
    //                          ->values();

    //     return response()->json([
    //         'transactions' => $transactions
    //     ]);
    // }
    public function getTransactions(Request $request, $id)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15); // Default to 15 items per page
        
        $client = Client::where('team_id', $user->team->id)->find($id);
        
        if (!$client) {
            return response()->json([
                'error' => true,
                'message' => 'Client not found'
            ], 404);
        }
        
        // Get all sales IDs for this client
        $salesIds = $client->sales()->pluck('id')->toArray();
        
        // Query transactions directly with pagination
        $transactions = DB::table('cash_transactions')
            ->whereIn('transactionable_id', $salesIds)
            ->where('transactionable_type', 'App\Models\Sale')
            ->orderBy('transaction_date', 'desc')
            ->paginate($perPage);
        
        return response()->json([
            'transactions' => $transactions
        ]);
    }
    
    public function getStatement(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Find client with team scope
            $client = Client::where('team_id', $user->team->id)->findOrFail($id);
    
            // Set date range from client creation to now
            $startDate = Carbon::parse($client->created_at)->startOfDay();
            $endDate = now()->endOfDay();
    
            $statement = [
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'current_balance' => $client->getCurrentBalance()
                ],
                'period' => [
                    'start_date' => $startDate->toISOString(),
                    'end_date' => $endDate->toISOString()
                ],
                'opening_balance' => $client->getOpeningBalance($startDate),
                'closing_balance' => $client->getCurrentBalance(),
                'transactions' => $client->getStatementTransactions($startDate, $endDate),
                'summary' => [
                    'total_sales' => DB::table('sales')
                        ->where('client_id', $client->id)
                        ->whereBetween('sale_date', [$startDate, $endDate])
                        ->sum('total_amount'),
                    'total_payments' => DB::table('cash_transactions')
                        ->join('sales', 'cash_transactions.transactionable_id', '=', 'sales.id')
                        ->where('sales.client_id', $client->id)
                        ->where('cash_transactions.transactionable_type', 'App\Models\Sale')
                        ->whereBetween('cash_transactions.transaction_date', [$startDate, $endDate])
                        ->sum('cash_transactions.amount'),
                    'outstanding_balance' => $client->getCurrentBalance()
                ]
            ];
    
            return response()->json([
                'success' => true,
                'statement' => $statement
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Statement generation error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while generating the statement'
            ], 500);
        }
    }
}
