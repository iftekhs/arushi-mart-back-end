<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ShippingAddressController;
use App\Http\Controllers\Api\SubscriberController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/cart/validate', [UserController::class, 'validateCart']);
Route::post('/checkout', [CheckoutController::class, 'store']);
Route::post('/subscribe', [SubscriberController::class, 'subscribe']);

Route::prefix('search')->group(function () {
    Route::get('/', [SearchController::class, 'search']);
    Route::get('/preview', [SearchController::class, 'preview']);
    Route::get('/suggestions', [SearchController::class, 'suggestions']);
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

Route::prefix('colors')->group(function () {
    Route::get('/', [ColorController::class, 'index']);
});

Route::prefix('variants')->group(function () {
    Route::get('/{variant}', [ProductVariantController::class, 'show']);
});

// Admin routes
Route::middleware(['auth:sanctum', CheckUserRole::for([UserRole::ADMIN, UserRole::SUPERADMIN])])->prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus']);
});

// Authenticated routes
Route::middleware(['auth:sanctum', CheckUserRole::for(UserRole::USER)])->group(function () {
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
