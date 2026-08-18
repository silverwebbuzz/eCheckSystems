<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckLineItem extends Model
{
    protected $table = 'check_line_items';

    protected $fillable = [
        'CheckID',
        'line_no',
        'qbo_line_id',
        'qbo_account_id',
        'account_name',
        'description',
        'amount',
        'billable',
        'tax',
        'customer_name',
        'customer_ref',
        'source',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'billable' => 'boolean',
        'tax' => 'boolean',
    ];

    public function check()
    {
        return $this->belongsTo(Checks::class, 'CheckID', 'CheckID');
    }
}
