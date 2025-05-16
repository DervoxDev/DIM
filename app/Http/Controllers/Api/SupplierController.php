<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
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

        $query = Supplier::where('team_id', $user->team->id);

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $suppliers = $query->paginate(15);

        return response()->json([
            'suppliers' => $suppliers
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
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'tax_number' => 'nullable|string',
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
            $supplier = new Supplier($request->all());
            $supplier->team_id = $user->team->id;
            $supplier->save();

            ActivityLog::create([
                'log_type' => 'Create',
                'model_type' => "Supplier",
                'model_id' => $supplier->id,
                'model_identifier' => $supplier->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Supplier {$supplier->name} created",
                'new_values' => $supplier->toArray()
            ]);

            return response()->json([
                'message' => 'Supplier created successfully',
                'supplier' => $supplier
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error creating supplier'
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $supplier = Supplier::where('team_id', $user->team->id)
                           ->with(['purchases'])
                           ->find($id);

        if (!$supplier) {
            return response()->json([
                'error' => true,
                'message' => 'Supplier not found'
            ], 404);
        }

        return response()->json([
            'supplier' => $supplier
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $supplier = Supplier::where('team_id', $user->team->id)->find($id);

        if (!$supplier) {
            return response()->json([
                'error' => true,
                'message' => 'Supplier not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supplier->update($request->all());

            ActivityLog::create([
                'log_type' => 'Update',
                'model_type' => "Supplier",
                'model_id' => $supplier->id,
                'model_identifier' => $supplier->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Supplier {$supplier->name} updated",
                'new_values' => $supplier->toArray()
            ]);

            return response()->json([
                'message' => 'Supplier updated successfully',
                'supplier' => $supplier
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error updating supplier'
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $supplier = Supplier::where('team_id', $user->team->id)->find($id);

        if (!$supplier) {
            return response()->json([
                'error' => true,
                'message' => 'Supplier not found'
            ], 404);
        }

        try {
            ActivityLog::create([
                'log_type' => 'Delete',
                'model_type' => "Supplier",
                'model_id' => $supplier->id,
                'model_identifier' => $supplier->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Supplier {$supplier->name} deleted",
                'new_values' => $supplier->toArray()
            ]);

            $supplier->delete();

            return response()->json([
                'message' => 'Supplier deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error deleting supplier'
            ], 500);
        }
    }
    public function getStatement(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Find supplier with team scope
            $supplier = Supplier::where('team_id', $user->team->id)->findOrFail($id);
    
            // Set date range from supplier creation to now
            $startDate = $supplier->created_at->startOfDay();
            $endDate = now()->endOfDay();
    
            // Optional date range from request
            if ($request->has('start_date')) {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            }
            
            if ($request->has('end_date')) {
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            }
    
            $statement = [
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'current_balance' => $supplier->getCurrentBalance()
                ],
                'period' => [
                    'start_date' => $startDate->toISOString(),
                    'end_date' => $endDate->toISOString()
                ],
                'opening_balance' => $supplier->getOpeningBalance($startDate),
                'closing_balance' => $supplier->getCurrentBalance(),
                'transactions' => $supplier->getStatementTransactions($startDate, $endDate),
                'summary' => [
                    'total_purchases' => $supplier->getTotalPurchases($startDate, $endDate),
                    'total_payments' => $supplier->getTotalPayments($startDate, $endDate),
                    'outstanding_balance' => $supplier->getOutstandingBalance($startDate, $endDate)
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
                'message' => 'An error occurred while generating the statement: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getPurchases(Request $request, $id)
{
    $user = $request->user();

    $supplier = Supplier::where('team_id', $user->team->id)->find($id);

    if (!$supplier) {
        return response()->json([
            'error' => true,
            'message' => 'Supplier not found'
        ], 404);
    }

    // Build the query for purchases
    $query = $supplier->purchases();
    
    // Include related models if needed
    if ($request->has('with_items')) {
        $query->with(['items.product']);
    }
    
    // Apply date filters if provided
    if ($request->has('start_date')) {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $query->where('purchase_date', '>=', $startDate);
    }
    
    if ($request->has('end_date')) {
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        $query->where('purchase_date', '<=', $endDate);
    }
    
    // Sort by purchase date descending
    $query->orderBy('purchase_date', 'desc');
    
    // Paginate the results
    $perPage = $request->input('per_page', 15);
    $purchases = $query->paginate($perPage);

    return response()->json([
        'purchases' => $purchases
    ]);
}
public function getTransactions(Request $request, $id)
{
    $user = $request->user();
    $perPage = $request->input('per_page', 15); // Default to 15 items per page
    
    $supplier = Supplier::where('team_id', $user->team->id)->find($id);
    
    if (!$supplier) {
        return response()->json([
            'error' => true,
            'message' => 'Supplier not found'
        ], 404);
    }
    
    // Get all purchase IDs for this supplier
    $purchaseIds = $supplier->purchases()->pluck('id')->toArray();
    
    // Query transactions directly with pagination
    $transactions = DB::table('cash_transactions')
        ->whereIn('transactionable_id', $purchaseIds)
        ->where('transactionable_type', 'App\Models\Purchase')
        ->orderBy('transaction_date', 'desc')
        ->paginate($perPage);
    
    return response()->json([
        'transactions' => $transactions
    ]);
}


}
