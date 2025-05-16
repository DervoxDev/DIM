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
     // Check if this is the first cash source for the team

     $count = CashSource::where('team_id', $user->team->id)->count();
     if ($count === 1) {
         // Set as default for all contexts
         $cashSource->update([
             'is_default_general' => true,
             'is_default_sales' => true,
             'is_default_purchases' => true,
             'is_default_payments' => true
         ]);
     }
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
                'is_default_general' => 'boolean',
                'is_default_sales' => 'boolean',
                'is_default_purchases' => 'boolean',
                'is_default_payments' => 'boolean'
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
                    'status'
                ]));
                
                // Handle default context fields
                $contexts = ['general', 'sales', 'purchases', 'payments'];
                foreach ($contexts as $context) {
                    $field = 'is_default_' . $context;
                    if (isset($request->$field)) {
                        if ($request->$field) {
                            $cashSource->setAsDefault($context);
                        } else {
                            // If setting to false and this was a default, find a new default
                            if ($cashSource->$field) {
                                $cashSource->$field = false;
                                
                                // Find another active cash source to make default
                                $newDefault = CashSource::where('team_id', $user->team->id)
                                    ->where('id', '!=', $cashSource->id)
                                    ->where('status', 'active')
                                    ->first();
                                    
                                if ($newDefault) {
                                    $newDefault->$field = true;
                                    $newDefault->save();
                                }
                            }
                        }
                    }
                }
                
                // For backward compatibility
                if (isset($request->is_default) && $request->is_default) {
                    $cashSource->setAsDefault('general');
                }
                
                $cashSource->save();
                
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
    
    public function destroy(Request $request, $id)
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
            
            // Check if this is the only active cash source
            $activeCount = CashSource::where('team_id', $user->team->id)
                ->where('status', 'active')
                ->count();
                
            if ($activeCount <= 1 && $cashSource->status === 'active') {
                return response()->json([
                    'error' => true,
                    'message' => 'Cannot delete the only active cash source. Please create another cash source first.'
                ], 422);
            }
            
            // Check if this is a default cash source for any context
            $isDefault = $cashSource->is_default_general || 
                        $cashSource->is_default_sales || 
                        $cashSource->is_default_purchases || 
                        $cashSource->is_default_payments;
            
            // If it's a default, find the next active cash source to make default
            if ($isDefault) {
                $nextDefault = CashSource::where('team_id', $user->team->id)
                    ->where('id', '!=', $id)
                    ->where('status', 'active')
                    ->first();
                    
                if ($nextDefault) {
                    $contexts = ['general', 'sales', 'purchases', 'payments'];
                    foreach ($contexts as $context) {
                        $field = 'is_default_' . $context;
                        if ($cashSource->$field) {
                            $nextDefault->$field = true;
                        }
                    }
                    $nextDefault->save();
                }
            }
            
            // Log the cash source details before deletion for record keeping
            $cashSourceDetails = $cashSource->toArray();
            
            // Delete the cash source (or soft delete if soft deletes are enabled)
            $cashSource->delete();
            
            ActivityLog::create([
                'log_type' => 'Delete',
                'model_type' => 'CashSource',
                'model_id' => $id,
                'model_identifier' => $cashSource->name,
                'user_identifier' => $user->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Cash source {$cashSource->name} was deleted",
                'old_values' => $cashSourceDetails,
            ]);
            
            \DB::commit();
            
            return response()->json([
                'message' => 'Cash source deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Cash source deletion failed:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error deleting cash source',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }
    
    
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user->team) {
            \Log::warning('No team found for user', ['user_id' => $user->id, 'method' => 'show']);
            return response()->json([
                'error' => true,
                'message' => 'No team found for the user'
            ], 404);
        }
        
        $teamId = $user->team->id;
        
        \Log::info('Getting cash source in show method', [
            'id' => $id,
            'user_id' => $user->id,
            'team_id' => $teamId
        ]);
        
        $cashSource = CashSource::where('team_id', $teamId)->find($id);
        
        if (!$cashSource) {
            \Log::warning('Cash source not found in show method', [
                'id' => $id, 
                'team_id' => $teamId
            ]);
            
            return response()->json([
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
 /**
 * Get default cash sources for all contexts
 */
/**
 * Get a specific cash source
 */
/**
 * Get default cash sources for all contexts
 */
public function getDefaults(Request $request)
{
    $user = $request->user();
    
    if (!$user->team) {
        \Log::warning('No team found for user', ['user_id' => $user->id, 'method' => 'getDefaults']);
        return response()->json([
            'error' => true,
            'message' => 'No team found for the user'
        ], 404);
    }
    
    $teamId = $user->team->id;
    
    \Log::info('Getting default cash sources', [
        'user_id' => $user->id,
        'team_id' => $teamId
    ]);
    
    return response()->json([
        'defaults' => [
            'general' => CashSource::getDefaultForTeam($teamId, 'general')?->id,
            'sales' => CashSource::getDefaultForTeam($teamId, 'sales')?->id,
            'purchases' => CashSource::getDefaultForTeam($teamId, 'purchases')?->id, 
            'payments' => CashSource::getDefaultForTeam($teamId, 'payments')?->id
        ]
    ]);
}


/**
 * Set a cash source as default for a specific context
 */
public function setDefault(Request $request, $id)
{
    $user = $request->user();
    
    if (!$user->team) {
        \Log::warning('No team found for user', ['user_id' => $user->id, 'method' => 'setDefault']);
        return response()->json([
            'error' => true,
            'message' => 'No team found for the user'
        ], 404);
    }
    
    $teamId = $user->team->id;
    
    \Log::info('Setting default cash source', [
        'id' => $id,
        'user_id' => $user->id,
        'team_id' => $teamId,
        'request_data' => $request->all(),
        'request_context' => $request->context
    ]);
    
    $cashSource = CashSource::where('team_id', $teamId)->find($id);
    
    if (!$cashSource) {
        \Log::warning('Cash source not found for set default', [
            'id' => $id, 
            'team_id' => $teamId,
            'query' => CashSource::where('team_id', $teamId)->toSql()
        ]);
        
        // Check if this cash source exists for any team
        $existsAtAll = CashSource::find($id);
        if ($existsAtAll) {
            \Log::warning('Source exists but for different team', [
                'requested_id' => $id,
                'actual_team_id' => $existsAtAll->team_id,
                'user_team_id' => $teamId
            ]);
        }
        
        return response()->json([
            'message' => 'Cash source not found'
        ], 404);
    }
    
    $validator = Validator::make($request->all(), [
        'context' => 'required|in:general,sales,purchases,payments'
    ]);
    
    if ($validator->fails()) {
        \Log::warning('Validation error setting default', ['errors' => $validator->errors()]);
        return response()->json([
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }
    
    try {
        $cashSource->setAsDefault($request->context);
        
        \Log::info('Default cash source updated', [
            'id' => $id,
            'context' => $request->context,
            'team_id' => $teamId,
            'name' => $cashSource->name
        ]);
        
        return response()->json([
            'message' => 'Default cash source updated successfully',
            'cash_source' => $cashSource
        ]);
    } catch (\Exception $e) {
        \Log::error('Error setting default cash source', [
            'id' => $id,
            'context' => $request->context,
            'team_id' => $teamId,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'message' => 'Error setting default: ' . $e->getMessage()
        ], 500);
    }
}

    
}
