<?php

return [
    'client_id' => env('QBO_CLIENT_ID'),
    'client_secret' => env('QBO_CLIENT_SECRET'),
    'redirect_uri' => env('QBO_REDIRECT_URI'),
    'scope' => env('QBO_SCOPE', 'com.intuit.quickbooks.accounting'),
    // development = sandbox, production = live
    'environment' => env('QBO_ENVIRONMENT', 'development'),
];
