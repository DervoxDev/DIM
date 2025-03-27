<?php

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\AuthController;
    use App\Http\Controllers\Api\SubscriptionController;
    use App\Http\Controllers\Api\ProductController;
    use App\Http\Controllers\Api\ActivityLogController;
    use App\Http\Controllers\Api\SupplierController;
    use App\Http\Controllers\Api\CashSourceController;
    use App\Http\Controllers\Api\CashTransactionController;
    use App\Http\Controllers\Api\PurchaseController;
    use App\Http\Controllers\Api\SaleController;
    use App\Http\Controllers\Api\ClientController;
    use App\Http\Controllers\Api\InvoiceController;
    use App\Http\Controllers\Api\DashboardAnalyticsController;
    use App\Http\Controllers\Api\TeamController;
    use App\Http\Controllers\LanguageController;

    Route::group(['prefix' =>'v1'], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::group(['middleware' => 'auth:sanctum'], function() {
            Route::get('logout', [AuthController::class, 'logout']);
            Route::get('user', [AuthController::class, 'user']);
            Route::get('subscription/status', [SubscriptionController::class, 'getStatus']);

            // Product routes
            Route::get('products', [ProductController::class, 'index']);
            Route::get('products/{id}', [ProductController::class, 'show']);
            Route::post('products', [ProductController::class, 'store']);
            Route::put('products/{id}', [ProductController::class, 'update']);
            Route::delete('products/{id}', [ProductController::class, 'destroy']);

            // Additional product routes
            Route::post('products/{id}/stock', [ProductController::class, 'updateStock']);
            Route::get('products/low-stock', [ProductController::class, 'getLowStock']);
            Route::get('product-units', [ProductController::class, 'getUnits']);

            Route::post('/products/{productId}/barcodes', [ProductController::class, 'addBarcode']);
            Route::put('/products/{productId}/barcodes/{barcodeId}', [ProductController::class, 'updateBarcode']);
            Route::delete('/products/{productId}/barcodes/{barcodeId}', [ProductController::class, 'removeBarcode']);
            Route::get('/products/{productId}/barcodes', [ProductController::class, 'getBarcodes']);
            Route::delete('products/{id}/image', [ProductController::class, 'removeImage']);
            Route::post('products/{id}/image', [ProductController::class, 'uploadImage']); 
            Route::prefix('activity-logs')->group(function () {
                Route::get('/', [ActivityLogController::class, 'index']);
                Route::get('/{id}', [ActivityLogController::class, 'show']);
                Route::get('/statistics', [ActivityLogController::class, 'statistics']);
                Route::get('/filter-options', [ActivityLogController::class, 'filterOptions']);
                Route::delete('/cleanup', [ActivityLogController::class, 'cleanup']); 
            });

            Route::get('/suppliers', [SupplierController::class, 'index']);
            Route::post('/suppliers', [SupplierController::class, 'store']);
            Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
            Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
            Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']); 

            Route::get('/cash-sources', [CashSourceController::class, 'index']);
            Route::post('/cash-sources', [CashSourceController::class, 'store']);
            Route::get('/cash-sources/{id}', [CashSourceController::class, 'show']);
            Route::put('/cash-sources/{id}', [CashSourceController::class, 'update']);
            Route::delete('/cash-sources/{id}', [CashSourceController::class, 'destroy']);
            
            // Transaction routes
            Route::post('/cash-sources/{id}/deposit', [CashSourceController::class, 'deposit']);
            Route::post('/cash-sources/{id}/withdraw', [CashSourceController::class, 'withdraw']);
            Route::post('/cash-sources/{id}/transfer', [CashSourceController::class, 'transfer']);

            Route::get('/transactions', [CashTransactionController::class, 'index']);
            Route::get('/transactions/{id}', [CashTransactionController::class, 'show']);
            Route::get('/transactions/by-source/{sourceId}', [CashTransactionController::class, 'getBySource']);
            Route::get('/transactions/summary', [CashTransactionController::class, 'getSummary']);

            Route::get('/purchases', [PurchaseController::class, 'index']);
            Route::post('/purchases', [PurchaseController::class, 'store']);
            Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
            Route::put('/purchases/{id}', [PurchaseController::class, 'update']);
            Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy']);
            Route::post('/purchases/{id}/add-payment', [PurchaseController::class, 'addPayment']);
            Route::post('/purchases/{id}/generate-invoice', [PurchaseController::class, 'generateInvoice']);
            Route::get('/purchases/summary', [PurchaseController::class, 'getSummary']);

            Route::get('/sales', [SaleController::class, 'index']);
            Route::post('/sales', [SaleController::class, 'store']);
            Route::get('/sales/{id}', [SaleController::class, 'show']);
            Route::put('/sales/{id}', [SaleController::class, 'update']);
            Route::get('sales/{id}/receipt', [SaleController::class, 'generateReceipt']);
            Route::delete('/sales/{id}', [SaleController::class, 'destroy']);
            Route::post('/sales/{id}/add-payment', [SaleController::class, 'addPayment']);
            Route::post('/sales/{id}/generate-invoice', [SaleController::class, 'generateInvoice']);
           
            Route::get('/sales/summary', [SaleController::class, 'getSummary']);

            Route::get('/clients', [ClientController::class, 'index']);
            Route::post('/clients', [ClientController::class, 'store']);
            Route::get('/clients/{id}', [ClientController::class, 'show']);
            Route::put('/clients/{id}', [ClientController::class, 'update']);
            Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
            Route::get('/clients/{id}/sales', [ClientController::class, 'getSales']);
            Route::get('/clients/{id}/transactions', [ClientController::class, 'getTransactions']);
            Route::get('/clients/{id}/statement', [ClientController::class, 'getStatement']);

            Route::get('/invoices', [InvoiceController::class, 'index']);
            Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
            Route::post('/invoices', [InvoiceController::class, 'store']);
            Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
            Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
            Route::post('/invoices/{id}/send', [InvoiceController::class, 'send']);
            Route::post('/invoices/{id}/mark-as-paid', [InvoiceController::class, 'markAsPaid']);
            Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']);
       
            Route::prefix('teams')->group(function () {
                Route::get('/', [TeamController::class, 'index']);
                Route::get('/{id}', [TeamController::class, 'show']);
                Route::post('/', [TeamController::class, 'store']);
                Route::put('/{id}', [TeamController::class, 'update']);
                Route::delete('/{id}', [TeamController::class, 'destroy']);
                Route::post('/{id}/image', [TeamController::class, 'uploadImage']);
                Route::delete('/{id}/image', [TeamController::class, 'removeImage']);
                Route::post('/{id}/language', [LanguageController::class, 'changeInvoiceLanguage']);
                Route::get('/{id}/language', [LanguageController::class, 'getInvoiceLanguage']);
            });
            Route::prefix('dashboard')->group(function () {
                Route::get('/sales-analytics', [DashboardAnalyticsController::class, 'getSaleAnalytics']);
                Route::get('/purchase-analytics', [DashboardAnalyticsController::class, 'getPurchaseAnalytics']);
                Route::get('/inventory-analytics', [DashboardAnalyticsController::class, 'getInventoryAnalytics']);
                Route::get('/customer-analytics', [DashboardAnalyticsController::class, 'getCustomerAnalytics']);
                Route::get('/overall', [DashboardAnalyticsController::class, 'getOverallDashboard']);
            });
            

        });
        Route::prefix('analytics')->group(function () {
            Route::get('/sales', [DashboardAnalyticsController::class, 'getSaleAnalytics']);
            Route::get('/purchases', [DashboardAnalyticsController::class, 'getPurchaseAnalytics']);
            Route::get('/inventory', [DashboardAnalyticsController::class, 'getInventoryAnalytics']);
            Route::get('/customers', [DashboardAnalyticsController::class, 'getCustomerAnalytics']);
            Route::get('/dashboard', [DashboardAnalyticsController::class, 'getOverallDashboard']);
        });
    });
