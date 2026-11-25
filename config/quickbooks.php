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
    // How QuickBooks work (webhook imports, push on generate, delete, mark printed, Sync now) runs:
    //   after_response (default): no cron / queue worker needed. The HTTP response is sent first
    //                  (QuickBooks gets its 200 immediately), then the work runs in the same request.
    //                  No automatic retries: failures are logged; "Sync now" catches up.
    //   queue:         jobs go to the queues below and need a running worker
    //                  (`php artisan queue:work --queue=qbo-inbound,qbo-outgoing`), with retries.
    'dispatch_mode' => env('QBO_DISPATCH_MODE', 'after_response'),
    'queues' => [
        'inbound' => env('QBO_INBOUND_QUEUE', 'qbo-inbound'),
        'outgoing' => env('QBO_OUTGOING_QUEUE', 'qbo-outgoing'),
    ],
];
