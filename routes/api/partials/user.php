<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ShippingAddressController;
use App\Http\Middleware\CheckUserRole;
use Illuminate\Support\Facades\Route;

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
