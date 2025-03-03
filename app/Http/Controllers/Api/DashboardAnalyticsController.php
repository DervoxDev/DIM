<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\CashSource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardAnalyticsController extends Controller
{
    private function getDateRange(Request $request)
    {
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        
        switch ($request->timeframe) {
            case 'daily':
                $startDate = Carbon::parse($request->start_date ?? $endDate)->startOfDay();
                $endDate = Carbon::parse($request->start_date ?? $endDate)->endOfDay();
                break;
            
            case '24hours':
                $startDate = $request->start_date ? 
                    Carbon::parse($request->start_date) : 
                    Carbon::now()->subHours(24);
                $endDate = $request->end_date ? 
                    Carbon::parse($request->end_date) : 
                    Carbon::now();
                break;

            case 'weekly':
                $startDate = $request->start_date ? 
                    Carbon::parse($request->start_date) : 
                    Carbon::now()->subDays(6)->startOfDay();
                $endDate = $request->end_date ? 
                    Carbon::parse($request->end_date) : 
                    Carbon::now()->endOfDay();
                break;

            case 'monthly':
                $startDate = $request->start_date ? 
                    Carbon::parse($request->start_date) : 
                    Carbon::now()->startOfMonth();
                $endDate = $request->end_date ? 
                    Carbon::parse($request->end_date) : 
                    Carbon::now()->endOfMonth();
                break;

            case 'yearly':
                $startDate = $request->start_date ? 
                    Carbon::parse($request->start_date) : 
                    Carbon::now()->startOfYear();
                $endDate = $request->end_date ? 
                    Carbon::parse($request->end_date) : 
                    Carbon::now()->endOfYear();
                break;

            case 'all':
                $startDate = $request->start_date ? 
                    Carbon::parse($request->start_date) : 
                    Carbon::createFromTimestamp(0);
                $endDate = $request->end_date ? 
                    Carbon::parse($request->end_date) : 
                    Carbon::now();
                break;

            default:
                $startDate = $request->start_date ? 
                    Carbon::parse($request->start_date) : 
                    Carbon::now()->subDays(30);
        }

        return [$startDate, $endDate];
    }

    private function getHistoryData($query, $timeframe, $startDate, $endDate)
    {
        switch ($timeframe) {
            case 'daily':
            case '24hours':
                $history = $query
                    ->selectRaw('HOUR(created_at) as period, COALESCE(SUM(total_amount), 0) as total')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->pluck('total', 'period')
                    ->toArray();
                
                return array_values(array_replace(array_fill(0, 24, 0), $history));

            case 'weekly':
                $history = $query
                    ->selectRaw('DAYOFWEEK(created_at) as period, COALESCE(SUM(total_amount), 0) as total')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->pluck('total', 'period')
                    ->toArray();
                
                return array_values(array_replace(array_fill(1, 7, 0), $history));

            case 'monthly':
                $daysInMonth = $endDate->daysInMonth;
                $history = $query
                    ->selectRaw('DAY(created_at) as period, COALESCE(SUM(total_amount), 0) as total')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->pluck('total', 'period')
                    ->toArray();
                
                return array_values(array_replace(array_fill(1, $daysInMonth, 0), $history));

            case 'yearly':
                $history = $query
                    ->selectRaw('MONTH(created_at) as period, COALESCE(SUM(total_amount), 0) as total')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->pluck('total', 'period')
                    ->toArray();
                
                return array_values(array_replace(array_fill(1, 12, 0), $history));

            case 'all':
                return $query
                    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COALESCE(SUM(total_amount), 0) as total')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->pluck('total')
                    ->toArray();

            default:
                return [];
        }
    }

    public function getSaleAnalytics(Request $request)
{
    try {
        $request->validate([
            'timeframe' => 'required|in:daily,24hours,weekly,monthly,yearly,all',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        [$startDate, $endDate] = $this->getDateRange($request);

        // Get sales summary for the period
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_revenue'),
                DB::raw('COALESCE(AVG(total_amount), 0) as average_sale'),
                DB::raw('COUNT(DISTINCT id) as total_orders')
            )
            ->first();

        // Get all-time stats
        $allTimeStats = Sale::select(
            DB::raw('COUNT(*) as total_sales'),
            DB::raw('COALESCE(SUM(total_amount), 0) as total_revenue'),
            DB::raw('COALESCE(AVG(total_amount), 0) as average_sale'),
            DB::raw('COUNT(DISTINCT id) as total_orders')
        )->first();

        // Get top products for the period
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                'products.description',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales.id) as total_orders')
            )
            ->groupBy('products.id', 'products.name', 'products.description')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description ?? 'No description',
                    'total_quantity' => $product->total_quantity,
                    'total_revenue' => number_format($product->total_revenue, 2),
                    'total_orders' => $product->total_orders,
                    'icon' => 'package' // or any default icon you want
                ];
            });

        // Get history data
        $historyQuery = Sale::whereBetween('created_at', [$startDate, $endDate]);
        $history = $this->getHistoryData($historyQuery, $request->timeframe, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_sales' => $sales->total_sales,
                    'total_revenue' => $sales->total_revenue,
                    'average_sale' => $sales->average_sale,
                    'total_orders' => $sales->total_orders
                ],
                'all_time' => [
                    'total_sales' => $allTimeStats->total_sales,
                    'total_revenue' => $allTimeStats->total_revenue,
                    'average_sale' => $allTimeStats->average_sale,
                    'total_orders' => $allTimeStats->total_orders
                ],
                'history' => $history,
                'top_products' => $topProducts, // Added this line
                'period_info' => [
                    'timeframe' => $request->timeframe,
                    'start_date' => $startDate->toDateTimeString(),
                    'end_date' => $endDate->toDateTimeString()
                ]
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Sales Analytics failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    
    public function getPurchaseAnalytics(Request $request)
    {
        try {
            $request->validate([
                'timeframe' => 'required|in:daily,24hours,weekly,monthly,yearly,all',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);
    
            [$startDate, $endDate] = $this->getDateRange($request);
    
            // Get purchases summary for the period
            $purchases = Purchase::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('COUNT(*) as total_purchases'),
                    DB::raw('COALESCE(SUM(total_amount), 0) as total_cost'),
                    DB::raw('COALESCE(AVG(total_amount), 0) as average_purchase')
                )
                ->first();
    
            // Get all-time stats
            $allTimeStats = Purchase::select(
                DB::raw('COUNT(*) as total_purchases'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_cost'),
                DB::raw('COALESCE(AVG(total_amount), 0) as average_purchase')
            )->first();
    
            // Get history data
            $historyQuery = Purchase::whereBetween('created_at', [$startDate, $endDate]);
            $history = $this->getHistoryData($historyQuery, $request->timeframe, $startDate, $endDate);
    
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_purchases' => $purchases->total_purchases,
                        'total_cost' => $purchases->total_cost,
                        'average_purchase' => $purchases->average_purchase
                    ],
                    'all_time' => [
                        'total_purchases' => $allTimeStats->total_purchases,
                        'total_cost' => $allTimeStats->total_cost,
                        'average_purchase' => $allTimeStats->average_purchase
                    ],
                    'history' => $history,
                    'period_info' => [
                        'timeframe' => $request->timeframe,
                        'start_date' => $startDate->toDateTimeString(),
                        'end_date' => $endDate->toDateTimeString()
                    ]
                ]
            ]);
    
        } catch (\Exception $e) {
            Log::error('Purchase Analytics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    public function getInventoryAnalytics(Request $request)
    {
        try {
            $request->validate([
                'timeframe' => 'required|in:daily,24hours,weekly,monthly,yearly,all',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);
    
            [$startDate, $endDate] = $this->getDateRange($request);
    
            // Current inventory stats
            $currentInventory = Product::select(
                DB::raw('COUNT(*) as total_products'),
                DB::raw('SUM(quantity) as total_stock'),
                DB::raw('AVG(quantity) as average_stock')
            )->first();
    
            // Get stock movements for the period
            $movements = DB::table('stock_movements')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(CASE WHEN type = "in" THEN quantity ELSE -quantity END) as net_change')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
    
            // Get low stock alerts
            $lowStock = Product::where('quantity', '<=', DB::raw('min_stock_level'))
                ->select('id', 'name', 'description', 'quantity', 'min_stock_level')
                ->get()
                ->map(function ($product) {
                    return [
                        'name' => $product->name,
                        'description' => $product->description ?? "Low stock alert",
                        'quantity' => $product->quantity,
                        'min_stock_level' => $product->min_stock_level,
                        'icon' => 'package-down'
                    ];
                });
    
            return response()->json([
                'success' => true,
                'data' => [
                    'current' => [
                        'total_products' => $currentInventory->total_products,
                        'total_stock' => $currentInventory->total_stock,
                        'average_stock' => number_format($currentInventory->average_stock, 4)
                    ],
                    'movements' => $movements,
                    'low_stock_alerts' => $lowStock,
                    'period_info' => [
                        'timeframe' => $request->timeframe,
                        'start_date' => $startDate->toDateTimeString(),
                        'end_date' => $endDate->toDateTimeString()
                    ]
                ]
            ]);
    
        } catch (\Exception $e) {
            Log::error('Inventory Analytics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    public function getCustomerAnalytics(Request $request)
    {
        try {
            $request->validate([
                'timeframe' => 'required|in:daily,24hours,weekly,monthly,yearly,all',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);
    
            [$startDate, $endDate] = $this->getDateRange($request);
    
            // Period statistics
            $periodStats = Sale::whereBetween('sales.created_at', [$startDate, $endDate])
                ->join('clients', 'sales.client_id', '=', 'clients.id')
                ->select(
                    DB::raw('COUNT(DISTINCT clients.id) as active_customers'),
                    DB::raw('COALESCE(SUM(sales.total_amount), 0) as period_revenue'),
                    DB::raw('COALESCE(AVG(sales.total_amount), 0) as average_sale')
                )
                ->whereNull('sales.deleted_at')
                ->first();
    
            // All-time statistics
            $allTimeStats = Sale::join('clients', 'sales.client_id', '=', 'clients.id')
                ->select(
                    DB::raw('COUNT(DISTINCT clients.id) as total_customers'),
                    DB::raw('COALESCE(SUM(sales.total_amount), 0) as total_revenue'),
                    DB::raw('COALESCE(AVG(sales.total_amount), 0) as average_sale')
                )
                ->whereNull('sales.deleted_at')
                ->first();
    
            // Top customers for the period
            $topCustomers = Sale::whereBetween('sales.created_at', [$startDate, $endDate])
                ->join('clients', 'sales.client_id', '=', 'clients.id')
                ->select(
                    'clients.id',
                    'clients.name',
                    'clients.email',
                    DB::raw('COUNT(*) as total_purchases'),
                    DB::raw('COALESCE(SUM(sales.total_amount), 0) as total_spent'),
                    DB::raw('MAX(sales.created_at) as last_purchase')
                )
                ->whereNull('sales.deleted_at')
                ->groupBy('clients.id', 'clients.name', 'clients.email')
                ->orderBy('total_spent', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($customer) {
                    return [
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'description' => "Loyal customer since " . Carbon::parse($customer->last_purchase)->format('Y'),
                        'total_purchases' => $customer->total_purchases,
                        'total_spent' => number_format($customer->total_spent, 2),
                        'last_purchase' => $customer->last_purchase,
                        'icon' => 'user'
                    ];
                });
    
            return response()->json([
                'success' => true,
                'data' => [
                    'period_stats' => [
                        'active_customers' => $periodStats->active_customers,
                        'period_revenue' => number_format($periodStats->period_revenue, 2),
                        'average_sale' => number_format($periodStats->average_sale, 2)
                    ],
                    'all_time' => [
                        'total_customers' => $allTimeStats->total_customers,
                        'total_revenue' => number_format($allTimeStats->total_revenue, 2),
                        'average_sale' => number_format($allTimeStats->average_sale, 2)
                    ],
                    'top_customers' => $topCustomers,
                    'period_info' => [
                        'timeframe' => $request->timeframe,
                        'start_date' => $startDate->toDateTimeString(),
                        'end_date' => $endDate->toDateTimeString()
                    ]
                ]
            ]);
    
        } catch (\Exception $e) {
            Log::error('Customer Analytics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    
    public function getOverallDashboard(Request $request)
    {
        try {
            $request->validate([
                'timeframe' => 'required|in:daily,24hours,weekly,monthly,yearly,all',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);
    
            [$startDate, $endDate] = $this->getDateRange($request);
    
            // Period statistics
            $periodStats = DB::transaction(function () use ($startDate, $endDate) {
                $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total_amount');
    
                $purchases = Purchase::whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total_amount');
    
                $orders = Sale::whereBetween('created_at', [$startDate, $endDate])
                    ->count();
    
                return [
                    'sales' => $sales ?? 0,
                    'purchases' => $purchases ?? 0,
                    'orders' => $orders ?? 0
                ];
            });
    
            // All-time statistics
            $allTimeStats = DB::transaction(function () {
                return [
                    'total_sales' => Sale::sum('total_amount') ?? 0,
                    'total_purchases' => Purchase::sum('total_amount') ?? 0,
                    'total_orders' => Sale::count() ?? 0
                ];
            });
    
            // Current status
            $currentStatus = [
                'low_stock_alerts' => Product::where('quantity', '<=', DB::raw('min_stock_level'))->count(),
                'cash_balance' => CashSource::sum('balance') ?? 0
            ];
    
            return response()->json([
                'success' => true,
                'data' => [
                    'period_stats' => $periodStats,
                    'all_time' => $allTimeStats,
                    'current_status' => $currentStatus,
                    'period_info' => [
                        'timeframe' => $request->timeframe,
                        'start_date' => $startDate->toDateTimeString(),
                        'end_date' => $endDate->toDateTimeString()
                    ]
                ]
            ]);
    
        } catch (\Exception $e) {
            Log::error('Overall Dashboard Analytics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
}
