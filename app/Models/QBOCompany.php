<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QBOCompany extends Model
{
    protected $table = 'qbo_companies';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'start_date',
        'realm_id',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'status'
    ];
}
