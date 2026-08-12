<?php

namespace App\Console\Commands;

use App\Models\QBOCompany;
use App\Services\QuickBooksService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncQuickBooksChecks extends Command
{
    protected $signature = 'qbo:sync-checks {--user= : Limit to a specific user id}';

    protected $description = 'Sync QuickBooks checks into Echeck Systems for all connected companies';

    public function handle(QuickBooksService $qbo): int
    {
        $query = QBOCompany::active();
        if ($this->option('user')) {
            $query->where('user_id', $this->option('user'));
        }

        $companies = $query->get();
        if ($companies->isEmpty()) {
            $this->info('No connected QuickBooks companies.');
            return self::SUCCESS;
        }

        foreach ($companies as $company) {
            try {
                $result = $qbo->syncChecksFromQbo($company, (int) $company->user_id);
                $this->info("User {$company->user_id} / {$company->name}: imported {$result['imported']}, updated {$result['updated']}");
            } catch (\Throwable $e) {
                Log::error('Scheduled QBO sync failed', [
                    'qbo_company_id' => $company->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed for {$company->name}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
