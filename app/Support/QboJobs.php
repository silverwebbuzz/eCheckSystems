<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Runs QuickBooks jobs according to config('quickbooks.dispatch_mode').
 *
 *  - after_response (default): the job runs in the same PHP process right after the HTTP
 *    response has been sent (Laravel "terminating" callback; with PHP-FPM / LiteSpeed the
 *    browser or Intuit already has its response). No queue worker or cron is needed.
 *  - queue: the job is pushed to the database queue and needs `php artisan queue:work`.
 */
class QboJobs
{
    public static function usesQueue(): bool
    {
        return config('quickbooks.dispatch_mode') === 'queue';
    }

    /**
     * Dispatch from a web request or console command.
     *
     * @param bool $afterCommit queue mode only: wait for the open DB transaction to commit.
     *                          (In after_response mode the request, and its transaction, is already finished.)
     */
    public static function dispatch(object $job, bool $afterCommit = false): void
    {
        if (self::usesQueue()) {
            $pending = dispatch($job);
            if ($afterCommit) {
                $pending->afterCommit();
            }
            return;
        }

        app()->terminating(function () use ($job) {
            self::runNow($job);
        });
    }

    /**
     * Dispatch from inside a QuickBooks job. In after_response mode that job is already running
     * after the response, so run the child job immediately instead of deferring it again.
     */
    public static function dispatchFromJob(object $job): void
    {
        if (self::usesQueue()) {
            dispatch($job);
            return;
        }

        self::runNow($job);
    }

    /**
     * Run a job synchronously. Errors are logged, never thrown: the response is already sent,
     * and one failing job must not stop the others.
     */
    public static function runNow(object $job): void
    {
        // A full "Sync now" can take a while on shared hosting.
        @set_time_limit(300);

        try {
            dispatch_sync($job);
        } catch (\Throwable $e) {
            Log::error('QBO job failed (after_response mode)', [
                'job' => get_class($job),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
