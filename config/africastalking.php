<?php

return [

    'username' => env('AFRICASTALKING_USERNAME'),

    'api_key' => env('AFRICASTALKING_API_KEY'),

    'sender_id' => env('AFRICASTALKING_SENDER_ID'),

    'environment' => env('AFRICASTALKING_ENVIRONMENT', 'sandbox'),

    'base_url' => env(
        'AFRICASTALKING_BASE_URL',
        env('AFRICASTALKING_ENVIRONMENT', 'sandbox') === 'sandbox'
            ? 'https://api.sandbox.africastalking.com'
            : 'https://api.africastalking.com'
    ),

    'timeout' => env('AFRICASTALKING_TIMEOUT', 15),

];