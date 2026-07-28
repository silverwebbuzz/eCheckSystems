<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSubscription extends Model
{
    protected $table = 'PaymentSubscription';

    protected $primaryKey = 'PaymentSubscriptionID';

    public $timestamps = false;

    protected $fillable = [
        'PaymentSubscriptionID',
        'UserID',
        'PackageID',
        'PaymentMethodID',
        'PaymentAmount',
        'PaymentStartDate',
        'PaymentEndDate',
        'NextRenewalDate',
        'ChecksGiven',
        'ChecksUsed',
        'RemainingChecks',
        'PaymentDate',
        'PaymentAttempts',
        'TransactionID',
        'PromotionID',
        'Status',
        'NextPackageID',
        'ChecksReceived',
        'ChecksSent',
        'ip_address',
        'created_at',
        'is_sys_generated'
    ];

    public function nextPlan()
    {
        return $this->belongsTo(Package::class, 'NextPackageID');
    }
    public function package(){
        return $this->belongsTo(Package::class, 'PackageID', 'PackageID');
    }

    public function user(){
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}
