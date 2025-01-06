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

        // Sort
        $sortField = $request->get('sort_by', 'sale_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

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
        
            // Check stock availability first
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
        
            // Create sale
            $sale = new Sale();
            $sale->team_id = $user->team->id;
            $sale->client_id = $request->client_id;
            $sale->cash_source_id = $request->cash_source_id;
            $sale->reference_number = 'SALE-' . str_pad(Sale::max('id') + 1, 6, '0', STR_PAD_LEFT);
            $sale->sale_date = $request->sale_date;
            $sale->due_date = $request->due_date;
            $sale->notes = $request->notes;
            $sale->status = $request->status ?? 'pending';
            $sale->payment_status = $request->payment_status ?? 'unpaid';
            $sale->total_amount = $request->total_amount ?? 0;
            $sale->tax_amount = $request->tax_amount ?? 0;
            $sale->discount_amount = $request->discount_amount ?? 0;
            
            if (!$sale->save()) {
                throw new \Exception("Failed to save sale");
            }
        
            // Update stock and create sale items
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                try {
                    // Calculate total pieces
                    $totalPieces = $item['is_package']
                        ? ($item['quantity'] * ProductPackage::find($item['package_id'])->pieces_per_package)
                        : $item['quantity'];
    
                    // Update stock first
                    if (!$product->updateStock($totalPieces, 'subtract')) {
                        throw new \Exception("Failed to update stock for product: {$product->name}");
                    }
        
                    // Then create sale item
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
                    throw new \Exception("Stock update failed for {$product->name}: " . $e->getMessage());
                }
            }
        
            // Recalculate sale totals
            $sale->calculateTotals();
          // Handle auto-payment if enabled
          if ($request->auto_payment) {
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

            // Log activity
            ActivityLog::create([
                'log_type' => 'Create',
                'model_type' => "Sale",
                'model_id' => $sale->id,
                'model_identifier' => $sale->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Created sale {$sale->reference_number}",
                'new_values' => $sale->toArray()
            ]);
        
            DB::commit();
        
            return response()->json([
                'message' => 'Sale created successfully',
                'sale' => $sale->load(['items.product', 'items.package', 'client', 'cashSource'])
            ], 201);
        
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Sale creation failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        
            return response()->json([
                'error' => true,
                'message' => 'Error creating sale',
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
    
            // Store original payment status and old data
            $originalPaymentStatus = $sale->payment_status;
            $oldData = $sale->toArray();
    
            // Prepare sale data with preserved payment status
            $saleData = array_merge($request->all(), [
                'payment_status' => $originalPaymentStatus
            ]);
    
            // Update sale basic information
            $sale->update($saleData);
    
            // Handle items if present in request
            if ($request->has('items')) {
                // First, restore old quantities to stock
                foreach ($sale->items as $oldItem) {
                    $product = Product::find($oldItem->product_id);
                    if ($product) {
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
                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_rate' => $item['tax_rate'] ?? 0,
                        'total_price' => ($item['quantity'] * $item['unit_price']) * (1 + ($item['tax_rate'] ?? 0) / 100)
                    ]);
    
                    // Update stock
                    $product->updateStock($item['quantity'], 'subtract');
                }
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
    
            return response()->json([
                'message' => 'Sale updated successfully',
                'sale' => $sale->fresh(['items.product', 'client', 'cashSource'])
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Sale update failed: ' . $e->getMessage(), [
                'sale_id' => $id,
                'user_id' => $user->id,
                'request_data' => $request->all(),
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
          //  'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string'
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
            'PAY-' . date('Ymd-His') . '-' . str_pad($sale->id, 4, '0', STR_PAD_LEFT);
            $transaction = $sale->addPayment($request->amount, $sale->cashSource ,  $request->payment_date,
            $referenceNumber);

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
                'description' => "Added payment of {$request->amount} to sale {$sale->reference_number}",
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

    public function generateInvoice(Request $request, $id)
    {
        $user = $request->user();
        $model = Sale::where('team_id', $user->team->id)->with(['items', 'client'])->find($id);
    
        if (!$model) {
            return response()->json([
                'error' => true,
                'message' => 'Record not found'
            ], 404);
        }
    
        // Reference number generation code remains the same...
        $lastInvoice = Invoice::where('team_id', $user->team->id)
            ->withTrashed()
            ->where('reference_number', 'like', "INV-" . date('Y') . "-%")
            ->orderBy('id', 'desc')
            ->first();
    
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->reference_number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
    
        $referenceNumber = "INV-" . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    
        while (Invoice::where('reference_number', $referenceNumber)->withTrashed()->exists()) {
            $nextNumber++;
            $referenceNumber = "INV-" . date('Y') . "-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        }
    
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
            
            // Prepare contact data based on whether client exists
            $contactData = null;
            if ($model instanceof Purchase) {
                $contactData = $model->supplier ? 
                    ['type' => 'supplier', 'data' => $model->supplier->toArray()] : 
                    ['type' => 'supplier', 'data' => null];
            } else {
                $contactData = $model->client ? 
                    ['type' => 'client', 'data' => $model->client->toArray()] : 
                    ['type' => 'client', 'data' => null];
            }
            
            // Set meta_data with null check for items
            $invoice->meta_data = [
                'source_type' => $model instanceof Purchase ? 'purchase' : 'sale',
                'source_reference' => $model->reference_number,
                'source_date' => $model->created_at,
                'contact' => $contactData,
                'items_data' => $model->items->map(function($item) {
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
                })->toArray()
            ];
            
            $invoice->save();
    
            // Create invoice items with null checks
            foreach ($model->items as $sourceItem) {
                $invoice->items()->create([
                    'description' => $sourceItem->product?->name ?? 'Unknown Product',
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
            \Log::error('Invoice generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'model_id' => $id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error generating invoice: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function generateReceipt(Request $request, $id)
{
    $user = $request->user();
    $sale = Sale::where('team_id', $user->team->id)
                ->with(['items.product', 'team'])
                ->findOrFail($id);

    try {
        // Generate HTML
        $html = View::make('receipts.pdf', [
            'sale' => $sale
        ])->render();

        // Create filename
        $filename = "receipt-{$sale->reference_number}.pdf";
        $tempPath = storage_path('app/public/temp');
        
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
        
        $pdfPath = $tempPath . '/' . $filename;

        // Generate PDF using Browsershot with custom width
        Browsershot::html($html)
            ->format('A4') // Use standard A4 format
            ->windowSize(302, 1122) // 80mm ≈ 302px at 96 DPI
            ->margins(5, 5, 5, 5)
            ->showBackground()
            ->savePdf($pdfPath);

        // Log activity
        ActivityLog::create([
            'log_type' => 'Generate',
            'model_type' => 'Receipt',
            'model_id' => $sale->id,
            'model_identifier' => $sale->reference_number,
            'user_identifier' => $user->name,
            'description' => "Generated receipt for sale {$sale->reference_number}"
        ]);

        return response()->download($pdfPath, $filename, [
            'Content-Type' => 'application/pdf'
        ])->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        \Log::error('Receipt generation error: ' . $e->getMessage());
        return response()->json([
            'error' => true,
            'message' => 'Error generating receipt: ' . $e->getMessage()
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
}