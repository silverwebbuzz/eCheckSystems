<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessQuickBooksWebhook;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuickBooksWebhookController extends Controller
{
    /**
     * Intuit QBO webhook receiver.
     * Respond quickly with 200; process via queue/job.
     *
     * Endpoint (API): POST /api/quickbooks/webhook
     * Configure this HTTPS URL in Intuit Developer → Webhooks.
     * Subscribe to entity: Purchase (Create, Update, Delete, Void).
     */
    public function handle(Request $request, QuickBooksService $qbo)
    {
        $raw = $request->getContent();
        $signature = $request->header('intuit-signature');

        if (!$qbo->verifyWebhookSignature($raw, $signature)) {
            Log::warning('QBO webhook signature verification failed');
            return response('Invalid signature', 401);
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return response('Invalid JSON', 400);
        }

        // Acknowledge immediately; fan-out each Purchase onto qbo-inbound
        ProcessQuickBooksWebhook::dispatch($payload);

        Log::info('QBO webhook queued', [
            'notifications' => count($payload['eventNotifications'] ?? []),
        ]);

        return response('OK', 200);
    }
}
