<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum']], function () {
    //Client routes
    Route::apiResource('clients', \App\Http\Controllers\ClientsController::class);
    //Sale routes
    Route::post('sales', [\App\Http\Controllers\SalesController::class, 'store']);
    Route::get('sales', [\App\Http\Controllers\SalesController::class, 'index']);
    Route::get('sales/{id}', [\App\Http\Controllers\SalesController::class, 'show']);
    //Item routes
    Route::get('/products', [\App\Http\Controllers\ItemsController::class, 'products']);
    Route::post('/products', [\App\Http\Controllers\ItemsController::class, 'storeProduct']);
    Route::get('/services', [\App\Http\Controllers\ItemsController::class, 'services']);
    Route::post('/services', [\App\Http\Controllers\ItemsController::class, 'storeService']);
});

require __DIR__.'/auth.php';