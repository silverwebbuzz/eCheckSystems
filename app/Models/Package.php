<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = 'Package';

    protected $primaryKey = 'PackageID';

    public $timestamps = false;

    protected $fillable = ['Name', 'Description', 'Price', 'Duration', 'CheckLimitPerMonth', 'web_forms','RecurringPaymentFrequency', 'Status', 'CreatedAt', 'UpdatedAt', 'ProductID', 'PlanID', 'PriceID','IsVisToAdmin'];
}
