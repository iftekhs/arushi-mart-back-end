<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SubscriberController;
use App\Http\Controllers\Api\UserController;
use App\Http\Resources\AuthUserResource;
use App\Http\Controllers\Api\CustomizationController as PublicCustomizationController;
use App\Http\Middleware\TrustedClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/user', function (Request $request) {
    return AuthUserResource::make($request->user());
})->middleware('auth:sanctum');

Route::post('/cart/validate', [UserController::class, 'validateCart']);
Route::post('/checkout', [CheckoutController::class, 'store']);
Route::post('/subscribe', [SubscriberController::class, 'subscribe']);

Route::prefix('search')->group(function () {
    Route::get('/', [SearchController::class, 'search']);
    Route::get('/preview', [SearchController::class, 'preview']);
    Route::get('/suggestions', [SearchController::class, 'suggestions']);
});

Route::get('/customizations/{key}', [PublicCustomizationController::class, 'show']);

Route::prefix('settings')->group(function () {
    Route::get('/{key}', [SettingController::class, 'show']);
    Route::get('/secure/{key}', [SettingController::class, 'showSecure'])->middleware(TrustedClient::class);
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

require __DIR__ . '/partials/user.php';
require __DIR__ . '/partials/admin.php';
