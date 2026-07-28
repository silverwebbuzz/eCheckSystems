<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payors extends Model
{
    use SoftDeletes;
    
    protected $table = 'Entities';

    protected $primaryKey = 'EntityID';

    public $timestamps = false;

    protected $fillable = [
        'EntityID',
        'UserID',
        'Name',
        'Address1',
        'Address2',
        'City',
        'State',
        'Zip',
        'Email',
        'Logo',
        'BankName',
        'RoutingNumber',
        'AccountNumber',
        'PhoneNumber',
        'Type',
        'Status',
        'CreatedAt',
        'UpdatedAt',
        'Category',
        'ServiceFeeType',
        'ServiceFee'
    ];

    protected $dates = ['deleted_at'];
}
