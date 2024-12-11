<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
                  ->orWhere('tax_number', 'like', "%{$searchTerm}%");
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
        $user = $request->user();

        $client = Client::where('team_id', $user->team->id)->find($id);

        if (!$client) {
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
            $oldData = $client->toArray();
            $client->update($request->all());

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

            return response()->json([
                'message' => 'Client updated successfully',
                'client' => $client
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error updating client'
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

    public function getTransactions(Request $request, $id)
    {
        $user = $request->user();

        $client = Client::where('team_id', $user->team->id)->find($id);

        if (!$client) {
            return response()->json([
                'error' => true,
                'message' => 'Client not found'
            ], 404);
        }

        $transactions = $client->sales()
                             ->with('transactions')
                             ->get()
                             ->pluck('transactions')
                             ->flatten()
                             ->sortByDesc('transaction_date')
                             ->values();

        return response()->json([
            'transactions' => $transactions
        ]);
    }

    public function getStatement(Request $request, $id)
    {
        $user = $request->user();

        $client = Client::where('team_id', $user->team->id)->find($id);

        if (!$client) {
            return response()->json([
                'error' => true,
                'message' => 'Client not found'
            ], 404);
        }

        $startDate = $request->input('start_date', now()->subMonths(6));
        $endDate = $request->input('end_date', now());

        $statement = [
            'client' => $client,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'opening_balance' => $client->getOpeningBalance($startDate),
            'closing_balance' => $client->balance,
            'transactions' => $client->getStatementTransactions($startDate, $endDate),
            'summary' => [
                'total_sales' => $client->sales()
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->sum('total_amount'),
                'total_payments' => $client->sales()
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->sum('paid_amount'),
                'total_pending' => $client->sales()
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->where('payment_status', '!=', 'paid')
                    ->sum(DB::raw('total_amount - paid_amount'))
            ]
        ];

        return response()->json([
            'statement' => $statement
        ]);
    }
}