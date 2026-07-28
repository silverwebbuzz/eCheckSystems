<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payors;

class WebForm extends Model
{
    protected $table = 'Webform';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'Id',
        'UserID',
        'PayeeID',
        'Logo',
        'page_url',
        'page_desc',
        'service_fees',
        'service_fees_type',
    ];

    public function payee()
    {
        return $this->belongsTo(Payors::class, 'PayeeID', 'EntityID');
    }
}
