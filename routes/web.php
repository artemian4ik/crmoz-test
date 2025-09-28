<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZohoIntegrationController;
use App\Http\Controllers\DealController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('zoho-integration')->name('zoho.integration.')->group(function () {
    Route::get('/', [ZohoIntegrationController::class, 'index'])->name('index');
    Route::get('/callback', [ZohoIntegrationController::class, 'callback'])->name('callback');
    Route::get('/status', [ZohoIntegrationController::class, 'status'])->name('status');
    Route::post('/refresh', [ZohoIntegrationController::class, 'refreshTokens'])->name('refresh');
});

Route::prefix('deal')->name('deal.')->group(function () {
    Route::get('/', [DealController::class, 'index'])->name('index');
    Route::post('/create', [DealController::class, 'create'])->name('create');
});