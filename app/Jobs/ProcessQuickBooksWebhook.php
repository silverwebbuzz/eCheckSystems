<?php

namespace App\Jobs;

use App\Models\QBOCompany;
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

    public int $backoff = 30;

    public function __construct(public array $payload)
    {
        $this->onQueue('quickbooks');
    }

    public function handle(QuickBooksService $qbo): void
    {
        $result = $qbo->processWebhookPayload($this->payload);

        Log::info('QBO webhook processed', $result);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('QBO webhook job failed permanently', [
            'error' => $e->getMessage(),
        ]);
    }
}
