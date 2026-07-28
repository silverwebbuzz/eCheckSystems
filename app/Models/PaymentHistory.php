<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $table = 'PaymentHistory';

    protected $primaryKey = 'PaymentHistoryID';

    public $timestamps = false;

    protected $fillable = [
        'PaymentHistoryID',
        'PaymentSubscriptionID',
        'PaymentAmount',
        'PaymentDate',
        'PaymentStatus',
        'PaymentAttempts',
        'TransactionID',
        'PaymentUrl',
        'Remarks',
        'created_at'
    ];

    public function subscription()
    {
        return $this->belongsTo(PaymentSubscription::class, 'PaymentSubscriptionID');
    }
}