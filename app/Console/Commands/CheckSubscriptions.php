<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentSubscription;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\Log;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change subscriptions Plan status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        Log::info('Check Subscriptions started');

        // $today = Carbon::today()->toDateString();
        // $exp_trial_ssubscriptions = PaymentSubscription::whereDate('NextRenewalDate','<', $today)
        //     ->where('PackageID', -1)->where('Status', 'Active')->get();

        // foreach($exp_trial_ssubscriptions as $subscription){

        //     $subscription->CancelAt = now()->toDateString();
        //     $subscription->Status = 'Canceled';
        //     $subscription->save();
            
        // }
         Log::info('Check Subscriptions ended');
    }
}
