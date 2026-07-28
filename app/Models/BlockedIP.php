<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIP extends Model
{
    protected $table = 'blocked_ips';

    public $timestamps = false;

    public $fillable = [
        'ip_address',
        'user_id'
    ];
}
