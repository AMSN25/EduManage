<?php

return [
    'provider' => env('SMS_PROVIDER', 'log'),
    'api_url' => env('SMS_API_URL', ''),
    'api_key' => env('SMS_API_KEY', ''),
    'sender_id' => env('SMS_SENDER_ID', ''),
];
