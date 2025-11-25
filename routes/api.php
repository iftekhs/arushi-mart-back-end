<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\ShippingAddressController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/cart/validate', [UserController::class, 'validateCart']);
Route::post('/checkout', [CheckoutController::class, 'store']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show'])->can('view', 'order');
    });

    // Shipping Addresses
    Route::prefix('shipping-addresses')->group(function () {
        Route::get('/', [ShippingAddressController::class, 'index']);
        Route::post('/', [ShippingAddressController::class, 'store']);
        Route::get('/{shippingAddress}', [ShippingAddressController::class, 'show'])->can('view', 'shippingAddress');
        Route::patch('/{shippingAddress}/set-default', [ShippingAddressController::class, 'setDefault'])->can('update', 'shippingAddress');
        Route::put('/{shippingAddress}', [ShippingAddressController::class, 'update'])->can('update', 'shippingAddress');
        Route::delete('/{shippingAddress}', [ShippingAddressController::class, 'delete'])->can('delete', 'shippingAddress');
    });
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category:slug}', [CategoryController::class, 'show']);
    Route::get('/{category:slug}/products', [CategoryController::class, 'products']);
    Route::get('/{category:slug}/colors', [CategoryController::class, 'colors']);
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/featured', [ProductController::class, 'featured']);
    Route::get('/{product:slug}', [ProductController::class, 'show'])->can('view', 'product');
    Route::get('/{product:slug}/related', [ProductController::class, 'related']);
});

Route::get('/colors', [ColorController::class, 'index']);

Route::prefix('variants')->group(function () {
    Route::get('/{variant}', [ProductVariantController::class, 'show']);
});
