<?php

use App\Http\Controllers\Webhooks\MpesaCallbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhooks/mobile/payment/callback', [MpesaCallbackController::class, 'handle']);

