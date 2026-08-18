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

class MarkQuickBooksCheckPrintedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public int $checkId)
    {
        $this->onQueue(config('quickbooks.queues.outgoing', 'qbo-outgoing'));
    }

    public function handle(QuickBooksService $qbo): void
    {
        $check = Checks::find($this->checkId);
        if (!$check || !$check->qbo_id) {
            return;
        }

        $qboCompany = $check->qbo_company_id
            ? QBOCompany::where('id', $check->qbo_company_id)->where('user_id', $check->UserID)->first()
            : $qbo->activeCompanyForUser((int) $check->UserID);

        if (!$qboCompany) {
            return;
        }

        $qbo->markPrintedInQbo($check, $qboCompany);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('QBO mark-printed job failed permanently', [
            'check_id' => $this->checkId,
            'error' => $e->getMessage(),
        ]);
    }
}
