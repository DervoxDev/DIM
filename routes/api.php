<?php

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\AuthController;
    use App\Http\Controllers\Api\SubscriptionController;
    use App\Http\Controllers\Api\ProductController;

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
            Route::delete('/products/{productId}/barcodes/{barcodeId}', [ProductController::class, 'removeBarcode']);
            Route::get('/products/{productId}/barcodes', [ProductController::class, 'getBarcodes']);
        });
    });
