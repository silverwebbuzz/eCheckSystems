<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'Company';

    protected $primaryKey = 'CompanyID';

    public $timestamps = false;

    protected $fillable = [
        'CompanyID',
        'UserID',
        'Name',
        'Address1',
        'Address2',
        'City',
        'State',
        'Zip',
        'Email',
        'Logo',
        'PageURL',
        'PageDescription',
        'BankName',
        'RoutingNumber',
        'AccountNumber',
        'Status',
        'Slug',
        'CreatedAt',
        'UpdatedAt',
    ];
}
