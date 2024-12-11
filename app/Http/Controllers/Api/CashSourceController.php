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

    public function deposit(Request $request, $id)
    {
        $user = $request->user();
        
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

        try {
            $transaction = $cashSource->deposit(
                $request->amount,
                $request->description
            );

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

            return response()->json([
                'message' => 'Deposit successful',
                'cash_source' => $cashSource->fresh(),
                'transaction' => $transaction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error processing deposit'
            ], 500);
        }
    }

    public function withdraw(Request $request, $id)
    {
        $user = $request->user();
        
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

        try {
            $transaction = $cashSource->withdraw(
                $request->amount,
                $request->description
            );

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

            return response()->json([
                'message' => 'Withdrawal successful',
                'cash_source' => $cashSource->fresh(),
                'transaction' => $transaction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function transfer(Request $request, $id)
    {
        $user = $request->user();
        
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
            $success = $sourceAccount->transfer(
                $request->amount,
                $destinationAccount,
                $request->description
            );

            if ($success) {
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
                        'source_account' => $sourceAccount->toArray(),
                        'destination_account' => $destinationAccount->toArray(),
                        'amount' => $request->amount
                    ]
                ]);

                return response()->json([
                    'message' => 'Transfer successful',
                    'source_account' => $sourceAccount->fresh(),
                    'destination_account' => $destinationAccount->fresh()
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
