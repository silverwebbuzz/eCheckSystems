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

class SyncQuickBooksChecksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public int $backoff = 60;

    public function __construct(
        public int $qboCompanyId,
        public int $userId
    ) {
        $this->onQueue('quickbooks');
    }

    public function handle(QuickBooksService $qbo): void
    {
        $company = QBOCompany::where('id', $this->qboCompanyId)
            ->where('user_id', $this->userId)
            ->where('status', 'connected')
            ->first();

        if (!$company) {
            Log::warning('QBO sync job skipped — company not connected', [
                'qbo_company_id' => $this->qboCompanyId,
                'user_id' => $this->userId,
            ]);
            return;
        }

        $result = $qbo->syncChecksFromQbo($company, $this->userId);

        Log::info('QBO sync job finished', [
            'qbo_company_id' => $this->qboCompanyId,
            'user_id' => $this->userId,
            'result' => $result,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('QBO sync job failed permanently', [
            'qbo_company_id' => $this->qboCompanyId,
            'user_id' => $this->userId,
            'error' => $e->getMessage(),
        ]);
    }
}
