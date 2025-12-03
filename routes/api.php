<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ClientPriceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\StockReceiptController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\SupplierInvoiceController;
use App\Http\Controllers\Api\SalesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/categories', [ProductController::class, 'categories']);

// Client routes
Route::get('/clients', [ClientController::class, 'index']);
Route::post('/clients', [ClientController::class, 'store']);
Route::get('/clients/{id}', [ClientController::class, 'show']);
Route::put('/clients/{id}', [ClientController::class, 'update']);
Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
Route::post('/clients/find-by-phone', [ClientController::class, 'findByPhone']);

// Auth routes
Route::post('/admin/login', [AuthController::class, 'login']);
Route::get('/admin/check', [AuthController::class, 'check'])->middleware('auth:sanctum');
Route::post('/admin/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Client Price routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/client-prices', [ClientPriceController::class, 'index']);
    Route::post('/client-prices', [ClientPriceController::class, 'store']);
    Route::get('/client-prices/{id}', [ClientPriceController::class, 'show']);
    Route::put('/client-prices/{id}', [ClientPriceController::class, 'update']);
    Route::delete('/client-prices/{id}', [ClientPriceController::class, 'destroy']);
    Route::post('/client-prices/bulk-update', [ClientPriceController::class, 'bulkUpdate']);
    
    // Client routes (protected)
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
    Route::put('/clients/{id}', [ClientController::class, 'update']);
    Route::delete('/clients/{id}', [ClientController::class, 'destroy']);

    // Admin product routes
    Route::get('/admin/products', [ProductController::class, 'adminIndex']);
    Route::post('/admin/products', [ProductController::class, 'store']);
    Route::post('/admin/products/{product}', [ProductController::class, 'update']); // fallback for clients without PUT support
    Route::put('/admin/products/{product}', [ProductController::class, 'update']);
    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy']);

    // Admin order routes
    Route::get('/orders', [OrderController::class, 'adminIndex']);
    Route::get('/orders/stats/payments', [OrderController::class, 'paymentStats']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
    
    // Sales routes
    Route::get('/sales', [SalesController::class, 'index']);

    // Admin supplier routes
    Route::apiResource('suppliers', SupplierController::class);

    // Admin stock receipt routes
    Route::apiResource('stock-receipts', StockReceiptController::class);

    // Admin stock routes
    Route::get('/stock', [StockController::class, 'index']);
    Route::get('/stock/movements', [StockController::class, 'movements']);
    Route::get('/stock/report', [StockController::class, 'report']);
    
    // Stock adjustment routes
    Route::post('/stock/adjust/{product}', [StockAdjustmentController::class, 'adjust']);
    Route::post('/stock/adjust/bulk', [StockAdjustmentController::class, 'bulkAdjust']);

    // Supplier invoice routes
    Route::apiResource('supplier-invoices', SupplierInvoiceController::class);
    
    // Trash routes
    Route::get('/trash', [\App\Http\Controllers\Api\TrashController::class, 'index']);
    Route::post('/trash/{type}/{id}/restore', [\App\Http\Controllers\Api\TrashController::class, 'restore']);
    Route::delete('/trash/{type}/{id}/force', [\App\Http\Controllers\Api\TrashController::class, 'forceDelete']);
});

// Public client find by phone (for cart identification - legacy)
Route::post('/clients/find-by-phone', [ClientController::class, 'findByPhone']);
// Public client find by business & fiscal number (for cart identification)
Route::post('/clients/find-by-business-and-fiscal', [ClientController::class, 'findByBusinessAndFiscal']);

// Order routes
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/history', [OrderController::class, 'history']);
Route::get('/orders/{order}', [OrderController::class, 'show']);

