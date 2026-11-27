
<?php

$environment = strtolower(
    env('AFRICASTALKING_ENVIRONMENT', 'sandbox')
);

return [

    'username' => env('AFRICASTALKING_USERNAME'),

    'api_key' => env('AFRICASTALKING_API_KEY'),

    'sender_id' => env('AFRICASTALKING_SENDER_ID'),

    'environment' => $environment,

    'endpoint' => env(
        'AFRICASTALKING_ENDPOINT',
        $environment === 'sandbox'
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging/bulk'
    ),

    'timeout' => (int) env('AFRICASTALKING_TIMEOUT', 15),

];
