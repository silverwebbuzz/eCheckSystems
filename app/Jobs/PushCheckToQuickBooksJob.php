<?php

namespace App\Jobs;

use App\Models\Checks;
use App\Models\QBOCompany;
use App\Services\QuickBooksService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushCheckToQuickBooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 45;

    public function __construct(public int $checkId)
    {
        $this->onQueue(config('quickbooks.queues.outgoing', 'qbo-outgoing'));
    }

    public function handle(QuickBooksService $qbo): void
    {
        $check = Checks::find($this->checkId);
        if (!$check) {
            return;
        }

        // Make Payment → QBO Checks; Process Payment → QBO Sales Receipt (routed in QuickBooksService).
        if (!in_array($check->CheckType, ['Make Payment', 'Process Payment'], true)) {
            Log::info('QBO push skipped — unsupported CheckType', [
                'check_id' => $this->checkId,
                'check_type' => $check->CheckType,
            ]);
            return;
        }

        $qboCompany = $check->qbo_company_id
            ? QBOCompany::where('id', $check->qbo_company_id)->where('user_id', $check->UserID)->first()
            : $qbo->activeCompanyForUser((int) $check->UserID);

        if (!$qboCompany || $qboCompany->status !== 'connected') {
            Log::info('QBO push skipped — no active company', ['check_id' => $this->checkId]);
            return;
        }

        $qbo->pushCheckToQbo($check, $qboCompany);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('QBO push job failed permanently', [
            'check_id' => $this->checkId,
            'error' => $e->getMessage(),
        ]);
    }
}
