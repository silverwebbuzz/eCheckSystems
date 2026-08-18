<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\QuickBooksWebhookController;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Intuit QBO webhooks (api routes — no CSRF). Must be HTTPS in Intuit portal.
Route::post('/quickbooks/webhook', [QuickBooksWebhookController::class, 'handle'])
    ->name('qbo.webhook');
