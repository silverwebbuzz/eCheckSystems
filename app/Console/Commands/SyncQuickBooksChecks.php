<?php

namespace App\Console\Commands;

use App\Jobs\SyncQuickBooksChecksJob;
use App\Models\QBOCompany;
use App\Support\QboJobs;
use Illuminate\Console\Command;

class SyncQuickBooksChecks extends Command
{
    protected $signature = 'qbo:sync-checks {--user= : Limit to a specific user id} {--sync : Run inline instead of queue}';

    protected $description = 'Queue QuickBooks check sync for connected companies (or run inline with --sync)';

    public function handle(): int
    {
        // Automatic catch-up poll: only for companies with setting 2a (auto import) on.
        $query = QBOCompany::active()->where('auto_import', true);
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
                QboJobs::dispatch(new SyncQuickBooksChecksJob((int) $company->id, (int) $company->user_id));
                $this->info("Queued sync: user {$company->user_id} / {$company->name}");
            }
        }

        return self::SUCCESS;
    }
}
