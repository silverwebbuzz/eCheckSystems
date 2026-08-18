<?php

namespace App\Jobs;

use App\Services\QuickBooksService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessQuickBooksWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 15;

    public function __construct(public array $payload)
    {
        $this->onQueue(config('quickbooks.queues.inbound', 'qbo-inbound'));
    }

    public function handle(QuickBooksService $qbo): void
    {
        $events = $qbo->extractWebhookPurchaseEvents($this->payload);

        foreach ($events as $event) {
            ImportQuickBooksCheckJob::dispatch(
                $event['realmId'],
                $event['id'],
                $event['operation']
            );
        }

        Log::info('QBO webhook queued inbound check jobs', [
            'count' => count($events),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('QBO webhook job failed permanently', [
            'error' => $e->getMessage(),
        ]);
    }
}
