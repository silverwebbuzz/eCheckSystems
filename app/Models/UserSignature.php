<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSignature extends Model
{
    use SoftDeletes;
    protected $table = 'user_signature';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'Id',
        'UserID',
        'Name',
        'Sign',
    ];

    protected $dates = ['deleted_at'];
}
