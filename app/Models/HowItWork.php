<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HowItWork extends Model
{
     protected $table = 'how_it_works';

    protected $primaryKey = 'id';

     public $timestamps = false;

     protected $fillable = [
        'section',
        'link',
        'status',
    ];
}
