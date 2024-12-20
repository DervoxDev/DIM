<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\InvoiceItem;
use App\Models\ActivityLog;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
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

        $query = Purchase::where('team_id', $user->team->id)
                        ->with(['supplier', 'items.product', 'cashSource']);

        // Date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('purchase_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Payment status filter
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Supplier filter
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Search by reference number
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('reference_number', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%");
            });
        }

        // Sort
        $sortField = $request->get('sort_by', 'purchase_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $purchases = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'purchases' => $purchases
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
            'supplier_id' => 'required|exists:suppliers,id',
            'cash_source_id' => 'required|exists:cash_sources,id',
            'purchase_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:purchase_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.is_package' => 'boolean',
          //  'items.*.package_id' => 'required_if:items.*.is_package,true|exists:product_packages,id',
            'items.*.update_prices' => 'required_if:items.*.is_package,false|boolean',
            'items.*.selling_price' => 'required_if:items.*.update_prices,true|numeric|min:0',
            'items.*.update_package_prices' => 'required_if:items.*.is_package,true|boolean',
            'items.*.package_purchase_price' => 'required_if:items.*.update_package_prices,true|numeric|min:0',
            'items.*.package_selling_price' => 'required_if:items.*.update_package_prices,true|numeric|min:0',
        ]);
        

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create purchase
        // Create purchase with initial values
        $purchase = new Purchase();
        $purchase->team_id = $request->user()->team->id;
        $purchase->supplier_id = $request->supplier_id;
        $purchase->cash_source_id = $request->cash_source_id;
        $purchase->reference_number = 'PUR-' . str_pad(Purchase::max('id') + 1, 6, '0', STR_PAD_LEFT);
        $purchase->purchase_date = $request->purchase_date;
        $purchase->due_date = $request->due_date;
        $purchase->notes = $request->notes;
        $purchase->status = 'pending';
        $purchase->payment_status = 'unpaid';
        $purchase->total_amount = 0;
        $purchase->paid_amount = 0;
        $purchase->tax_amount = 0;
        $purchase->discount_amount = 0;
        $purchase->save();

        // Create purchase items and calculate totals
        $totalAmount = 0;
        $totalTax = 0;
        $totalDiscount = 0;  // Add this to track total discounts

        foreach ($request->items as $item) {
            \Log::info('Processing purchase item:', [
                'item_data' => $item,
                'is_package' => $item['is_package'] ?? false,
                'package_id' => $item['package_id'] ?? null
            ]);
            $purchaseItem = new PurchaseItem();
            $purchaseItem->purchase_id = $purchase->id;
            $purchaseItem->product_id = $item['product_id'];
            $purchaseItem->quantity = $item['quantity'];
            $purchaseItem->unit_price = $item['unit_price'];
            $purchaseItem->tax_rate = $item['tax_rate'] ?? 0;
            $purchaseItem->discount_amount = $item['discount_amount'] ?? 0;
            $purchaseItem->is_package = $item['is_package'] ?? false;
            
            // Only set package_id if it's a package and package_id is provided
            if ($purchaseItem->is_package && isset($item['package_id'])) {
                $purchaseItem->package_id = $item['package_id'];
            }
            
            $purchaseItem->calculateTotals();
            $purchaseItem->save();
        
            $totalAmount += $purchaseItem->total_price;
            $totalTax += $purchaseItem->tax_amount;
            $totalDiscount += $purchaseItem->discount_amount;
        
            $product = Product::find($item['product_id']);
            
            // Update stock
            if ($purchaseItem->is_package) {
                // If it's a package, multiply quantity by pieces per package
                $package = $product->packages()->find($item['package_id']);
                if ($package) {
                    $totalPieces = $item['quantity'] * $package->pieces_per_package;
                    $product->updateStock($totalPieces, 'add');
                }
            } else {
                $product->updateStock($item['quantity'], 'add');
            }
        
            // Update prices based on whether it's a package or piece
            if ($purchaseItem->is_package && $purchaseItem->package_id) {
                // Update package prices
                if (!empty($item['update_package_prices'])) {
                    $product->updatePackagePrices(
                        $purchaseItem->package_id,
                        $item['package_purchase_price'],
                        $item['package_selling_price']
                    );
                }
            } else {
                // Update product prices
                if (!empty($item['update_prices'])) {
                    $product->updatePrices(
                        $item['unit_price'],
                        $item['selling_price'] ?? null
                    );
                }
            }
        }
        

        // Update purchase totals
        $purchase->total_amount = $totalAmount;
        $purchase->tax_amount = $totalTax;
        $purchase->discount_amount = $totalDiscount;
        $purchase->save();
            // Log activity
            ActivityLog::create([
                'log_type' => 'Create',
                'model_type' => "Purchase",
                'model_id' => $purchase->id,
                'model_identifier' => $purchase->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Created purchase {$purchase->reference_number}",
                'new_values' => $purchase->toArray()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Purchase created successfully',
                'purchase' => $purchase->load(['items.product', 'supplier', 'cashSource'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error creating purchase',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $purchase = Purchase::where('team_id', $user->team->id)
                          ->with(['supplier', 'items.product', 'cashSource', 'transactions'])
                          ->find($id);

        if (!$purchase) {
            return response()->json([
                'error' => true,
                'message' => 'Purchase not found'
            ], 404);
        }

        return response()->json([
            'purchase' => $purchase
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
    
        $purchase = Purchase::where('team_id', $user->team->id)->find($id);
    
        if (!$purchase) {
            return response()->json([
                'error' => true,
                'message' => 'Purchase not found'
            ], 404);
        }
    
        if ($purchase->status === 'completed') {
            return response()->json([
                'error' => true,
                'message' => 'Cannot update completed purchase'
            ], 400);
        }
    
        $validator = Validator::make($request->all(), [
            'purchase_date' => 'sometimes|required|date',
            'due_date' => 'nullable|date|after_or_equal:purchase_date',
            'notes' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,completed,cancelled',
            'items' => 'sometimes|required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            DB::beginTransaction();
    
            // Store original payment status and old data
            $originalPaymentStatus = $purchase->payment_status;
            $oldData = $purchase->toArray();
    
            // Prepare purchase data with preserved payment status
            $purchaseData = array_merge($request->all(), [
                'payment_status' => $originalPaymentStatus
            ]);
    
            // Update purchase basic information
            $purchase->update($purchaseData);
    
            // Handle items if present in request
            if ($request->has('items')) {
                // First, reverse old quantities from stock
                foreach ($purchase->items as $oldItem) {
                    $product = Product::find($oldItem->product_id);
                    if ($product) {
                        if ($oldItem->is_package) {
                            $package = $product->packages()->find($oldItem->package_id);
                            if ($package) {
                                $totalPieces = $oldItem->quantity * $package->pieces_per_package;
                                $product->updateStock($totalPieces, 'subtract');
                            }
                        } else {
                            $product->updateStock($oldItem->quantity, 'subtract');
                        }
                    }
                }
            
                // Delete old items
                $purchase->items()->delete();
            
                // Add new items and update stock
                $totalAmount = 0;
                $totalTax = 0;
                $totalDiscount = 0;
            
                foreach ($request->items as $item) {
                    $purchaseItem = new PurchaseItem();
                    $purchaseItem->purchase_id = $purchase->id;
                    $purchaseItem->product_id = $item['product_id'];
                    $purchaseItem->quantity = $item['quantity'];
                    $purchaseItem->unit_price = $item['unit_price'];
                    $purchaseItem->tax_rate = $item['tax_rate'] ?? 0;
                    $purchaseItem->discount_amount = $item['discount_amount'] ?? 0;
                    $purchaseItem->is_package = $item['is_package'] ?? false;
                    
                    if ($purchaseItem->is_package) {
                        $purchaseItem->package_id = $item['package_id'];
                    }
                    
                    $purchaseItem->calculateTotals();
                    $purchaseItem->save();
            
                    $totalAmount += $purchaseItem->total_price;
                    $totalTax += $purchaseItem->tax_amount;
                    $totalDiscount += $purchaseItem->discount_amount;
            
                    // Update product stock
                    $product = Product::find($item['product_id']);
                    if ($purchaseItem->is_package) {
                        $package = $product->packages()->find($item['package_id']);
                        if ($package) {
                            $totalPieces = $item['quantity'] * $package->pieces_per_package;
                            $product->updateStock($totalPieces, 'add');
                        }
                    } else {
                        $product->updateStock($item['quantity'], 'add');
                    }
                }
            
                // Update purchase totals
                $purchase->total_amount = $totalAmount;
                $purchase->tax_amount = $totalTax;
                $purchase->discount_amount = $totalDiscount;
                $purchase->save();
            }
            
            // Log activity
            ActivityLog::create([
                'log_type' => 'Update',
                'model_type' => "Purchase",
                'model_id' => $purchase->id,
                'model_identifier' => $purchase->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Updated purchase {$purchase->reference_number}",
                'old_values' => $oldData,
                'new_values' => $purchase->fresh()->toArray()
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Purchase updated successfully',
                'purchase' => $purchase->fresh(['items.product', 'supplier', 'cashSource'])
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Purchase update failed: ' . $e->getMessage(), [
                'purchase_id' => $id,
                'user_id' => $user->id,
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'error' => true,
                'message' => 'Error updating purchase: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function addPayment(Request $request, $id)
    {
        $user = $request->user();

        $purchase = Purchase::where('team_id', $user->team->id)->find($id);

        if (!$purchase) {
            return response()->json([
                'error' => true,
                'message' => 'Purchase not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
           // 'payment_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            $referenceNumber = $request->reference_number ?? 
            'PAY-' . date('Ymd-His') . '-' . str_pad($purchase->id, 4, '0', STR_PAD_LEFT);

            $transaction = $purchase->addPayment($request->amount, $purchase->cashSource, $referenceNumber);

            ActivityLog::create([
                'log_type' => 'Payment',
                'model_type' => "Purchase",
                'model_id' => $purchase->id,
                'model_identifier' => $purchase->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Added payment of {$request->amount} to purchase {$purchase->reference_number}",
                'new_values' => $transaction->toArray()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment added successfully',
                'purchase' => $purchase->fresh(['items.product', 'supplier', 'cashSource']),
                'transaction' => $transaction
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function generateInvoice(Request $request, $id)
    {
        $user = $request->user();
        $model = Purchase::where('team_id', $user->team->id)->with(['items', 'supplier'])->find($id);
        // Or for Sale: 
        // $model = Sale::where('team_id', $user->team->id)->with(['items', 'client'])->find($id);
    
        if (!$model) {
            return response()->json([
                'error' => true,
                'message' => 'Record not found'
            ], 404);
        }
        // Get the last used number from the reference number
        $lastInvoice = Invoice::where('team_id', $user->team->id)
        ->withTrashed() // Include soft deleted records
        ->where('reference_number', 'like', "INV-" . date('Y') . "-%")
        ->orderBy('id', 'desc')
        ->first();

        // Extract the numeric part and increment
        if ($lastInvoice) {
        $lastNumber = (int) substr($lastInvoice->reference_number, -6);
        $nextNumber = $lastNumber + 1;
        } else {
        $nextNumber = 1;
        }

        // Generate new reference number
        $referenceNumber = "INV-" . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        // Verify uniqueness
        while (Invoice::where('reference_number', $referenceNumber)->withTrashed()->exists()) {
        $nextNumber++;
        $referenceNumber = "INV-" . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        }
        $nextId = ($lastInvoice ? $lastInvoice->id : 0) + 1;
        $nextInvoiceableId = ($lastInvoice ? $lastInvoice->invoiceable_id : 0) + 1;


        try {
            DB::beginTransaction();
    
            $invoice = new Invoice();
            $invoice->team_id = $user->team->id;
            $invoice->invoiceable_type = get_class($model);
            $invoice->invoiceable_id = $model->id;
            $invoice->reference_number = $referenceNumber;
            $invoice->total_amount = $model->total_amount;
            $invoice->tax_amount = $model->tax_amount;
            $invoice->discount_amount = $model->discount_amount;
            $invoice->status = 'draft';
            $invoice->issue_date = now();
            $invoice->due_date = $model->due_date;
            
            // Set meta_data
            $invoice->meta_data = [
                'source_type' => $model instanceof Purchase ? 'purchase' : 'sale',
                'source_reference' => $model->reference_number,
                'source_date' => $model->created_at,
                'contact' => $model instanceof Purchase 
                    ? ['type' => 'supplier', 'data' => $model->supplier->toArray()]
                    : ['type' => 'client', 'data' => $model->client->toArray()],
                'items_data' => $model->items->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate' => $item->tax_rate,
                        'discount_amount' => $item->discount_amount,
                        'total_price' => $item->total_price,
                        'is_package' => $item->is_package ?? false,
                        'package_id' => $item->package_id ?? null,
                        'total_pieces' => $item->total_pieces ?? $item->quantity
                    ];
                })->toArray()
            ];
            
            $invoice->save();
    
            // Create invoice items
            foreach ($model->items as $sourceItem) {
                $invoice->items()->create([
                    'description' => $sourceItem->product->name,
                    'quantity' => $sourceItem->quantity,
                    'unit_price' => $sourceItem->unit_price,
                    'total_price' => $sourceItem->total_price,
                    'notes' => $sourceItem->notes ?? ''
                ]);
            }
    
            ActivityLog::create([
                'log_type' => 'Create',
                'model_type' => "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Generated invoice {$invoice->reference_number} from " . 
                               ($model instanceof Purchase ? "purchase" : "sale") . 
                               " {$model->reference_number}",
                'new_values' => $invoice->toArray()
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Invoice generated successfully',
                'invoice' => $invoice->load('items')
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error generating invoice: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function getSummary(Request $request)
    {
        $user = $request->user();

        if (!$user->team) {
            return response()->json([
                'error' => true,
                'message' => 'No team found for the user'
            ], 404);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now()->endOfMonth());

            $summary = [
                'total_purchases' => Purchase::where('team_id', $user->team->id)
                    ->whereBetween('purchase_date', [$startDate, $endDate])
                    ->count(),

                'total_amount' => Purchase::where('team_id', $user->team->id)
                    ->whereBetween('purchase_date', [$startDate, $endDate])
                    ->sum('total_amount'),

                'total_paid' => Purchase::where('team_id', $user->team->id)
                    ->whereBetween('purchase_date', [$startDate, $endDate])
                    ->sum('paid_amount'),

                'purchases_by_status' => Purchase::where('team_id', $user->team->id)
                    ->whereBetween('purchase_date', [$startDate, $endDate])
                    ->select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get(),

                'purchases_by_payment_status' => Purchase::where('team_id', $user->team->id)
                    ->whereBetween('purchase_date', [$startDate, $endDate])
                    ->select('payment_status', DB::raw('COUNT(*) as count'))
                    ->groupBy('payment_status')
                    ->get(),

                'top_suppliers' => Purchase::where('team_id', $user->team->id)
                    ->whereBetween('purchase_date', [$startDate, $endDate])
                    ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                    ->select('suppliers.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total_amount'))
                    ->groupBy('suppliers.id', 'suppliers.name')
                    ->orderBy('total_amount', 'desc')
                    ->limit(5)
                    ->get()
            ];

            return response()->json([
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error generating summary'
            ], 500);
        }
    }
}