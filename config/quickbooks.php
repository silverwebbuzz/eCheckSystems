<?php

return [
    'client_id' => env('QBO_CLIENT_ID'),
    'client_secret' => env('QBO_CLIENT_SECRET'),
    'redirect_uri' => env('QBO_REDIRECT_URI'),
    'scope' => env('QBO_SCOPE', 'com.intuit.quickbooks.accounting'),
    // development = sandbox, production = live
    'environment' => env('QBO_ENVIRONMENT', 'development'),
    // Intuit Developer → Webhooks → Show verifier token
    'webhook_verifier_token' => env('QBO_WEBHOOK_VERIFIER_TOKEN'),
    // Purchase = expense Checks (Make Payment); SalesReceipt = receive payments (Process Payment)
    'webhook_entities' => ['Purchase', 'SalesReceipt'],
    'queues' => [
        'inbound' => env('QBO_INBOUND_QUEUE', 'qbo-inbound'),
        'outgoing' => env('QBO_OUTGOING_QUEUE', 'qbo-outgoing'),
    ],
];
