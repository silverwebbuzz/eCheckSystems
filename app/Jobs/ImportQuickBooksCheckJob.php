<?php

namespace App\Jobs;

use App\Services\QuickBooksService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportQuickBooksCheckJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public int $uniqueFor = 120;

    public function __construct(
        public string $realmId,
        public string $purchaseId,
        public string $operation
    ) {
        $this->onQueue(config('quickbooks.queues.inbound', 'qbo-inbound'));
    }

    public function uniqueId(): string
    {
        return $this->realmId . ':' . $this->purchaseId . ':' . strtolower($this->operation);
    }

    public function handle(QuickBooksService $qbo): void
    {
        $result = $qbo->processWebhookEntity($this->realmId, $this->purchaseId, $this->operation);

        Log::info('QBO inbound check processed', [
            'realmId' => $this->realmId,
            'purchaseId' => $this->purchaseId,
            'operation' => $this->operation,
            'result' => $result,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('QBO inbound check job failed permanently', [
            'realmId' => $this->realmId,
            'purchaseId' => $this->purchaseId,
            'operation' => $this->operation,
            'error' => $e->getMessage(),
        ]);
    }
}
