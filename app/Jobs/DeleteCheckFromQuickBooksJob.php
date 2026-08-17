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

class DeleteCheckFromQuickBooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 45;

    public function __construct(
        public string $qboId,
        public int $userId,
        public ?int $qboCompanyId = null
    ) {
        $this->onQueue(config('quickbooks.queues.outgoing', 'qbo-outgoing'));
    }

    public function handle(QuickBooksService $qbo): void
    {
        if ($this->qboId === '') {
            return;
        }

        $qboCompany = $this->qboCompanyId
            ? QBOCompany::where('id', $this->qboCompanyId)->where('user_id', $this->userId)->first()
            : $qbo->activeCompanyForUser($this->userId);

        if (!$qboCompany) {
            Log::info('QBO delete skipped — no company', [
                'qbo_id' => $this->qboId,
                'user_id' => $this->userId,
            ]);
            return;
        }

        $check = new \App\Models\Checks();
        $check->qbo_id = $this->qboId;
        $check->UserID = $this->userId;
        $check->CheckID = 0;

        $qbo->deleteCheckInQbo($check, $qboCompany);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('QBO delete job failed permanently', [
            'qbo_id' => $this->qboId,
            'user_id' => $this->userId,
            'error' => $e->getMessage(),
        ]);
    }
}
