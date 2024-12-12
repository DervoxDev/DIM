<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashSource;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CashSourceController extends Controller
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

        $query = CashSource::where('team_id', $user->team->id);

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('type', 'like', "%{$searchTerm}%")
                  ->orWhere('account_number', 'like', "%{$searchTerm}%");
            });
        }

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $cashSources = $query->paginate(15);

        return response()->json([
            'cash_sources' => $cashSources
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
            'type' => 'required|string|in:cash,bank,other',
            'initial_balance' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'account_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cashSource = new CashSource($request->all());
            $cashSource->team_id = $user->team->id;
            $cashSource->balance = $request->initial_balance;
            $cashSource->save();

            ActivityLog::create([
                'log_type' => 'Create',
                'model_type' => "CashSource",
                'model_id' => $cashSource->id,
                'model_identifier' => $cashSource->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Cash source {$cashSource->name} created",
                'new_values' => $cashSource->toArray()
            ]);

            return response()->json([
                'message' => 'Cash source created successfully',
                'cash_source' => $cashSource
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error creating cash source'
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        try {
            \DB::beginTransaction();
            
            // Log the incoming request
            \Log::info('Cash source update request:', [
                'id' => $id,
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);
            
            $cashSource = CashSource::where('team_id', $user->team->id)->find($id);
        
            if (!$cashSource) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cash source not found'
                ], 404);
            }
        
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:cash,bank,other',
                'description' => 'nullable|string',
                'account_number' => 'nullable|string',
                'bank_name' => 'nullable|string',
                'status' => 'required|string|in:active,inactive',
                'is_default' => 'boolean'
            ]);
        
            if ($validator->fails()) {
                \Log::warning('Cash source update validation failed:', [
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'error' => true,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
        
            try {
                $oldValues = $cashSource->toArray();
                
                // Update the cash source
                $cashSource->fill($request->only([
                    'name',
                    'type',
                    'description',
                    'account_number',
                    'bank_name',
                    'status',
                    'is_default'
                ]));
                
                $cashSource->save();
                
                // Handle default status
                if ($cashSource->is_default) {
                    CashSource::where('team_id', $user->team->id)
                             ->where('id', '!=', $cashSource->id)
                             ->update(['is_default' => false]);
                }
                
                // Log the successful update
                \Log::info('Cash source updated successfully:', [
                    'id' => $cashSource->id,
                    'old_values' => $oldValues,
                    'new_values' => $cashSource->toArray()
                ]);
                
                ActivityLog::create([
                    'log_type' => 'Update',
                    'model_type' => 'CashSource',
                    'model_id' => $cashSource->id,
                    'model_identifier' => $cashSource->name,
                    'user_identifier' => $user->name,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Cash source {$cashSource->name} updated",
                    'old_values' => $oldValues,
                    'new_values' => $cashSource->toArray()
                ]);
                
                \DB::commit();
                
                return response()->json([
                    'message' => 'Cash source updated successfully',
                    'cash_source' => $cashSource
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Error saving cash source:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Cash source update failed:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error updating cash source',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }
    
    
    
    public function destroy($id)
    {
        $user = request()->user();
        
        $cashSource = CashSource::where('team_id', $user->team->id)->find($id);
    
        if (!$cashSource) {
            return response()->json([
                'error' => true,
                'message' => 'Cash source not found'
            ], 404);
        }
    
        try {
            $oldValues = $cashSource->toArray();
            $cashSource->delete();
    
            ActivityLog::create([
                'log_type' => 'Delete',
                'model_type' => "CashSource",
                'model_id' => $cashSource->id,
                'model_identifier' => $cashSource->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'description' => "Cash source {$cashSource->name} deleted",
                'old_values' => $oldValues
            ]);
    
            return response()->json([
                'message' => 'Cash source deleted successfully'
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error deleting cash source'
            ], 500);
        }
    }
    
    public function show($id)
    {
        $user = request()->user();
        
        $cashSource = CashSource::where('team_id', $user->team->id)->find($id);
    
        if (!$cashSource) {
            return response()->json([
                'error' => true,
                'message' => 'Cash source not found'
            ], 404);
        }
    
        return response()->json([
            'cash_source' => $cashSource
        ]);
    }
    
    public function deposit(Request $request, $id)
    {
        $user = $request->user();
        
        try {
            \DB::beginTransaction();
            
            $cashSource = CashSource::where('team_id', $user->team->id)->find($id);
    
            if (!$cashSource) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cash source not found'
                ], 404);
            }
    
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Log the incoming request data
            \Log::info('Deposit request:', [
                'cash_source_id' => $id,
                'amount' => $request->amount,
                'description' => $request->description
            ]);
    
            try {
                $transaction = $cashSource->deposit(
                    $request->amount,
                    $request->description
                );
    
                // Log the created transaction
                \Log::info('Transaction created:', $transaction->toArray());
    
                ActivityLog::create([
                    'log_type' => 'Deposit',
                    'model_type' => "CashSource",
                    'model_id' => $cashSource->id,
                    'model_identifier' => $cashSource->name,
                    'user_identifier' => $user?->name,
                    'user_id' => $user->id,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Deposit of {$request->amount} to {$cashSource->name}",
                    'new_values' => $transaction->toArray()
                ]);
    
                \DB::commit();
    
                return response()->json([
                    'message' => 'Deposit successful',
                    'cash_source' => $cashSource->fresh(),
                    'transaction' => $transaction->load(['cashSource']) // Load the relationship
                ]);
    
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Transaction creation error: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
    
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Deposit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'error' => true,
                'message' => 'Error processing deposit',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    public function withdraw(Request $request, $id)
    {
        $user = $request->user();
        
        try {
            \DB::beginTransaction();
            
            $cashSource = CashSource::where('team_id', $user->team->id)->find($id);
    
            if (!$cashSource) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cash source not found'
                ], 404);
            }
    
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Log the incoming request data
            \Log::info('Withdrawal request:', [
                'cash_source_id' => $id,
                'amount' => $request->amount,
                'description' => $request->description
            ]);
    
            try {
                $transaction = $cashSource->withdraw(
                    $request->amount,
                    $request->description
                );
    
                // Log the created transaction
                \Log::info('Transaction created:', $transaction->toArray());
    
                ActivityLog::create([
                    'log_type' => 'Withdrawal',
                    'model_type' => "CashSource",
                    'model_id' => $cashSource->id,
                    'model_identifier' => $cashSource->name,
                    'user_identifier' => $user?->name,
                    'user_id' => $user->id,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Withdrawal of {$request->amount} from {$cashSource->name}",
                    'new_values' => $transaction->toArray()
                ]);
    
                \DB::commit();
    
                return response()->json([
                    'message' => 'Withdrawal successful',
                    'cash_source' => $cashSource->fresh(),
                    'transaction' => $transaction->load(['cashSource'])
                ]);
    
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Transaction creation error: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
    
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Withdrawal error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(), // Return the actual error message for insufficient funds
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 400); // Use 400 for insufficient funds error
        }
    }
    
    public function transfer(Request $request, $id)
    {
        $user = $request->user();
        
        try {
            \DB::beginTransaction();
            
            $sourceAccount = CashSource::where('team_id', $user->team->id)->find($id);
            
            if (!$sourceAccount) {
                return response()->json([
                    'error' => true,
                    'message' => 'Source cash account not found'
                ], 404);
            }
    
            $validator = Validator::make($request->all(), [
                'destination_id' => 'required|exists:cash_sources,id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            $destinationAccount = CashSource::where('team_id', $user->team->id)
                                          ->find($request->destination_id);
    
            if (!$destinationAccount) {
                return response()->json([
                    'error' => true,
                    'message' => 'Destination cash account not found'
                ], 404);
            }
    
            try {
                $transaction = $sourceAccount->transfer(
                    $request->amount,
                    $destinationAccount,
                    $request->description
                );
    
                ActivityLog::create([
                    'log_type' => 'Transfer',
                    'model_type' => "CashSource",
                    'model_id' => $sourceAccount->id,
                    'model_identifier' => $sourceAccount->name,
                    'user_identifier' => $user?->name,
                    'user_id' => $user->id,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Transfer of {$request->amount} from {$sourceAccount->name} to {$destinationAccount->name}",
                    'new_values' => [
                        'transaction' => $transaction->toArray(),
                        'source_account' => $sourceAccount->toArray(),
                        'destination_account' => $destinationAccount->toArray()
                    ]
                ]);
    
                \DB::commit();
    
                return response()->json([
                    'message' => 'Transfer successful',
                    'transaction' => $transaction->load(['cashSource', 'transferDestination']),
                    'source_account' => $sourceAccount->fresh(),
                    'destination_account' => $destinationAccount->fresh()
                ]);
    
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Transfer error: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage(),
                    'details' => config('app.debug') ? $e->getMessage() : null
                ], 400);
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Transfer error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'error' => true,
                'message' => 'Error processing transfer',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    

}
