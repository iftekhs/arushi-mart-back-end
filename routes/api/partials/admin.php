<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\ColorController as AdminColorController;
use App\Http\Controllers\Api\Admin\CustomizationController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\SizeController;
use App\Http\Controllers\Api\Admin\TagController as AdminTagController;
use App\Http\Middleware\CheckUserRole;
use Illuminate\Support\Facades\Route;

// Admin routes
Route::middleware(['auth:sanctum', CheckUserRole::for([UserRole::ADMIN, UserRole::SUPERADMIN])])->prefix('admin')->group(function () {
    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/metrics', [DashboardController::class, 'metrics']);
        Route::get('/sales-overview', [DashboardController::class, 'salesOverview']);
        Route::get('/latest-orders', [DashboardController::class, 'latestOrders']);
        Route::get('/analytics', [DashboardController::class, 'analytics']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [AdminProductController::class, 'index']);
        Route::get('/search-variants', [AdminProductController::class, 'searchVariants']);
        Route::get('/search-by-sku', [AdminProductController::class, 'searchBySku']);
        Route::post('/', [AdminProductController::class, 'store']);
        Route::get('/{product}', [AdminProductController::class, 'show']);
        Route::post('/{product}', [AdminProductController::class, 'update']);
        Route::patch('/{product}/toggle-active', [AdminProductController::class, 'toggleActive']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/export', [AdminUserController::class, 'export']);
        Route::patch('/{user}/toggle-status', [AdminUserController::class, 'toggleStatus']);
    });

    Route::prefix('sizes')->group(function () {
        Route::get('/', [SizeController::class, 'index']);
        Route::post('/', [SizeController::class, 'store']);
        Route::post('/update-order', [SizeController::class, 'updateOrder']);
        Route::put('/{size}', [SizeController::class, 'update']);
        Route::delete('/{size}', [SizeController::class, 'delete']);
    });

    Route::apiResource('colors', AdminColorController::class);

    Route::prefix('tags')->group(function () {
        Route::get('/', [AdminTagController::class, 'index']);
        Route::post('/sync', [AdminTagController::class, 'syncTags']);
        Route::delete('/{tag}', [AdminTagController::class, 'delete']);
    });

    Route::apiResource('categories', AdminCategoryController::class);

    Route::prefix('orders')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index']);
        Route::post('/', [AdminOrderController::class, 'store']);
        Route::get('/metrics', [AdminOrderController::class, 'metrics']);
        Route::get('/{order}', [AdminOrderController::class, 'show']);
        Route::post('/{order}/cancel', [AdminOrderController::class, 'cancel'])->can('cancel', 'order');
        Route::patch('/{order}/shipping-status', [AdminOrderController::class, 'updateShippingStatus'])->can('updateShippingStatus', 'order');
    });

    Route::prefix('customizations')->group(function () {
        Route::get('/', [CustomizationController::class, 'index']);
        Route::post('/{customization}', [CustomizationController::class, 'update']);
    });
});
