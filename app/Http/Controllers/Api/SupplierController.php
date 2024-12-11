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
}
