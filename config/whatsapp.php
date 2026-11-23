
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Cloud API Credentials
    |--------------------------------------------------------------------------
    */

    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),

    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),

    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Graph API Configuration
    |--------------------------------------------------------------------------
    */

    'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v27.0'),

    'base_url' => env(
        'WHATSAPP_BASE_URL',
        'https://graph.facebook.com'
    ),

    'timeout' => (int) env('WHATSAPP_TIMEOUT', 30),

    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),

    'app_secret' => env('WHATSAPP_APP_SECRET'),

];
