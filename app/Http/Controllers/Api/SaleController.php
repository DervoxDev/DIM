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
            'items.*.discount_amount' => 'required|numeric|min:0',
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
                    $taxableAmount = $subtotal - $saleItem->discount_amount; // Apply discount before tax
                    $saleItem->tax_amount = ($taxableAmount * $saleItem->tax_rate) / 100; // Calculate tax on discounted amount
                    $saleItem->total_price = $taxableAmount + $saleItem->tax_amount; // Total with tax (after discount)
                    
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
               // In SaleController.php, update method, when creating new sale items:

// Add new items and update stock
foreach ($request->items as $item) {
    $product = Product::findOrFail($item['product_id']);
    
    // Validate stock availability
    if ($product->quantity < $item['quantity']) {
        throw new \Exception("Insufficient stock for product: {$product->name}");
    }
    
    // Calculate values correctly
    $quantity = $item['quantity'];
    $unitPrice = $item['unit_price'];
    $taxRate = $item['tax_rate'] ?? 0;
    $discountAmount = $item['discount_amount'] ?? 0;
    
    // Calculate subtotal
    $subtotal = $quantity * $unitPrice;
    
    // Apply discount to get taxable amount
    $taxableAmount = $subtotal - $discountAmount;
    
    // Calculate tax on the discounted amount
    $taxAmount = ($taxableAmount * $taxRate) / 100;
    
    // Calculate final total price
    $totalPrice = $taxableAmount + $taxAmount;
    
    // Create new sale item with correct values
    $saleItem = $sale->items()->create([
        'product_id' => $item['product_id'],
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'tax_rate' => $taxRate,
        'tax_amount' => $taxAmount, // Properly calculated tax amount
        'discount_amount' => $discountAmount,
        'is_package' => $item['is_package'] ?? false,
        'package_id' => $item['is_package'] ? $item['package_id'] : null,
        'total_pieces' => $item['total_pieces'] ?? $quantity,
        'total_price' => $totalPrice // Correctly calculated total
    ]);
    
    \Log::debug('Created sale item', [
        'item_id' => $saleItem->id,
        'product_id' => $item['product_id'],
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'taxable_amount' => $taxableAmount,
        'tax_rate' => $taxRate,
        'tax_amount' => $taxAmount,
        'total_price' => $totalPrice
    ]);
    
    // Update stock
    $product->updateStock($quantity, 'subtract');
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
                'model_type' => $sale->type === 'quote' ? "Quote" : "Sale",
                'model_id' => $sale->id,
                'model_identifier' => $sale->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Updated " . ($sale->type === 'quote' ? "quote" : "sale") . " {$sale->reference_number}",
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
    public function checkExistingInvoice(Request $request, $id)
{
    $user = $request->user();
    $sale = Sale::where('team_id', $user->team->id)->findOrFail($id);
    
    // Find existing invoice
    $existingInvoice = Invoice::where('invoiceable_type', 'App\Models\Sale')
        ->where('invoiceable_id', $sale->id)
        ->first();
    
    if ($existingInvoice) {
        return response()->json([
            'exists' => true,
            'invoice' => $existingInvoice->load('items'),
            'created_at' => $existingInvoice->created_at,
            'updated_at' => $existingInvoice->updated_at
        ]);
    }
    
    return response()->json(['exists' => false]);
}

    // public function generateInvoice(Request $request, $id)
    // {
    //     $user = $request->user();
    //     $sale = Sale::where('team_id', $user->team->id)
    //         ->with(['items', 'client', 'transactions'])
    //         ->find($id);
        
    //     if (!$sale) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Record not found'
    //         ], 404);
    //     }
        
    //     // Define default configuration values
    //     $defaultConfig = [
    //         'showClientInfo' => true,
    //         'showAmountInWords' => true,
    //         'showPaymentMethods' => true,
    //         'showTaxNumbers' => true,
    //         'showNotes' => true,
    //         'showThanksMessage' => true,
    //         'showTermsConditions' => true,
    //         'primaryColor' => '#2563eb',
    //         'logoEnabled' => true,
    //         'footerText' => '',
    //         'defaultNotes' => 'Thank you for your business.',
    //         'defaultTerms' => 'Payment is due within 30 days of invoice date.',
    //         'thanksMessage' => 'Thank you for your business!'
    //     ];
    
    //     // Get configuration options from request using defaults
    //     $config = [];
    //     foreach ($defaultConfig as $key => $defaultValue) {
    //         $config[$key] = $request->input($key, $defaultValue);
    //     }
                
    //     // Debug log
    //     \Log::info('Invoice generation config', [
    //         'received' => $request->all(),
    //         'final_config' => $config
    //     ]);
        
    //     // No need for merging as we've already applied defaults above
    //     $finalConfig = $config;
        
    //     // Determine document type based on sale type
    //     $documentType = ($sale->type === 'quote') ? 'quote' : 'invoice';
        
    //     // Generate different reference number prefix based on type
    //     $prefix = ($sale->type === 'quote') ? "DEVIS-" : "INV-";
                
    //     $lastInvoice = Invoice::where('team_id', $user->team->id)
    //         ->withTrashed()
    //         ->where('reference_number', 'like', $prefix . date('Y') . "-%")
    //         ->orderBy('id', 'desc')
    //         ->first();
        
    //     if ($lastInvoice) {
    //         $lastNumber = (int) substr($lastInvoice->reference_number, -6);
    //         $nextNumber = $lastNumber + 1;
    //     } else {
    //         $nextNumber = 1;
    //     }
        
    //     $referenceNumber = $prefix . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        
    //     while (Invoice::where('reference_number', $referenceNumber)->withTrashed()->exists()) {
    //         $nextNumber++;
    //         $referenceNumber = $prefix . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    //     }
        
    //     try {
    //         DB::beginTransaction();
        
    //         $invoice = new Invoice();
    //         $invoice->team_id = $user->team->id;
    //         $invoice->invoiceable_type = get_class($sale);
    //         $invoice->invoiceable_id = $sale->id;
    //         $invoice->reference_number = $referenceNumber;
    //         $invoice->type = $documentType; // Set document type (invoice or quote)
    //         $invoice->total_amount = $sale->total_amount;
    //         $invoice->tax_amount = $sale->tax_amount;
    //         $invoice->discount_amount = $sale->discount_amount;
    //         $invoice->status = 'draft'; // Use draft as initial status
    //         $invoice->payment_status = $sale->payment_status ?? 'unpaid'; // Copy payment status from sale
    //         $invoice->is_email_sent = false; // Default to not email sent
    //         $invoice->issue_date = now();
    //         $invoice->due_date = $sale->due_date;
                
    //         // Calculate subtotal
    //         $subtotal = $sale->total_amount - $sale->tax_amount + $sale->discount_amount;
                
    //         // Prepare contact data based on whether client exists
    //         $contactData = null;
    //         if ($sale instanceof Purchase) {
    //             $contactData = $sale->supplier ? 
    //                 ['type' => 'supplier', 'data' => $sale->supplier->toArray()] : 
    //                 ['type' => 'supplier', 'data' => null];
    //         } else {
    //             $contactData = $sale->client ? 
    //                 ['type' => 'client', 'data' => $sale->client->toArray()] : 
    //                 ['type' => 'client', 'data' => null];
    //         }
                
    //         // Set meta_data with null check for items
    //         $invoice->meta_data = [
    //             'source_type' => $sale instanceof Purchase ? 'purchase' : ($sale->type ?? 'sale'),
    //             'document_type' => $documentType,
    //             'source_reference' => $sale->reference_number,
    //             'source_date' => $sale->created_at,
    //             'contact' => $contactData,
    //             'payment_status' => $sale->payment_status ?? 'unpaid',
    //             'subtotal' => $subtotal,
    //             'payment_methods' => $sale->transactions->groupBy('payment_method')
    //                 ->map(function ($group) {
    //                     return [
    //                         'method' => $group->first()->payment_method,
    //                         'amount' => $group->sum('amount'),
    //                         'method_name' => ucfirst(str_replace('_', ' ', $group->first()->payment_method))
    //                     ];
    //                 })->values()->toArray(),
    //             'paid_amount' => $sale->paid_amount ?? 0,
    //             'items_data' => $sale->items->map(function($item) {
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
    //             })->toArray(),
    //             'config' => $finalConfig
    //         ];
                
    //         $invoice->save();
        
    //         // Create invoice items with null checks
    //         foreach ($sale->items as $sourceItem) {
    //             $invoice->items()->create([
    //                 'description' => $sourceItem->product?->name ?? 'Unknown Product',
    //                 'quantity' => $sourceItem->quantity,
    //                 'unit_price' => $sourceItem->unit_price,
    //                 'total_price' => $sourceItem->total_price,
    //                 'notes' => $sourceItem->notes ?? '',
    //                 'tax_amount' => $sourceItem->tax_amount ?? 0,
    //                 'discount_amount' => $sourceItem->discount_amount ?? 0
    //             ]);
    //         }
        
    //         ActivityLog::create([
    //             'log_type' => 'Create',
    //             'model_type' => $sale->type === 'quote' ? "Quotation" : "Invoice",
    //             'model_id' => $invoice->id,
    //             'model_identifier' => $invoice->reference_number,
    //             'user_identifier' => $user?->name,
    //             'user_id' => $user->id,
    //             'user_email' => $user?->email,
    //             'ip_address' => $request->ip(),
    //             'user_agent' => $request->userAgent(),
    //             'description' => "Generated " . ($sale->type === 'quote' ? "quotation" : "invoice") . 
    //                            " {$invoice->reference_number} from " . 
    //                            ($sale instanceof Purchase ? "purchase" : ($sale->type ?? "sale")) . 
    //                            " {$sale->reference_number}",
    //             'new_values' => $invoice->toArray()
    //         ]);
        
    //         DB::commit();
        
    //         return response()->json([
    //             'message' => ($sale->type === 'quote' ? "Quotation" : "Invoice") . ' generated successfully',
    //             'invoice' => $invoice->load('items'),
    //             'invoice_id' => $invoice->id,
    //             'url' => url("/api/v1/invoices/{$invoice->id}/pdf") // Add URL for PDF
    //         ]);
        
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::error('Document generation error', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'model_id' => $id,
    //             'user_id' => $user->id
    //         ]);
                
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Error generating ' . ($sale->type === 'quote' ? "quotation" : "invoice") . 
    //                         ': ' . $e->getMessage()
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
    
    // Check for existing invoice if mode is 'check_first'
    $mode = $request->input('mode', 'check_first');
    
    if ($mode === 'check_first') {
        $existingInvoice = Invoice::where('invoiceable_type', 'App\Models\Sale')
            ->where('invoiceable_id', $sale->id)
            ->first();
        
        if ($existingInvoice) {
            return response()->json([
                'exists' => true,
                'invoice' => $existingInvoice->load('items'),
                'message' => 'An invoice already exists for this sale'
            ]);
        }
        
        // No existing invoice, continue with creation
        $mode = 'create_new';
    }
    
    // Handle replacement of existing invoice
    if ($mode === 'replace') {
        $existingInvoice = Invoice::where('invoiceable_type', 'App\Models\Sale')
            ->where('invoiceable_id', $sale->id)
            ->first();
        
        if ($existingInvoice) {
            try {
                DB::beginTransaction();
                
                // Save the existing reference number
                $referenceNumber = $existingInvoice->reference_number;
                
                // Delete items
                $existingInvoice->items()->delete();
                
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
                \Log::info('Invoice replacement config', [
                    'received' => $request->all(),
                    'final_config' => $config,
                    'invoice_id' => $existingInvoice->id
                ]);
                
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
                
                // Update the existing invoice
                $existingInvoice->total_amount = $sale->total_amount;
                $existingInvoice->tax_amount = $sale->tax_amount;
                $existingInvoice->discount_amount = $sale->discount_amount;
                $existingInvoice->payment_status = $sale->payment_status ?? 'unpaid';
                $existingInvoice->due_date = $sale->due_date;
                
                // Update meta_data with null check for items
                $existingInvoice->meta_data = [
                    'source_type' => $sale instanceof Purchase ? 'purchase' : ($sale->type ?? 'sale'),
                    'document_type' => $existingInvoice->type, // Keep original document type
                    'source_reference' => $sale->reference_number,
                    'source_date' => $sale->created_at,
                    'contact' => $contactData,
                    'payment_status' => $sale->payment_status ?? 'unpaid',
                    'subtotal' => $subtotal,
                    'payment_methods' => $sale->transactions->groupBy('payment_method')
                        ->map(function ($group) {
                            return [
                                'method' => $group->first()->payment_method,
                                'amount' => $group->sum('amount'),
                                'method_name' => ucfirst(str_replace('_', ' ', $group->first()->payment_method))
                            ];
                        })->values()->toArray(),
                    'paid_amount' => $sale->paid_amount ?? 0,
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
                    'config' => $config,
                    'updated_at' => now(),
                    'was_replaced' => true
                ];
                
                $existingInvoice->save();
                
                // Create invoice items with null checks
                foreach ($sale->items as $sourceItem) {
                    $existingInvoice->items()->create([
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
                    'log_type' => 'Update',
                    'model_type' => $sale->type === 'quote' ? "Quotation" : "Invoice",
                    'model_id' => $existingInvoice->id,
                    'model_identifier' => $existingInvoice->reference_number,
                    'user_identifier' => $user?->name,
                    'user_id' => $user->id,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Updated " . ($sale->type === 'quote' ? "quotation" : "invoice") . 
                                   " {$existingInvoice->reference_number} from " . 
                                   ($sale instanceof Purchase ? "purchase" : ($sale->type ?? "sale")) . 
                                   " {$sale->reference_number}",
                    'new_values' => $existingInvoice->toArray()
                ]);
                
                DB::commit();
                
                return response()->json([
                    'message' => ($sale->type === 'quote' ? "Quotation" : "Invoice") . ' updated successfully',
                    'invoice' => $existingInvoice->load('items'),
                    'invoice_id' => $existingInvoice->id,
                    'url' => url("/api/v1/invoices/{$existingInvoice->id}/pdf"),
                    'was_replaced' => true
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Document update error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'model_id' => $id,
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'error' => true,
                    'message' => 'Error updating ' . ($sale->type === 'quote' ? "quotation" : "invoice") . 
                                ': ' . $e->getMessage()
                ], 500);
            }
        } else {
            // No invoice to replace, create a new one instead
            $mode = 'create_new';
        }
    }
    
    // Create a new invoice (existing logic for 'create_new' mode)
    if ($mode === 'create_new') {
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
        
        // Determine document type based on sale type
        $documentType = ($sale->type === 'quote') ? 'quote' : 'invoice';
        
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
            $invoice->type = $documentType; // Set document type (invoice or quote)
            $invoice->total_amount = $sale->total_amount;
            $invoice->tax_amount = $sale->tax_amount;
            $invoice->discount_amount = $sale->discount_amount;
            $invoice->status = 'draft'; // Use draft as initial status
            $invoice->payment_status = $sale->payment_status ?? 'unpaid'; // Copy payment status from sale
            $invoice->is_email_sent = false; // Default to not email sent
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
                
            // Set meta_data with null check for items
            $invoice->meta_data = [
                'source_type' => $sale instanceof Purchase ? 'purchase' : ($sale->type ?? 'sale'),
                'document_type' => $documentType,
                'source_reference' => $sale->reference_number,
                'source_date' => $sale->created_at,
                'contact' => $contactData,
                'payment_status' => $sale->payment_status ?? 'unpaid',
                'subtotal' => $subtotal,
                'payment_methods' => $sale->transactions->groupBy('payment_method')
                    ->map(function ($group) {
                        return [
                            'method' => $group->first()->payment_method,
                            'amount' => $group->sum('amount'),
                            'method_name' => ucfirst(str_replace('_', ' ', $group->first()->payment_method))
                        ];
                    })->values()->toArray(),
                'paid_amount' => $sale->paid_amount ?? 0,
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
            
            // Get paper dimensions from request with defaults
            $paperWidthMM = $request->input('paperWidth', 80); // Default 80mm
            $heightMode = $request->input('heightMode', 'auto'); // Default auto
            $maxHeightPt = $request->input('maxHeight', 800); // Default max height
            
            // Convert mm to points for PDF (1mm ≈ 2.83pt)
            $paperWidthPt = round($paperWidthMM * 2.83);
            
            // Create filename
            $filename = "receipt-{$sale->reference_number}-{$locale}.pdf";
            
            // HTML for receipt
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>' . __('receipt.receipt_number') . ': ' . $sale->reference_number . '</title>
                <style>
                    /* Reset */
                    * {
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                    }
                    
                    /* Base styles */
                    body {
                        font-family: "Courier New", Courier, monospace;
                        font-size: 9pt;
                        line-height: 1.15;
                        padding: 8mm 2mm 0 1.5mm; /* No bottom padding */
                        width: 100%;
                        max-width: ' . ($paperWidthPt - 8) . 'pt;
                        margin: 0 auto;
                    }
                    
                    /* Header */
                    .header {
                        text-align: center;
                        margin-bottom: 3mm;
                    }
                    
                    h2 {
                        font-size: 11pt;
                        font-weight: bold;
                        margin-bottom: 1.5mm;
                    }
                    
                    p {
                        margin-bottom: 1mm;
                    }
                    
                    /* Table layout */
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 3mm 0;
                        table-layout: fixed;
                    }
                    
                    th {
                        border-top: 0.5pt solid #000;
                        border-bottom: 0.5pt solid #000;
                        padding: 1mm 0;
                        text-align: left;
                        font-size: 9pt;
                        font-weight: bold;
                    }
                    
                    td {
                        padding: 0.8mm 0;
                        font-size: 9pt;
                        vertical-align: top;
                    }
                    
                    td.product-name {
                        padding-right: 1mm;
                    }
                    
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    
                    /* Totals section */
                    .totals {
                        text-align: right;
                        border-top: 0.5pt solid #000;
                        padding-top: 1mm;
                        margin-top: 1mm;
                    }
                    
                    /* Footer */
                    .footer {
                        text-align: center;
                        border-top: 0.5pt solid #000;
                        padding-top: 1mm;
                        margin-top: 2mm;
                    }
                    
                    .total-line {
                        font-weight: bold;
                        font-size: 10pt;
                        margin-top: 1mm;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>' . $sale->team->name . '</h2>
                    <p>' . $sale->team->address . '</p>
                    <p>' . __('receipt.tel') . ': ' . $sale->team->phone . '</p>
                    <p>' . __('receipt.receipt_number') . ': ' . $sale->reference_number . '</p>
                    <p>' . __('receipt.date') . ': ' . $sale->updated_at->format('d/m/Y H:i') . '</p>
                </div>
                
                <table>';
                
            // Determine if tax needs to be shown as a separate column
            $showTaxColumn = !empty(array_filter(array_column($sale->items->toArray(), 'tax_amount')));
            
            if ($showTaxColumn) {
                $html .= '<tr>
                    <th width="38%">' . __('receipt.item') . '</th>
                    <th width="8%" class="text-center">' . __('receipt.quantity') . '</th>
                    <th width="10%" class="text-right">' . __('receipt.tax') . '</th>
                    <th width="19%" class="text-right">' . __('receipt.price') . '</th>
                    <th width="25%" class="text-right">' . __('receipt.total') . '</th>
                </tr>';
            } else {
                $html .= '<tr>
                    <th width="46%">' . __('receipt.item') . '</th>
                    <th width="8%" class="text-center">' . __('receipt.quantity') . '</th>
                    <th width="20%" class="text-right">' . __('receipt.price') . '</th>
                    <th width="26%" class="text-right">' . __('receipt.total') . '</th>
                </tr>';
            }
            
            foreach($sale->items as $item) {
                // Allow more characters for product name
                $productName = $item->product->name;
                if (strlen($productName) > 22) {
                    $productName = substr($productName, 0, 19) . '...';
                }
                
                if ($showTaxColumn) {
                    $html .= '<tr>
                        <td class="product-name">' . $productName . '</td>
                        <td class="text-center">' . $item->quantity . '</td>
                        <td class="text-right">' . number_format($item->tax_amount, 2) . '</td>
                        <td class="text-right">' . number_format($item->unit_price, 2) . '</td>
                        <td class="text-right">' . number_format($item->total_price, 2) . '</td>
                    </tr>';
                } else {
                    $html .= '<tr>
                        <td class="product-name">' . $productName . '</td>
                        <td class="text-center">' . $item->quantity . '</td>
                        <td class="text-right">' . number_format($item->unit_price, 2) . '</td>
                        <td class="text-right">' . number_format($item->total_price, 2) . '</td>
                    </tr>';
                }
            }
            
            $html .= '</table>
                
                <div class="totals">
                    <p>' . __('receipt.subtotal') . ': ' . number_format($sale->total_amount - $sale->tax_amount, 2) . ' ' . __('receipt.currency') . '</p>';
                    
            // Always show tax amount in the totals even if it's zero
            $html .= '<p>' . __('receipt.tax') . ': ' . number_format($sale->tax_amount, 2) . ' ' . __('receipt.currency') . '</p>';
            
            if($sale->discount_amount > 0) {
                $html .= '<p>' . __('receipt.discount') . ': -' . number_format($sale->discount_amount, 2) . ' ' . __('receipt.currency') . '</p>';
            }
            
            $html .= '<p class="total-line">' . __('receipt.total') . ': ' . number_format($sale->total_amount, 2) . ' ' . __('receipt.currency') . '</p>
                </div>
                
                <div class="footer">
                    <p>' . __('receipt.thank_you') . '</p>
                </div>
            </body>
            </html>';
    
            // Create DomPDF options
            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'courier');
            $options->set('dpi', 203); // Standard thermal printer DPI
            $options->set('isRemoteEnabled', false);
            $options->set('isPhpEnabled', false);
            $options->set('isHtml5ParserEnabled', true);
            
            $finalHeight = 0;
            
            if ($heightMode === 'auto') {
                // Two-pass approach to determine height
                
                // First pass - estimate the page count from rendering a fixed height
                $tempDompdf = new \Dompdf\Dompdf($options);
                $tempDompdf->loadHtml($html);
                
                // Start with a reasonable height
                $tempDompdf->setPaper(array(0, 0, $paperWidthPt, 500), 'portrait');
                $tempDompdf->render();
                
                // Get the page count as a way to determine if content fits
                $pageCount = $tempDompdf->getCanvas()->get_page_count();
                
                // Use page count to determine appropriate height
                if ($pageCount == 1) {
                    // Content fits on a single page - use binary search to find optimal height
                    $minHeight = 200;  // Minimum reasonable height
                    $maxHeight = 500;  // Our initial test height
                    $bestHeight = $maxHeight;
                    $iterations = 0;
                    $maxIterations = 5; // Limit iterations to avoid excessive processing
                    
                    while ($iterations < $maxIterations && ($maxHeight - $minHeight) > 10) {
                        $iterations++;
                        $testHeight = floor(($minHeight + $maxHeight) / 2);
                        
                        // Test with this height
                        $testDompdf = new \Dompdf\Dompdf($options);
                        $testDompdf->loadHtml($html);
                        $testDompdf->setPaper(array(0, 0, $paperWidthPt, $testHeight), 'portrait');
                        $testDompdf->render();
                        
                        // If this still fits on one page, we can go smaller
                        $testPageCount = $testDompdf->getCanvas()->get_page_count();
                        
                        if ($testPageCount == 1) {
                            $bestHeight = $testHeight; // Save this working height
                            $maxHeight = $testHeight;  // Try smaller
                        } else {
                            $minHeight = $testHeight;  // Try larger
                        }
                    }
                    
                    $finalHeight = $bestHeight + 5; // Add a small buffer
                } else {
                    // Content doesn't fit on a single page at 500pt height
                    // Use a larger height based on page count but respect maxHeight
                    $finalHeight = min(500 * $pageCount, $maxHeightPt);
                }
            } else {
                // Fixed height mode
                $finalHeight = $maxHeightPt;
            }
            
            // Final render with optimized height
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper(array(0, 0, $paperWidthPt, $finalHeight), 'portrait');
            $dompdf->render();
            
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
                'meta_data' => [
                    'locale' => $locale, 
                    'paper_width_mm' => $paperWidthMM,
                    'paper_width_pts' => $paperWidthPt,
                    'paper_height_pts' => $finalHeight,
                    'optimized_height' => $finalHeight,
                    'height_mode' => $heightMode,
                    'algorithm' => 'binary-search',
                    'page_count' => $pageCount ?? 1,
                    'has_tax_column' => $showTaxColumn
                ]
            ]);
    
            // Output the PDF with dimension metadata in headers
            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('X-Paper-Width-MM', $paperWidthMM)
                ->header('X-Paper-Height-PT', $finalHeight)
                ->header('X-Height-Mode', $heightMode);
        }
        catch (\Exception $e) {
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
    
    public function generateTestReceipt(Request $request)
{
    try {
        $user = $request->user();
        $team = $user->team;

        // Set locale based on team's preference
        $locale = $team->locale ?? config('app.locale');
        app()->setLocale($locale);
        
        // Get paper dimensions from request with defaults
        $paperWidthMM = $request->input('paperWidth', 80); // Default 80mm
        $heightMode = $request->input('heightMode', 'auto'); // Default auto
        $maxHeightPt = $request->input('maxHeight', 800); // Default max height
        
        // Convert mm to points for PDF (1mm ≈ 2.83pt)
        $paperWidthPt = round($paperWidthMM * 2.83);
        
        // Create test reference number
        $refNumber = 'TEST-' . date('YmdHis');
        
        // Create filename
        $filename = "test-receipt-{$refNumber}-{$locale}.pdf";

        // Create test sale data
        $testItems = [
            [
                'product' => [
                    'name' => 'Test Product 1'
                ],
                'quantity' => 2,
                'unit_price' => 25.99,
                'total_price' => 51.98,
                'tax_amount' => 5.20
            ],
            [
                'product' => [
                    'name' => 'Test Product 2 with a longer name to test wrapping'
                ],
                'quantity' => 1,
                'unit_price' => 15.50,
                'total_price' => 15.50,
                'tax_amount' => 1.55
            ],
            [
                'product' => [
                    'name' => 'Test Product 3'
                ],
                'quantity' => 3,
                'unit_price' => 7.75,
                'total_price' => 23.25,
                'tax_amount' => 2.33
            ],
            [
                'product' => [
                    'name' => 'Test Product with No Tax'
                ],
                'quantity' => 2,
                'unit_price' => 5.00,
                'total_price' => 10.00,
                'tax_amount' => 0.00
            ]
        ];
        
        // Calculate totals
        $subtotal = array_sum(array_column($testItems, 'total_price'));
        $taxAmount = array_sum(array_column($testItems, 'tax_amount'));
        $discountAmount = 5.00; // Example discount
        $totalAmount = $subtotal; // Without discount for simplicity
        
        // HTML for receipt
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>' . __('receipt.receipt_number') . ': ' . $refNumber . '</title>
            <style>
                /* Reset */
                * {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }
                
                /* Base styles */
                body {
                    font-family: "Courier New", Courier, monospace;
                    font-size: 9pt;
                    line-height: 1.15;
                    padding: 8mm 2mm 0 1.5mm; /* No bottom padding */
                    width: 100%;
                    max-width: ' . ($paperWidthPt - 8) . 'pt;
                    margin: 0 auto;
                }
                
                /* Header */
                .header {
                    text-align: center;
                    margin-bottom: 3mm;
                }
                
                h2 {
                    font-size: 11pt;
                    font-weight: bold;
                    margin-bottom: 1.5mm;
                }
                
                p {
                    margin-bottom: 1mm;
                }
                
                /* Table layout */
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 3mm 0;
                    table-layout: fixed;
                }
                
                th {
                    border-top: 0.5pt solid #000;
                    border-bottom: 0.5pt solid #000;
                    padding: 1mm 0;
                    text-align: left;
                    font-size: 9pt;
                    font-weight: bold;
                }
                
                td {
                    padding: 0.8mm 0;
                    font-size: 9pt;
                    vertical-align: top;
                }
                
                td.product-name {
                    padding-right: 1mm;
                }
                
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                
                /* Totals section */
                .totals {
                    text-align: right;
                    border-top: 0.5pt solid #000;
                    padding-top: 1mm;
                    margin-top: 1mm;
                }
                
                /* Footer */
                .footer {
                    text-align: center;
                    border-top: 0.5pt solid #000;
                    padding-top: 1mm;
                    margin-top: 2mm;
                }
                
                .total-line {
                    font-weight: bold;
                    font-size: 10pt;
                    margin-top: 1mm;
                }

                .test-receipt {
                    text-align: center;
                    margin-top: 4mm;
                    font-size: 8pt;
                    font-style: italic;
                    color: #555;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>' . $team->name . '</h2>
                <p>' . ($team->address ?? '123 Main Street, City') . '</p>
                <p>' . __('receipt.tel') . ': ' . ($team->phone ?? '+1 234-567-8901') . '</p>
                <p>' . __('receipt.receipt_number') . ': ' . $refNumber . '</p>
                <p>' . __('receipt.date') . ': ' . date('d/m/Y H:i') . '</p>
            </div>
            
            <table>';
            
        // Determine if tax needs to be shown as a separate column
        $showTaxColumn = !empty(array_filter(array_column($testItems, 'tax_amount')));
        
        if ($showTaxColumn) {
            $html .= '<tr>
                <th width="38%">' . __('receipt.item') . '</th>
                <th width="8%" class="text-center">' . __('receipt.quantity') . '</th>
                <th width="10%" class="text-right">' . __('receipt.tax') . '</th>
                <th width="19%" class="text-right">' . __('receipt.price') . '</th>
                <th width="25%" class="text-right">' . __('receipt.total') . '</th>
            </tr>';
        } else {
            $html .= '<tr>
                <th width="46%">' . __('receipt.item') . '</th>
                <th width="8%" class="text-center">' . __('receipt.quantity') . '</th>
                <th width="20%" class="text-right">' . __('receipt.price') . '</th>
                <th width="26%" class="text-right">' . __('receipt.total') . '</th>
            </tr>';
        }
        
        foreach($testItems as $item) {
            // Allow more characters for product name
            $productName = $item['product']['name'];
            if (strlen($productName) > 22) {
                $productName = substr($productName, 0, 19) . '...';
            }
            
            if ($showTaxColumn) {
                $html .= '<tr>
                    <td class="product-name">' . $productName . '</td>
                    <td class="text-center">' . $item['quantity'] . '</td>
                    <td class="text-right">' . number_format($item['tax_amount'], 2) . '</td>
                    <td class="text-right">' . number_format($item['unit_price'], 2) . '</td>
                    <td class="text-right">' . number_format($item['total_price'], 2) . '</td>
                </tr>';
            } else {
                $html .= '<tr>
                    <td class="product-name">' . $productName . '</td>
                    <td class="text-center">' . $item['quantity'] . '</td>
                    <td class="text-right">' . number_format($item['unit_price'], 2) . '</td>
                    <td class="text-right">' . number_format($item['total_price'], 2) . '</td>
                </tr>';
            }
        }
        
        $html .= '</table>
            
            <div class="totals">
                <p>' . __('receipt.subtotal') . ': ' . number_format($subtotal, 2) . ' ' . __('receipt.currency') . '</p>';
                
        // Always show tax amount in the totals even if it's zero
        $html .= '<p>' . __('receipt.tax') . ': ' . number_format($taxAmount, 2) . ' ' . __('receipt.currency') . '</p>';
        
        $html .= '<p>' . __('receipt.discount') . ': -' . number_format($discountAmount, 2) . ' ' . __('receipt.currency') . '</p>';
        
        $html .= '<p class="total-line">' . __('receipt.total') . ': ' . number_format($totalAmount, 2) . ' ' . __('receipt.currency') . '</p>
            </div>
            
            <div class="footer">
                <p>' . __('receipt.thank_you') . '</p>
                <div class="test-receipt">TEST RECEIPT - NOT A VALID DOCUMENT</div>
            </div>
        </body>
        </html>';

        // Create DomPDF options
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'courier');
        $options->set('dpi', 203); // Standard thermal printer DPI
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        
        $finalHeight = 0;
        $pageCount = 1;
        
        if ($heightMode === 'auto') {
            // Two-pass approach to determine height
            
            // First pass - estimate the page count from rendering a fixed height
            $tempDompdf = new \Dompdf\Dompdf($options);
            $tempDompdf->loadHtml($html);
            
            // Start with a reasonable height
            $tempDompdf->setPaper(array(0, 0, $paperWidthPt, 500), 'portrait');
            $tempDompdf->render();
            
            // Get the page count as a way to determine if content fits
            $pageCount = $tempDompdf->getCanvas()->get_page_count();
            
            // Use page count to determine appropriate height
            if ($pageCount == 1) {
                // Content fits on a single page - use binary search to find optimal height
                $minHeight = 200;  // Minimum reasonable height
                $maxHeight = 500;  // Our initial test height
                $bestHeight = $maxHeight;
                $iterations = 0;
                $maxIterations = 5; // Limit iterations to avoid excessive processing
                
                while ($iterations < $maxIterations && ($maxHeight - $minHeight) > 10) {
                    $iterations++;
                    $testHeight = floor(($minHeight + $maxHeight) / 2);
                    
                    // Test with this height
                    $testDompdf = new \Dompdf\Dompdf($options);
                    $testDompdf->loadHtml($html);
                    $testDompdf->setPaper(array(0, 0, $paperWidthPt, $testHeight), 'portrait');
                    $testDompdf->render();
                    
                    // If this still fits on one page, we can go smaller
                    $testPageCount = $testDompdf->getCanvas()->get_page_count();
                    
                    if ($testPageCount == 1) {
                        $bestHeight = $testHeight; // Save this working height
                        $maxHeight = $testHeight;  // Try smaller
                    } else {
                        $minHeight = $testHeight;  // Try larger
                    }
                }
                
                $finalHeight = $bestHeight + 5; // Add a small buffer
            } else {
                // Content doesn't fit on a single page at 500pt height
                // Use a larger height based on page count but respect maxHeight
                $finalHeight = min(500 * $pageCount, $maxHeightPt);
            }
        } else {
            // Fixed height mode
            $finalHeight = $maxHeightPt;
        }
        
        // Final render with optimized height
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper(array(0, 0, $paperWidthPt, $finalHeight), 'portrait');
        $dompdf->render();
        
        // Log activity
        ActivityLog::create([
            'log_type' => 'Generate',
            'model_type' => 'TestReceipt',
            'model_id' => 0,
            'model_identifier' => $refNumber,
            'user_identifier' => $user->name,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => "Generated test receipt {$refNumber} for previewing in settings",
            'meta_data' => [
                'locale' => $locale, 
                'paper_width_mm' => $paperWidthMM,
                'paper_width_pts' => $paperWidthPt,
                'paper_height_pts' => $finalHeight,
                'optimized_height' => $finalHeight,
                'height_mode' => $heightMode,
                'algorithm' => 'binary-search',
                'page_count' => $pageCount,
                'has_tax_column' => $showTaxColumn
            ]
        ]);

        // Output the PDF with dimension metadata in headers
        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('X-Paper-Width-MM', $paperWidthMM)
            ->header('X-Paper-Height-PT', $finalHeight)
            ->header('X-Height-Mode', $heightMode);
    }
    catch (\Exception $e) {
        \Log::error('Test receipt generation error: ' . $e->getMessage(), [
            'user_id' => $request->user()->id ?? null,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => true,
            'message' => 'Error generating test receipt: ' . $e->getMessage(),
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
            
            // Find the last reference number with this prefix INCLUDING soft-deleted records
            $latestSale = Sale::where('reference_number', 'like', $prefix . '%')
                ->withTrashed() // Include soft deleted records
                ->orderBy('id', 'desc')
                ->first();
            
            $nextNumber = 1;
            if ($latestSale) {
                // Extract the numeric part and increment
                $numericPart = substr($latestSale->reference_number, strlen($prefix));
                $nextNumber = intval($numericPart) + 1;
            }
            
            $referenceNumber = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            
            // Double-check uniqueness to avoid collisions, including soft-deleted records
            while (Sale::where('reference_number', $referenceNumber)->withTrashed()->exists()) {
                $nextNumber++;
                $referenceNumber = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
            
            return $referenceNumber;
        }, 3); // 3 retries if transaction fails
    }
    
    
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $sale = Sale::where('team_id', $user->team->id)->find($id);
        
        if (!$sale) {
            return response()->json([
                'error' => true,
                'message' => 'Sale not found'
            ], 404);
        }
        
        try {
            DB::beginTransaction();
            
            // Soft delete the sale items
            $sale->items()->delete();
            
            // Soft delete the sale itself (using the SoftDeletes trait)
            $sale->delete();
            
            // Log activity
            ActivityLog::create([
                'log_type' => 'Delete',
                'model_type' => $sale->type === 'quote' ? "Quote" : "Sale",
                'model_id' => $sale->id,
                'model_identifier' => $sale->reference_number,
                'user_identifier' => $user->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Deleted " . ($sale->type === 'quote' ? "quote" : "sale") . " {$sale->reference_number}",
                'old_values' => $sale->toArray()
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => ($sale->type === 'quote' ? "Quote" : "Sale") . ' deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => true,
                'message' => 'Error deleting ' . ($sale->type === 'quote' ? "quote" : "sale") . ': ' . $e->getMessage()
            ], 500);
        }
    }
    
}