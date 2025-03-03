<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Get paginated activity logs
     * Supports filtering and sorting
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Validate and sanitize input
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'log_type' => 'string|nullable',
            'model_type' => 'string|nullable',
            'model_identifier' => 'string|nullable',
            'user_identifier' => 'string|nullable',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'sort_by' => 'in:created_at,log_type|nullable',
            'sort_direction' => 'in:asc,desc|nullable'
        ]);

        // Start with base query
        $query = ActivityLog::query();

        // Apply filters
        if ($request->has('log_type')) {
            $query->where('log_type', $request->log_type);
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->has('model_identifier')) {
            $query->where('model_identifier', 'like', "%{$request->model_identifier}%");
        }

        if ($request->has('user_identifier')) {
            $query->where('user_identifier', 'like', "%{$request->user_identifier}%");
        }

        // Date range filtering
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $logs = $query->paginate($perPage);

        return response()->json([
            'logs' => $logs,
            'total' => $logs->total(),
            'current_page' => $logs->currentPage(),
            'per_page' => $logs->perPage(),
            'last_page' => $logs->lastPage()
        ]);
    }

    /**
     * Get detailed information about a specific log entry
     */
    public function show($id)
    {
        $log = ActivityLog::findOrFail($id);

        return response()->json([
            'log' => $log,
            'model_details' => $log->model // This uses the accessor we defined earlier
        ]);
    }

    /**
     * Get log statistics and summaries
     */
    public function statistics(Request $request)
    {
        $request->validate([
            'days' => 'integer|min:1|max:365|nullable'
        ]);

        // Default to last 30 days if no days specified
        $days = $request->get('days', 30);

        // Log type distribution
        $logTypeDistribution = ActivityLog::where('created_at', '>=', now()->subDays($days))
            ->groupBy('log_type')
            ->selectRaw('log_type, COUNT(*) as count')
            ->orderByDesc('count')
            ->get();

        // Most active models
        $mostActiveModels = ActivityLog::where('created_at', '>=', now()->subDays($days))
            ->groupBy('model_type', 'model_identifier')
            ->selectRaw('model_type, model_identifier, COUNT(*) as count')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Total logs count
        $totalLogsCount = ActivityLog::where('created_at', '>=', now()->subDays($days))->count();

        return response()->json([
            'total_logs' => $totalLogsCount,
            'log_type_distribution' => $logTypeDistribution,
            'most_active_models' => $mostActiveModels,
            'analysis_period_days' => $days
        ]);
    }

    /**
     * Get unique log types and model types for filtering
     */
    public function filterOptions()
    {
        $logTypes = ActivityLog::select('log_type')
            ->distinct()
            ->pluck('log_type');

        $modelTypes = ActivityLog::select('model_type')
            ->distinct()
            ->pluck('model_type');

        return response()->json([
            'log_types' => $logTypes,
            'model_types' => $modelTypes
        ]);
    }

    /**
     * Delete old activity logs (admin-only method)
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:7|max:365'
        ]);

        $days = $request->input('days');
        $deletedCount = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'message' => 'Logs cleaned up successfully',
            'deleted_logs_count' => $deletedCount
        ]);
    }
}