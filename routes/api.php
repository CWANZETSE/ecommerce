<?php

use App\Http\Controllers\Webhooks\MpesaCallbackController;
use App\Http\Controllers\Webhooks\WhatsAppWebhookController;
use App\Http\Controllers\Webhooks\AfricasTalkingDeliveryReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhooks/mobile/payment/callback', [MpesaCallbackController::class, 'handle']);
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])
    ->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'receive'])
    ->name('webhooks.whatsapp.receive');
Route::post(
    '/webhooks/africastalking/delivery-reports',
    [AfricasTalkingDeliveryReportController::class, 'receive']
)->name('webhooks.africastalking.delivery-reports');

