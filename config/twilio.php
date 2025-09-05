<?php

return [
    'account_sid' => env('TWILIO_ACCOUNT_SID'),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),
    'app_sid' => env('TWILIO_APP_SID'),
    'phone_number' => env('TWILIO_PHONE_NUMBER'),
    'api_key' => env('TWILIO_API_KEY'),
    'api_secret' => env('TWILIO_API_SECRET'),
    'region' => env('TWILIO_REGION', 'au1'),
    'edge' => env('TWILIO_EDGE', 'sydney'),
    'webhook_url' => env('TWILIO_WEBHOOK_URL', 'https://dev.phoneus.com.au/'),
];
