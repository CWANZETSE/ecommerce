<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\ReconcileMpesaPaymentJob;
use App\Models\Payment;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Payment::where('status', 'processing')
        ->where('created_at', '<', now()->subSeconds(90))
        ->whereNotNull('checkout_request_id')
        ->limit(20)
        ->get()
        ->each(fn (Payment $p) => ReconcileMpesaPaymentJob::dispatch($p->id));
})->everyFiveMinutes()->name('reconcile-mpesa-payments');
