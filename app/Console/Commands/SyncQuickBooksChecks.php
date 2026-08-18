<?php

namespace App\Console\Commands;

use App\Jobs\SyncQuickBooksChecksJob;
use App\Models\QBOCompany;
use Illuminate\Console\Command;

class SyncQuickBooksChecks extends Command
{
    protected $signature = 'qbo:sync-checks {--user= : Limit to a specific user id} {--sync : Run inline instead of queue}';

    protected $description = 'Queue QuickBooks check sync for connected companies (or run inline with --sync)';

    public function handle(): int
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
            if ($this->option('sync')) {
                SyncQuickBooksChecksJob::dispatchSync((int) $company->id, (int) $company->user_id);
                $this->info("Synced inline: user {$company->user_id} / {$company->name}");
            } else {
                SyncQuickBooksChecksJob::dispatch((int) $company->id, (int) $company->user_id);
                $this->info("Queued sync: user {$company->user_id} / {$company->name}");
            }
        }

        return self::SUCCESS;
    }
}
