<?php

    namespace App\Http\Controllers\Api;
    use App\Http\Controllers\Controller;

    use Illuminate\Http\Request;
    use App\Models\Product;
    use App\Models\ProductUnit;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    use App\Models\ProductBarcode;
    use App\Services\ActivityLogService;
    use App\Models\ActivityLog;

    class ProductController extends Controller
    {
        /**
         * Get all products for the authenticated user's team
         */
        public function index(Request $request)
        {
            $user = $request->user();

            if (!$user->team) {
                return response()->json([
                    'error' => true,
                    'message' => 'No team found for the user'
                ], 404);
            }

            $query = Product::where('team_id', $user->team->id)
                            ->with('unit');

            // Handle filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('low_stock')) {
                $query->lowStock();
            }

            if ($request->has('expiring_soon')) {
                $query->expiringSoon(30); // Next 30 days
            }

            // Handle search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('reference', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            // Handle sorting
            $sortField = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $products = $query->paginate(15);

            return response()->json([
                'products' => $products
            ]);
        }

        /**
         * Get a specific product
         */
        // public function show(Request $request, $id)
        // {
        //     $user = $request->user();

        //     $product = Product::where('team_id', $user->team->id)
        //                       ->with('unit', 'packages')
        //                       ->find($id);

        //     if (!$product) {
        //         return response()->json([
        //             'error' => true,
        //             'message' => 'Product not found'
        //         ], 404);
        //     }

        //     return response()->json([
        //         'product' => $product
        //     ]);
        // }
        public function show(Request $request, $id)
        {
            $user = $request->user();
        
            $product = Product::where('team_id', $user->team->id)
                             ->with('unit', 'packages')
                             ->find($id);
        
            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found'
                ], 404);
            }
        
            // Format the response with explicit package data
            $formattedProduct = [
                'id' => $product->id,
                'reference' => $product->reference,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'purchase_price' => $product->purchase_price,
                'expired_date' => $product->expired_date,
                'quantity' => $product->quantity,
                'product_unit_id' => $product->product_unit_id,
                'sku' => $product->sku,
                'min_stock_level' => $product->min_stock_level,
                'max_stock_level' => $product->max_stock_level,
                'reorder_point' => $product->reorder_point,
                'location' => $product->location,
                'unit' => $product->unit ? [
                    'id' => $product->unit->id,
                    'name' => $product->unit->name,
                ] : null,
                'packages' => $product->packages->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'name' => $package->name,
                        'pieces_per_package' => $package->pieces_per_package,
                        'purchase_price' => $package->purchase_price,
                        'selling_price' => $package->selling_price,
                        'barcode' => $package->barcode,
                        'product_id' => $package->product_id,
                    ];
                })->values()->all()
            ];
        
            // Add debug logging
            \Log::info('Product data being sent:', [
                'product_id' => $product->id,
                'packages' => $formattedProduct['packages']
            ]);
        
            return response()->json([
                'product' => $formattedProduct
            ]);
        }
        
        /**
         * Create a new product
         */
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
                'reference' => 'nullable|unique:products,reference',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|integer|min:0',
                'purchase_price' => 'required|integer|min:0',
                'expired_date' => 'nullable|date',
                'quantity' => 'nullable|integer|min:0',
                'product_unit_id' => 'nullable|exists:product_units,id',
                'sku' => 'nullable|unique:products,sku',
                'barcode' => 'nullable|string',
                'min_stock_level' => 'nullable|integer|min:0',
                'max_stock_level' => 'nullable|integer|min:0',
                'reorder_point' => 'nullable|integer|min:0',
                'location' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            if ($request->has('packages')) {
                $packageValidator = Validator::make($request->all(), [
                    'packages.*.name' => 'required|string|max:255',
                    'packages.*.pieces_per_package' => 'required|integer|min:1',
                    'packages.*.purchase_price' => 'required|integer|min:0',
                    'packages.*.selling_price' => 'required|integer|min:0',
                    'packages.*.barcode' => 'nullable|string|unique:product_packages,barcode'
                ]);
        
                if ($packageValidator->fails()) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Validation error',
                        'errors' => $packageValidator->errors()
                    ], 422);
                }
            }
            $product = new Product($request->all());
            $product->team_id = $user->team->id;
            $product->save();
            if ($request->has('packages')) {
                foreach ($request->packages as $packageData) {
                    $packageData['team_id'] = $user->team->id;
                    $product->packages()->create($packageData);
                }
            }
            $product->load('packages');
            $activityLog = ActivityLog::create([
                'log_type' => 'Create',
                'model_type' => "Product",
                'model_id' => $product->id,
                'model_identifier' => $product->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Product {$product->name} created",
                'new_values' => $product->toArray()
            ]);
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        }

        /**
         * Update a product
         */
        public function update(Request $request, $id)
        {
            $user = $request->user();

            $product = Product::where('team_id', $user->team->id)->find($id);

            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'reference' => ['nullable', Rule::unique('products')->ignore($product->id)],
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|integer|min:0',
                'purchase_price' => 'nullable|integer|min:0',
                'expired_date' => 'nullable|date',
                'quantity' => 'nullable|integer|min:0',
                'product_unit_id' => 'nullable|exists:product_units,id',
                'sku' => ['nullable', Rule::unique('products')->ignore($product->id)],
                'barcode' => 'nullable|string',
                'min_stock_level' => 'nullable|integer|min:0',
                'max_stock_level' => 'nullable|integer|min:0',
                'reorder_point' => 'nullable|integer|min:0',
                'location' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            if ($request->has('packages')) {
                $packageValidator = Validator::make($request->all(), [
                    'packages.*.name' => 'required|string|max:255',
                    'packages.*.pieces_per_package' => 'required|integer|min:1',
                    'packages.*.purchase_price' => 'required|integer|min:0',
                    'packages.*.selling_price' => 'required|integer|min:0',
                    'packages.*.barcode' => 'nullable|string'
                ]);
        
                if ($packageValidator->fails()) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Validation error',
                        'errors' => $packageValidator->errors()
                    ], 422);
                }
            }
            $product->update($request->all());
            if ($request->has('packages')) {
                // Remove existing packages
                $product->packages()->delete();
                
                // Add new packages
                foreach ($request->packages as $packageData) {
                    $packageData['team_id'] = $user->team->id;
                    $product->packages()->create($packageData);
                }
            }
        
            // Load the packages relationship
            $product->load('packages');
            $activityLog = ActivityLog::create([
                'log_type' => 'Update',
                'model_type' => "Product",
                'model_id' => $product->id,
                'model_identifier' => $product->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Product {$product->name} updated",
                'new_values' => $product->toArray()
            ]);
            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product
            ]);
        }

        /**
         * Delete a product
         */
        public function destroy(Request $request, $id)
        {
            $user = $request->user();

            $product = Product::where('team_id', $user->team->id)->find($id);

            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found'
                ], 404);
            }

            $product->delete();
            $activityLog = ActivityLog::create([
                'log_type' => 'Delete',
                'model_type' => "Product",
                'model_id' => $product->id,
                'model_identifier' => $product->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Product {$product->name} deleted",
                'new_values' => $product->toArray()
            ]);
            return response()->json([
                'message' => 'Product deleted successfully'
            ]);
        }

        /**
         * Update product stock
         */
        public function updateStock(Request $request, $id)
        {
            $user = $request->user();

            $product = Product::where('team_id', $user->team->id)->find($id);

            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer',
                'operation' => ['required', Rule::in(['add', 'subtract'])]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                $product->updateStock(
                    $request->quantity,
                    $request->operation
                );
                $activityLog = ActivityLog::create([
                    'log_type' => 'Update',
                    'model_type' => "Product",
                    'model_id' => $product->id,
                    'model_identifier' => $product->name,
                    'user_identifier' => $user?->name,
                    'user_id' => $user->id,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Stock Updated for {$product->name}",
                    'new_values' => $product->toArray()
                ]);
                return response()->json([
                    'message' => 'Stock updated successfully',
                    'product' => $product->fresh()
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => 'Error updating stock'
                ], 500);
            }
        }

        /**
         * Get all product units
         */
        public function getUnits()
        {
            $units = ProductUnit::all();

            return response()->json([
                'units' => $units
            ]);
        }

        /**
         * Get low stock products
         */
        public function getLowStock(Request $request)
        {
            $user = $request->user();

            if (!$user->team) {
                return response()->json([
                    'error' => true,
                    'message' => 'No team found for the user'
                ], 404);
            }

            $products = Product::where('team_id', $user->team->id)
                               ->lowStock()
                               ->with('unit')
                               ->get();

            return response()->json([
                'products' => $products
            ]);
        }
        public function addBarcode(Request $request, $productId)
        {
            $user = $request->user();

            $product = Product::where('team_id', $user->team->id)->find($productId);

            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'barcode' => [
                    'required',
                    'string',
                    Rule::unique('product_barcodes', 'barcode')
                ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                $barcode = $product->addBarcode($request->barcode);
                $activityLog = ActivityLog::create([
                    'log_type' => 'Create',
                    'model_type' => "Product",
                    'model_id' => $product->id,
                    'model_identifier' => $product->name,
                    'user_identifier' => $user?->name,
                    'user_id' => $user->id,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Barcode {$barcode->barcode} created for {$product->name}",
                    'new_values' => $product->toArray()
                ]);
                return response()->json([
                    'message' => 'Barcode added successfully',
                    'barcode' => $barcode
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage()
                ], 400);
            }
        }

        /**
         * Remove a barcode from a product
         */
        public function removeBarcode(Request $request, $productId, $barcodeId)
        {
            $user = $request->user();

            $product = Product::where('team_id', $user->team->id)->find($productId);

            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found'
                ], 404);
            }

            $deleted = $product->removeBarcodeById($barcodeId);

            if (!$deleted) {
                return response()->json([
                    'error' => true,
                    'message' => 'Barcode not found'
                ], 404);
            }
            $activityLog = ActivityLog::create([
                'log_type' => 'Delete',
                'model_type' => "Product",
                'model_id' => $product->id,
                'model_identifier' => $product->name,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Barcode {$barcode} deleted from {$product->name}",
                'new_values' => $product->toArray()
            ]);
            return response()->json([
                'message' => 'Barcode removed successfully'
            ]);
        }

        /**
         * Get all barcodes for a product
         */
        public function getBarcodes(Request $request, $productId)
        {
            $user = $request->user();

            $product = Product::where('team_id', $user->team->id)->find($productId);

            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'barcodes' => $product->barcodes
            ]);
        }
    }
