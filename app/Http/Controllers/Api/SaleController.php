<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\ActivityLog;
use App\Models\ProductPackage;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\CashSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
class SaleController extends Controller
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
    
        $query = Sale::where('team_id', $user->team->id)
                    ->with(['client', 'items.product', 'cashSource']);
    
        // Type filter - sales, quotes, or both
        if ($request->has('type') && in_array($request->type, ['sale', 'quote'])) {
            $query->where('type', $request->type);
        }
    
        // Date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('sale_date', [
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
    
        // Client filter
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }
    
        // Search by reference number
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('reference_number', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%");
            });
        }
        
        // Primary Sort
        $primarySortField = $request->get('sort_by', 'reference_number');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        // Apply main sorting
        $query->orderBy($primarySortField, $sortDirection);
        
        // Apply secondary sorting (always sort by these fields secondarily)
        if ($primarySortField !== 'sale_date') {
            $query->orderBy('sale_date', 'asc');
        }
        
        if ($primarySortField !== 'created_at') {
            $query->orderBy('created_at', 'desc');
        }
        
        // For the case where primary sort is sale_date, 
        // we can additionally sort sales happening on the same date
        if ($primarySortField === 'sale_date') {
            $query->orderBy('id', $sortDirection); // Using ID ensures consistent ordering
        }
    
        $sales = $query->paginate($request->input('per_page', 15));
    
        return response()->json([
            'sales' => $sales
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
            'client_id' => 'nullable|exists:clients,id',
            'cash_source_id' => 'required|exists:cash_sources,id',
            'type' => 'nullable|in:sale,quote',  // Add validation for the new type field
            'sale_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:sale_date',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,completed,cancelled',
            'payment_status' => 'required|in:paid,partial,unpaid',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.is_package' => 'required|boolean',
            'items.*.package_id' => 'required_if:items.*.is_package,true|exists:product_packages,id',
            'items.*.total_pieces' => 'required|integer|min:1',
            'auto_payment' => 'boolean',
            'payment_amount' => 'required_if:auto_payment,true|numeric|min:0',
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
            
            // Get the type from request or default to 'sale'
            $type = $request->type ?? 'sale';
            
            // Check stock availability first (only for sales, not quotes)
            if ($type === 'sale') {
                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) {
                        throw new \Exception("Product not found: {$item['product_id']}");
                    }
        
                    // Calculate total pieces needed based on whether it's a package or not
                    $totalPieces = $item['is_package'] 
                        ? ($item['quantity'] * ProductPackage::find($item['package_id'])->pieces_per_package)
                        : $item['quantity'];
        
                    if ($product->quantity < $totalPieces) {
                        throw new \Exception("Insufficient stock for product: {$product->name} (Need: {$totalPieces}, Available: {$product->quantity})");
                    }
                }
            }
            
            // Create sale/quote record
            $sale = new Sale();
            $sale->team_id = $user->team->id;
            $sale->client_id = $request->client_id;
            $sale->cash_source_id = $request->cash_source_id;
            
            // Use different prefixes based on type
            $sale->reference_number = $this->generateReferenceNumber($type);
            $sale->type = $type;
            $sale->sale_date = $request->sale_date;
            $sale->due_date = $request->due_date;
            $sale->notes = $request->notes;
            $sale->status = $request->status ?? 'pending';
            $sale->payment_status = $request->payment_status ?? 'unpaid';
            $sale->total_amount = $request->total_amount ?? 0;
            $sale->tax_amount = $request->tax_amount ?? 0;
            $sale->discount_amount = $request->discount_amount ?? 0;
            
            if (!$sale->save()) {
                throw new \Exception("Failed to save " . ($type === 'quote' ? 'quote' : 'sale'));
            }
            
            // Update stock (for sales only) and create sale items
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                try {
                    // Calculate total pieces
                    $totalPieces = $item['is_package']
                        ? ($item['quantity'] * ProductPackage::find($item['package_id'])->pieces_per_package)
                        : $item['quantity'];
        
                    // Only update stock for sales, skip for quotes
                    if ($type === 'sale') {
                        if (!$product->updateStock($totalPieces, 'subtract')) {
                            throw new \Exception("Failed to update stock for product: {$product->name}");
                        }
                    }
            
                    // Create sale item (same for both types)
                    $saleItem = new SaleItem();
                    $saleItem->sale_id = $sale->id;
                    $saleItem->product_id = $item['product_id'];
                    $saleItem->is_package = $item['is_package'];
                    $saleItem->package_id = $item['is_package'] ? $item['package_id'] : null;
                    $saleItem->quantity = $item['quantity'];
                    $saleItem->total_pieces = $totalPieces;
                    $saleItem->unit_price = $item['unit_price'];
                    $saleItem->tax_rate = $item['tax_rate'] ?? 0;
                    $saleItem->discount_amount = $item['discount_amount'] ?? 0;
                    
                    // Calculate totals before saving
                    $subtotal = $saleItem->quantity * $saleItem->unit_price;
                    $saleItem->tax_amount = ($subtotal * $saleItem->tax_rate) / 100;
                    $saleItem->total_price = $subtotal + $saleItem->tax_amount - $saleItem->discount_amount;
                    
                    if (!$saleItem->save()) {
                        throw new \Exception("Failed to save sale item");
                    }
                } catch (\Exception $e) {
                    throw new \Exception("Item processing failed for {$product->name}: " . $e->getMessage());
                }
            }
            
            // Recalculate sale totals
            $sale->calculateTotals();
              
            // Handle auto-payment if enabled (only for sales, not for quotes)
            if ($type === 'sale' && $request->auto_payment) {
                $referenceNumber = 'AUTO-PAY-' . date('YmdHis') . '-' . str_pad($sale->id, 4, '0', STR_PAD_LEFT);
                
                // Add payment using existing method
                $transaction = $sale->addPayment(
                    $request->payment_amount ?? $sale->total_amount,
                    $sale->cashSource,
                    now(),
                    $referenceNumber,
                    "Auto payment on sale creation"
                );
            }
    
            // Log activity with the right type
            ActivityLog::create([
                'log_type' => 'Create',
                'model_type' => $type === 'quote' ? "Quote" : "Sale",
                'model_id' => $sale->id,
                'model_identifier' => $sale->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => $type === 'quote' 
                    ? "Created quote {$sale->reference_number}" 
                    : "Created sale {$sale->reference_number}",
                'new_values' => $sale->toArray()
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => ($type === 'quote' ? 'Quote' : 'Sale') . ' created successfully',
                'sale' => $sale->load(['items.product', 'items.package', 'client', 'cashSource'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error(($request->type === 'quote' ? 'Quote' : 'Sale') . ' creation failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error creating ' . ($request->type === 'quote' ? 'quote' : 'sale'),
                'debug' => config('app.debug') ? $e->getMessage() : null,
                'details' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]
            ], 500);
        }
    }
    
    
    
    // public function store(Request $request)
    // {
    //     $user = $request->user();

    //     if (!$user->team) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'No team found for the user'
    //         ], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'client_id' => 'nullable|exists:clients,id',
    //         'cash_source_id' => 'required|exists:cash_sources,id',
    //         'sale_date' => 'required|date',
    //         'due_date' => 'nullable|date|after_or_equal:sale_date',
    //         'notes' => 'nullable|string',
    //         'items' => 'required|array|min:1',
    //         'items.*.product_id' => 'required|exists:products,id',
    //         'items.*.quantity' => 'required|integer|min:1',
    //         'items.*.unit_price' => 'required|numeric|min:0',
    //         'items.*.tax_rate' => 'nullable|numeric|min:0',
    //         'items.*.discount_amount' => 'nullable|numeric|min:0',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Validation error',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Check stock availability
    //         foreach ($request->items as $item) {
    //             $product = Product::find($item['product_id']);
    //             if ($product->quantity < $item['quantity']) {
    //                 return response()->json([
    //                     'error' => true,
    //                     'message' => "Insufficient stock for product: {$product->name}"
    //                 ], 400);
    //             }
    //         }

    //         // Create sale
    //         $sale = new Sale();
    //         $sale->team_id = $user->team->id;
    //         $sale->client_id = $request->client_id;
    //         $sale->cash_source_id = $request->cash_source_id;
    //         $sale->reference_number = 'SALE-' . str_pad(Sale::max('id') + 1, 6, '0', STR_PAD_LEFT);
    //         $sale->sale_date = $request->sale_date;
    //         $sale->due_date = $request->due_date;
    //         $sale->notes = $request->notes;
    //         $sale->status = 'pending';
    //         $sale->payment_status = 'unpaid';
    //         $sale->save();

    //         // Create sale items and update stock
    //         foreach ($request->items as $item) {
    //             $saleItem = new SaleItem();
    //             $saleItem->sale_id = $sale->id;
    //             $saleItem->product_id = $item['product_id'];
    //             $saleItem->quantity = $item['quantity'];
    //             $saleItem->unit_price = $item['unit_price'];
    //             $saleItem->tax_rate = $item['tax_rate'] ?? 0;
    //             $saleItem->discount_amount = $item['discount_amount'] ?? 0;
    //             $saleItem->calculateTotals();
    //             $saleItem->save();

    //             // Update product stock
    //             $product = Product::find($item['product_id']);
    //             $product->updateStock($item['quantity'], 'subtract');
    //         }

    //         // Calculate sale totals
    //         $sale->calculateTotals();

    //         // Log activity
    //         ActivityLog::create([
    //             'log_type' => 'Create',
    //             'model_type' => "Sale",
    //             'model_id' => $sale->id,
    //             'model_identifier' => $sale->reference_number,
    //             'user_identifier' => $user?->name,
    //             'user_id' => $user->id,
    //             'user_email' => $user?->email,
    //             'ip_address' => $request->ip(),
    //             'user_agent' => $request->userAgent(),
    //             'description' => "Created sale {$sale->reference_number}",
    //             'new_values' => $sale->toArray()
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Sale created successfully',
    //             'sale' => $sale->load(['items.product', 'client', 'cashSource'])
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Error creating sale',
    //             'debug' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function show(Request $request, $id)
    {
        $user = $request->user();
    
        $sale = Sale::where('team_id', $user->team->id)
                   ->with(['client', 'items.product', 'cashSource', 'transactions'])
                   ->find($id);
    
        if (!$sale) {
            return response()->json([
                'error' => true,
                'message' => 'Sale not found'
            ], 404);
        }
    
        // Calculate paid amount if not already set
        $sale->paid_amount = $sale->transactions->sum('amount');
    
        return response()->json([
            'sale' => $sale
        ]);
    }
    

    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        \Log::debug('Sale update started', [
            'sale_id' => $id,
            'user_id' => $user->id
        ]);
        
        $sale = Sale::where('team_id', $user->team->id)->find($id);
        
        if (!$sale) {
            return response()->json([
                'error' => true,
                'message' => 'Sale not found'
            ], 404);
        }
        
        if ($sale->status === 'completed') {
            return response()->json([
                'error' => true,
                'message' => 'Cannot update completed sale'
            ], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'sale_date' => 'sometimes|required|date',
            'due_date' => 'nullable|date|after_or_equal:sale_date',
            'notes' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,completed,cancelled',
            'items' => 'sometimes|required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
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
            
            // Store original values for later comparison
            $originalSale = clone $sale;
            $originalTotalAmount = $sale->total_amount;
            $originalPaymentStatus = $sale->payment_status;
            $originalPaidAmount = $sale->paid_amount;
            $oldData = $sale->toArray();
            
            \Log::debug('Original sale data', [
                'original_total' => $originalTotalAmount,
                'original_paid' => $originalPaidAmount,
                'original_payment_status' => $originalPaymentStatus
            ]);
            
            // Update the inventory first
            if ($request->has('items')) {
                // Restore old quantities to stock
                foreach ($sale->items as $oldItem) {
                    $product = Product::find($oldItem->product_id);
                    if ($product) {
                        \Log::debug('Restoring product to stock', [
                            'product_id' => $product->id, 
                            'quantity' => $oldItem->quantity
                        ]);
                        $product->updateStock($oldItem->quantity, 'add');
                    }
                }
                
                // Delete old items
                $sale->items()->delete();
                
                // Add new items and update stock
                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    
                    // Validate stock availability
                    if ($product->quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->name}");
                    }
                    
                    // Create new sale item
                    $saleItem = $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_rate' => $item['tax_rate'] ?? 0,
                        'total_price' => ($item['quantity'] * $item['unit_price']) * (1 + ($item['tax_rate'] ?? 0) / 100)
                    ]);
                    
                    \Log::debug('Created sale item', [
                        'item_id' => $saleItem->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity']
                    ]);
                    
                    // Update stock
                    $product->updateStock($item['quantity'], 'subtract');
                }
            }
            
            // Update sale data except payment status (will adjust later)
            $saleData = $request->except('payment_status');
            $sale->update($saleData);
            
            // Now handle the cash source adjustment if needed
            $newTotalAmount = $request->input('total_amount', $originalTotalAmount);
            
            \Log::debug('Comparing totals', [
                'old_total' => $originalTotalAmount,
                'new_total' => $newTotalAmount,
                'difference' => $originalTotalAmount - $newTotalAmount
            ]);
            
            // If this is a paid or partially paid sale and the total amount has changed
            if (($originalPaymentStatus == 'paid' || $originalPaymentStatus == 'partial') &&
                $originalTotalAmount != $newTotalAmount) {
                
                $cashSource = CashSource::find($sale->cash_source_id);
                if (!$cashSource) {
                    throw new \Exception("Cash source not found");
                }
                
                // If new total is less than original total and there were payments
                if ($newTotalAmount < $originalTotalAmount && $originalPaidAmount > 0) {
                    $amountReduction = $originalTotalAmount - $newTotalAmount;
                    
                    // Only adjust up to the amount already paid
                    $adjustmentAmount = min($amountReduction, $originalPaidAmount);
                    
                    if ($adjustmentAmount > 0) {
                        \Log::debug('Withdrawing from cash source due to reduced total', [
                            'cash_source_id' => $cashSource->id,
                            'amount' => $adjustmentAmount
                        ]);
                        
                        // Create a withdrawal transaction
                        $cashSource->withdraw($adjustmentAmount, 
                            "Adjustment due to modified sale #{$sale->reference_number} (total reduced)"
                        );
                        
                        // Record transaction linked to sale
                        $sale->transactions()->create([
                            'team_id' => $sale->team_id,
                            'cash_source_id' => $cashSource->id,
                            'amount' => -$adjustmentAmount,  // negative amount for withdrawal
                            'type' => 'Sale Adjustment',
                            'transaction_date' => now(),
                            'reference_number' => 'ADJ-' . time(),
                            'description' => "Adjustment due to modified sale (total reduced)"
                        ]);
                        
                        // Adjust the paid amount
                        if ($originalPaidAmount <= $newTotalAmount) {
                            // No change to payment status if still has balance
                            $sale->payment_status = $originalPaidAmount == $newTotalAmount ? 'paid' : 'partial';
                        } else {
                            // If paid more than new total, adjust paid amount and set to paid
                            $sale->paid_amount = $newTotalAmount;
                            $sale->payment_status = 'paid';
                        }
                        
                        $sale->save();
                        
                        \Log::debug('Updated payment status after adjustment', [
                            'new_payment_status' => $sale->payment_status,
                            'adjusted_paid_amount' => $sale->paid_amount
                        ]);
                    }
                }
                // If new total is greater than original and was fully paid
                else if ($newTotalAmount > $originalTotalAmount && $originalPaymentStatus == 'paid') {
                    // Change to partially paid
                    $sale->payment_status = 'partial';
                    $sale->save();
                    
                    \Log::debug('Changed payment status to partial due to increased total', [
                        'original_total' => $originalTotalAmount,
                        'new_total' => $newTotalAmount
                    ]);
                }
            } else {
                // Preserve original payment status if no adjustments needed
                $sale->payment_status = $originalPaymentStatus;
                $sale->save();
            }
            
            // Log activity
            ActivityLog::create([
                'log_type' => 'Update',
                'model_type' => "Sale",
                'model_id' => $sale->id,
                'model_identifier' => $sale->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Updated sale {$sale->reference_number}",
                'old_values' => $oldData,
                'new_values' => $sale->fresh()->toArray()
            ]);
            
            DB::commit();
            
            \Log::info('Sale updated successfully', [
                'sale_id' => $sale->id,
                'new_total' => $sale->total_amount,
                'new_paid_amount' => $sale->paid_amount,
                'new_payment_status' => $sale->payment_status
            ]);
            
            return response()->json([
                'message' => 'Sale updated successfully',
                'sale' => $sale->fresh(['items.product', 'client', 'cashSource'])
            ]);
        
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Sale update failed: ' . $e->getMessage(), [
                'sale_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error updating sale: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function addPayment(Request $request, $id)
    {
        $user = $request->user();
    
        $sale = Sale::where('team_id', $user->team->id)->find($id);
    
        if (!$sale) {
            return response()->json([
                'error' => true,
                'message' => 'Sale not found'
            ], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'payment_method' => 'nullable|string' // Add payment method validation
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        // Use default cash_source_id from sale if none provided
        $cashSourceId = $request->cash_source_id ?? $sale->cash_source_id;
        $cashSource = CashSource::find($cashSourceId);
        
        if (!$cashSource) {
            return response()->json([
                'error' => true,
                'message' => 'Cash source not found'
            ], 404);
        }
    
        try {
            DB::beginTransaction();
            
            $referenceNumber = $request->reference_number ?? 
            'PAY-' . date('Ymd-His') . '-' . str_pad($sale->id, 4, '0', STR_PAD_LEFT);
            
            // Use the payment method from request or default to 'cash'
            $paymentMethod = $request->payment_method ?? 'cash';
            
            $transaction = $sale->addPayment(
                $request->amount, 
                $cashSource,
                $request->payment_date,
                $referenceNumber,
                $paymentMethod,
                $request->notes
            );
    
            ActivityLog::create([
                'log_type' => 'Payment',
                'model_type' => "Sale",
                'model_id' => $sale->id,
                'model_identifier' => $sale->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Added {$paymentMethod} payment of {$request->amount} to sale {$sale->reference_number}",
                'new_values' => $transaction->toArray()
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Payment added successfully',
                'sale' => $sale->fresh(['items.product', 'client', 'cashSource']),
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
    
    // public function generateInvoice(Request $request, $id)
    // {
    //     $user = $request->user();
    //     $model = Sale::where('team_id', $user->team->id)->with(['items', 'client'])->find($id);
    
    //     if (!$model) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Record not found'
    //         ], 404);
    //     }
    
    //     // Reference number generation code remains the same...
    //     $lastInvoice = Invoice::where('team_id', $user->team->id)
    //         ->withTrashed()
    //         ->where('reference_number', 'like', "INV-" . date('Y') . "-%")
    //         ->orderBy('id', 'desc')
    //         ->first();
    
    //     if ($lastInvoice) {
    //         $lastNumber = (int) substr($lastInvoice->reference_number, -6);
    //         $nextNumber = $lastNumber + 1;
    //     } else {
    //         $nextNumber = 1;
    //     }
    
    //     $referenceNumber = "INV-" . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    
    //     while (Invoice::where('reference_number', $referenceNumber)->withTrashed()->exists()) {
    //         $nextNumber++;
    //         $referenceNumber = "INV-" . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    //     }
    
    //     try {
    //         DB::beginTransaction();
    
    //         $invoice = new Invoice();
    //         $invoice->team_id = $user->team->id;
    //         $invoice->invoiceable_type = get_class($model);
    //         $invoice->invoiceable_id = $model->id;
    //         $invoice->reference_number = $referenceNumber;
    //         $invoice->total_amount = $model->total_amount;
    //         $invoice->tax_amount = $model->tax_amount;
    //         $invoice->discount_amount = $model->discount_amount;
    //         $invoice->status = 'draft';
    //         $invoice->issue_date = now();
    //         $invoice->due_date = $model->due_date;
            
    //         // Prepare contact data based on whether client exists
    //         $contactData = null;
    //         if ($model instanceof Purchase) {
    //             $contactData = $model->supplier ? 
    //                 ['type' => 'supplier', 'data' => $model->supplier->toArray()] : 
    //                 ['type' => 'supplier', 'data' => null];
    //         } else {
    //             $contactData = $model->client ? 
    //                 ['type' => 'client', 'data' => $model->client->toArray()] : 
    //                 ['type' => 'client', 'data' => null];
    //         }
            
    //         // Set meta_data with null check for items
    //         $invoice->meta_data = [
    //             'source_type' => $model instanceof Purchase ? 'purchase' : 'sale',
    //             'source_reference' => $model->reference_number,
    //             'source_date' => $model->created_at,
    //             'contact' => $contactData,
    //             'items_data' => $model->items->map(function($item) {
    //                 return [
    //                     'product_id' => $item->product_id,
    //                     'product_name' => $item->product?->name ?? 'Unknown Product',
    //                     'quantity' => $item->quantity,
    //                     'unit_price' => $item->unit_price,
    //                     'tax_rate' => $item->tax_rate,
    //                     'discount_amount' => $item->discount_amount,
    //                     'total_price' => $item->total_price,
    //                     'is_package' => $item->is_package ?? false,
    //                     'package_id' => $item->package_id ?? null,
    //                     'total_pieces' => $item->total_pieces ?? $item->quantity
    //                 ];
    //             })->toArray()
    //         ];
            
    //         $invoice->save();
    
    //         // Create invoice items with null checks
    //         foreach ($model->items as $sourceItem) {
    //             $invoice->items()->create([
    //                 'description' => $sourceItem->product?->name ?? 'Unknown Product',
    //                 'quantity' => $sourceItem->quantity,
    //                 'unit_price' => $sourceItem->unit_price,
    //                 'total_price' => $sourceItem->total_price,
    //                 'notes' => $sourceItem->notes ?? ''
    //             ]);
    //         }
    
    //         ActivityLog::create([
    //             'log_type' => 'Create',
    //             'model_type' => "Invoice",
    //             'model_id' => $invoice->id,
    //             'model_identifier' => $invoice->reference_number,
    //             'user_identifier' => $user?->name,
    //             'user_id' => $user->id,
    //             'user_email' => $user?->email,
    //             'ip_address' => $request->ip(),
    //             'user_agent' => $request->userAgent(),
    //             'description' => "Generated invoice {$invoice->reference_number} from " . 
    //                            ($model instanceof Purchase ? "purchase" : "sale") . 
    //                            " {$model->reference_number}",
    //             'new_values' => $invoice->toArray()
    //         ]);
    
    //         DB::commit();
    
    //         return response()->json([
    //             'message' => 'Invoice generated successfully',
    //             'invoice' => $invoice->load('items')
    //         ]);
    
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::error('Invoice generation error', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'model_id' => $id,
    //             'user_id' => $user->id
    //         ]);
            
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Error generating invoice: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function generateInvoice(Request $request, $id)
    {
        $user = $request->user();
        $sale = Sale::where('team_id', $user->team->id)
            ->with(['items', 'client', 'transactions'])
            ->find($id);
        
        if (!$sale) {
            return response()->json([
                'error' => true,
                'message' => 'Record not found'
            ], 404);
        }
        
        // Define default configuration values
        $defaultConfig = [
            'showClientInfo' => true,
            'showAmountInWords' => true,
            'showPaymentMethods' => true,
            'showTaxNumbers' => true,
            'showNotes' => true,
            'showThanksMessage' => true,
            'showTermsConditions' => true,
            'primaryColor' => '#2563eb',
            'logoEnabled' => true,
            'footerText' => '',
            'defaultNotes' => 'Thank you for your business.',
            'defaultTerms' => 'Payment is due within 30 days of invoice date.',
            'thanksMessage' => 'Thank you for your business!'
        ];
    
        // Get configuration options from request using defaults
        $config = [];
        foreach ($defaultConfig as $key => $defaultValue) {
            $config[$key] = $request->input($key, $defaultValue);
        }
                
        // Debug log
        \Log::info('Invoice generation config', [
            'received' => $request->all(),
            'final_config' => $config
        ]);
        
        // No need for merging as we've already applied defaults above
        $finalConfig = $config;
        
        // Generate different reference number prefix based on type
        $prefix = ($sale->type === 'quote') ? "DEVIS-" : "INV-";
                
        $lastInvoice = Invoice::where('team_id', $user->team->id)
            ->withTrashed()
            ->where('reference_number', 'like', $prefix . date('Y') . "-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->reference_number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        $referenceNumber = $prefix . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        
        while (Invoice::where('reference_number', $referenceNumber)->withTrashed()->exists()) {
            $nextNumber++;
            $referenceNumber = $prefix . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        }
        
        try {
            DB::beginTransaction();
        
            $invoice = new Invoice();
            $invoice->team_id = $user->team->id;
            $invoice->invoiceable_type = get_class($sale);
            $invoice->invoiceable_id = $sale->id;
            $invoice->reference_number = $referenceNumber;
            $invoice->total_amount = $sale->total_amount;
            $invoice->tax_amount = $sale->tax_amount;
            $invoice->discount_amount = $sale->discount_amount;
            $invoice->status = 'draft';
            $invoice->issue_date = now();
            $invoice->due_date = $sale->due_date;
                
            // Calculate subtotal
            $subtotal = $sale->total_amount - $sale->tax_amount + $sale->discount_amount;
                
            // Prepare contact data based on whether client exists
            $contactData = null;
            if ($sale instanceof Purchase) {
                $contactData = $sale->supplier ? 
                    ['type' => 'supplier', 'data' => $sale->supplier->toArray()] : 
                    ['type' => 'supplier', 'data' => null];
            } else {
                $contactData = $sale->client ? 
                    ['type' => 'client', 'data' => $sale->client->toArray()] : 
                    ['type' => 'client', 'data' => null];
            }
                
            // Add document type to meta_data
            $documentType = ($sale->type === 'quote') ? 'quote' : 'invoice';
                
            // Set meta_data with null check for items
            $invoice->meta_data = [
                'source_type' => $sale instanceof Purchase ? 'purchase' : ($sale->type ?? 'sale'),
                'document_type' => $documentType,
                'source_reference' => $sale->reference_number,
                'source_date' => $sale->created_at,
                'contact' => $contactData,
                'payment_status' => $sale->payment_status,
                'subtotal' => $subtotal,
                'payment_methods' => $sale->transactions->groupBy('payment_method')
                    ->map(function ($group) {
                        return [
                            'method' => $group->first()->payment_method,
                            'amount' => $group->sum('amount'),
                            'method_name' => ucfirst(str_replace('_', ' ', $group->first()->payment_method))
                        ];
                    })->values()->toArray(),
                'paid_amount' => $sale->paid_amount,
                'items_data' => $sale->items->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name ?? 'Unknown Product',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate' => $item->tax_rate,
                        'discount_amount' => $item->discount_amount,
                        'total_price' => $item->total_price,
                        'is_package' => $item->is_package ?? false,
                        'package_id' => $item->package_id ?? null,
                        'total_pieces' => $item->total_pieces ?? $item->quantity
                    ];
                })->toArray(),
                'config' => $finalConfig
            ];
                
            $invoice->save();
        
            // Create invoice items with null checks
            foreach ($sale->items as $sourceItem) {
                $invoice->items()->create([
                    'description' => $sourceItem->product?->name ?? 'Unknown Product',
                    'quantity' => $sourceItem->quantity,
                    'unit_price' => $sourceItem->unit_price,
                    'total_price' => $sourceItem->total_price,
                    'notes' => $sourceItem->notes ?? '',
                    'tax_amount' => $sourceItem->tax_amount ?? 0,
                    'discount_amount' => $sourceItem->discount_amount ?? 0
                ]);
            }
        
            ActivityLog::create([
                'log_type' => 'Create',
                'model_type' => $sale->type === 'quote' ? "Quotation" : "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Generated " . ($sale->type === 'quote' ? "quotation" : "invoice") . 
                               " {$invoice->reference_number} from " . 
                               ($sale instanceof Purchase ? "purchase" : ($sale->type ?? "sale")) . 
                               " {$sale->reference_number}",
                'new_values' => $invoice->toArray()
            ]);
        
            DB::commit();
        
            return response()->json([
                'message' => ($sale->type === 'quote' ? "Quotation" : "Invoice") . ' generated successfully',
                'invoice' => $invoice->load('items'),
                'invoice_id' => $invoice->id,
                'url' => url("/api/v1/invoices/{$invoice->id}/pdf") // Add URL for PDF
            ]);
        
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Document generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'model_id' => $id,
                'user_id' => $user->id
            ]);
                
            return response()->json([
                'error' => true,
                'message' => 'Error generating ' . ($sale->type === 'quote' ? "quotation" : "invoice") . 
                            ': ' . $e->getMessage()
            ], 500);
        }
    }
    
    

    public function generateReceipt(Request $request, $id)
    {
        try {
            $user = $request->user();
            $sale = Sale::where('team_id', $user->team->id)
                        ->with(['items.product', 'team'])
                        ->findOrFail($id);
    
            // Set locale based on team's preference
            $locale = $sale->team->locale ?? config('app.locale');
            app()->setLocale($locale);
            
            // Create filename
            $filename = "receipt-{$sale->reference_number}-{$locale}.pdf";
            $tempPath = storage_path('app/public/temp');
            
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            $pdfPath = $tempPath . '/' . $filename;
    
            // Generate HTML with simplified receipt centered on page
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>' . __('receipt.receipt_number') . ': ' . $sale->reference_number . '</title>
                <style>
                    body {
                        font-family: Courier, monospace;
                        font-size: 10pt;
                        margin: 0;
                        padding: 100px 0;
                        display: flex;
                        justify-content: center;
                    }
                    
                    .receipt {
                        width: 300px;
                        border: 1px dashed #ccc;
                        padding: 20px;
                        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                        background-color: white;
                    }
                    
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    
                    h2 {
                        margin: 5px 0;
                        font-size: 12pt;
                    }
                    
                    p {
                        margin: 3px 0;
                    }
                    
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 15px 0;
                    }
                    
                    th {
                        border-top: 1px solid #000;
                        border-bottom: 1px solid #000;
                        padding: 5px;
                        text-align: left;
                    }
                    
                    td {
                        padding: 5px;
                    }
                    
                    .totals {
                        text-align: right;
                        margin-top: 15px;
                        border-top: 1px solid #000;
                        padding-top: 10px;
                    }
                    
                    .footer {
                        text-align: center;
                        margin-top: 20px;
                        border-top: 1px solid #000;
                        padding-top: 10px;
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    <div class="header">
                        <h2>' . $sale->team->name . '</h2>
                        <p>' . $sale->team->address . '</p>
                        <p>' . __('receipt.tel') . ': ' . $sale->team->phone . '</p>
                        <p>' . __('receipt.receipt_number') . ': ' . $sale->reference_number . '</p>
                        <p>' . __('receipt.date') . ': ' . $sale->updated_at->format('d/m/Y H:i') . '</p>
                    </div>
                    
                    <table>
                        <tr>
                            <th>' . __('receipt.item') . '</th>
                            <th>' . __('receipt.quantity') . '</th>
                            <th>' . __('receipt.tax') . '</th>
                            <th>' . __('receipt.price') . '</th>
                            <th>' . __('receipt.total') . '</th>
                        </tr>';
            
            foreach($sale->items as $item) {
                $html .= '<tr>
                    <td>' . $item->product->name . '</td>
                    <td>' . $item->quantity . '</td>
                    <td>' . number_format($item->tax_amount, 2) . '</td>
                    <td>' . number_format($item->unit_price, 2) . '</td>
                    <td>' . number_format($item->total_price, 2) . '</td>
                </tr>';
            }
            
            $html .= '</table>
                    
                    <div class="totals">
                        <p>' . __('receipt.subtotal') . ': ' . number_format($sale->total_amount - $sale->tax_amount, 2) . ' ' . __('receipt.currency') . '</p>
                        <p>' . __('receipt.tax') . ': ' . number_format($sale->tax_amount, 2) . ' ' . __('receipt.currency') . '</p>';
            
            if($sale->discount_amount > 0) {
                $html .= '<p>' . __('receipt.discount') . ': -' . number_format($sale->discount_amount, 2) . ' ' . __('receipt.currency') . '</p>';
            }
            
            $html .= '<p><strong>' . __('receipt.total') . ': ' . number_format($sale->total_amount, 2) . ' ' . __('receipt.currency') . '</strong></p>
                    </div>
                    
                    <div class="footer">
                        <p>' . __('receipt.thank_you') . '</p>
                    </div>
                </div>
            </body>
            </html>';
    
            // Configure DomPDF
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('defaultFont', 'Courier');
            
            // Create DomPDF instance
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            
            // Use standard A4
            $dompdf->setPaper('A4');
            
            $dompdf->render();
            
            // Save PDF to file
            file_put_contents($pdfPath, $dompdf->output());
            
            // Log activity
            ActivityLog::create([
                'log_type' => 'Generate',
                'model_type' => 'Receipt',
                'model_id' => $sale->id,
                'model_identifier' => $sale->reference_number,
                'user_identifier' => $user->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Generated receipt for sale {$sale->reference_number} in {$locale}",
                'meta_data' => ['locale' => $locale]
            ]);
    
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf'
            ])->deleteFileAfterSend(true);
    
        } catch (\Exception $e) {
            \Log::error('Receipt generation error: ' . $e->getMessage(), [
                'sale_id' => $id ?? null,
                'user_id' => $request->user()->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error generating receipt: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTrace() : null
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
                'total_sales' => Sale::where('team_id', $user->team->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->count(),

                'total_amount' => Sale::where('team_id', $user->team->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->sum('total_amount'),

                'total_paid' => Sale::where('team_id', $user->team->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->sum('paid_amount'),

                'sales_by_status' => Sale::where('team_id', $user->team->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get(),

                'sales_by_payment_status' => Sale::where('team_id', $user->team->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->select('payment_status', DB::raw('COUNT(*) as count'))
                    ->groupBy('payment_status')
                    ->get(),

                'top_clients' => Sale::where('team_id', $user->team->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->join('clients', 'sales.client_id', '=', 'clients.id')
                    ->select('clients.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total_amount'))
                    ->groupBy('clients.id', 'clients.name')
                    ->orderBy('total_amount', 'desc')
                    ->limit(5)
                    ->get(),

                'top_products' => SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sales.team_id', $user->team->id)
                    ->whereBetween('sales.sale_date', [$startDate, $endDate])
                    ->join('products', 'sale_items.product_id', '=', 'products.id')
                    ->select(
                        'products.name',
                        DB::raw('SUM(sale_items.quantity) as total_quantity'),
                        DB::raw('SUM(sale_items.total_price) as total_amount')
                    )
                    ->groupBy('products.id', 'products.name')
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
    public function convertToSale(Request $request, $id)
    {
        $user = $request->user();
    
        $quote = Sale::where('team_id', $user->team->id)
                    ->where('type', 'quote')
                    ->with(['items.product', 'client', 'cashSource'])
                    ->find($id);
    
        if (!$quote) {
            return response()->json([
                'error' => true,
                'message' => 'Quote not found'
            ], 404);
        }
    
        try {
            DB::beginTransaction();
            
            // First check stock availability for all items
            foreach ($quote->items as $item) {
                $product = $item->product;
                
                if (!$product) {
                    throw new \Exception("Product not found for item {$item->id}");
                }
                
                // Calculate total pieces needed
                $totalPieces = $item->is_package 
                    ? ($item->quantity * $item->package->pieces_per_package)
                    : $item->quantity;
                
                if ($product->quantity < $totalPieces) {
                    throw new \Exception("Insufficient stock for product: {$product->name} (Need: {$totalPieces}, Available: {$product->quantity})");
                }
            }
            
            // Change type to 'sale'
            $quote->type = 'sale';
            $quote->reference_number = $this->generateReferenceNumber('sale');
            $quote->save();
            
            // Now update stock for each item
            foreach ($quote->items as $item) {
                $product = $item->product;
                
                // Calculate total pieces
                $totalPieces = $item->is_package 
                    ? ($item->quantity * $item->package->pieces_per_package)
                    : $item->quantity;
                
                // Update stock
                $product->updateStock($totalPieces, 'subtract');
            }
            
            // Log activity
            ActivityLog::create([
                'log_type' => 'Convert',
                'model_type' => "Sale",
                'model_id' => $quote->id,
                'model_identifier' => $quote->reference_number,
                'user_identifier' => $user->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Converted quote {$quote->reference_number} to sale",
                'new_values' => $quote->toArray()
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Quote converted to sale successfully',
                'sale' => $quote->fresh(['items.product', 'client', 'cashSource'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => true,
                'message' => 'Error converting quote to sale: ' . $e->getMessage()
            ], 500);
        }
    }
    protected function generateReferenceNumber($type = 'sale')
    {
        $prefix = $type === 'quote' ? 'QUOTE-' : 'SALE-';
        
        // Use a database transaction with a lock to prevent race conditions
        return DB::transaction(function() use ($prefix) {
            // Lock the sales table to prevent concurrent reference number generation
            DB::table('sales')->sharedLock();
            
            // Find the last reference number with this prefix
            $latestSale = Sale::where('reference_number', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();
            
            $nextNumber = 1;
            if ($latestSale) {
                // Extract the numeric part and increment
                $numericPart = substr($latestSale->reference_number, strlen($prefix));
                $nextNumber = intval($numericPart) + 1;
            }
            
            $referenceNumber = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            
            // Double-check uniqueness to avoid collisions
            while (Sale::where('reference_number', $referenceNumber)->exists()) {
                $nextNumber++;
                $referenceNumber = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
            
            return $referenceNumber;
        }, 3); // 3 retries if transaction fails
    }
    

}