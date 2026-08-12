<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QboSyncLog extends Model
{
    protected $table = 'qbo_sync_logs';

    protected $fillable = [
        'user_id',
        'qbo_company_id',
        'direction',
        'action',
        'status',
        'records',
        'message',
    ];
}
