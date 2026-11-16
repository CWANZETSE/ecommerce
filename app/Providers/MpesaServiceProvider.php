<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MpesaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bridge our MPESA_* env keys to the DARAJA_* keys the installed
        // starnerz/laravel-daraja package reads internally.
        config([
            'laravel-daraja.mode' => config('mpesa.env', 'sandbox'),
            'laravel-daraja.consumer_key' => config('mpesa.consumer_key'),
            'laravel-daraja.consumer_secret' => config('mpesa.consumer_secret'),
            'laravel-daraja.stk.short_code' => config('mpesa.shortcode'),
            'laravel-daraja.stk.pass_key' => config('mpesa.passkey'),
            'laravel-daraja.stk.callback_url' => config('mpesa.callback_url'),
        ]);
    }
}