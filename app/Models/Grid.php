<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grid extends Model
{
     protected $table = 'grid';

    protected $primaryKey = 'id';

     public $timestamps = false;

    protected $fillable = [
        'UserID',
        'Title',
        'Type',
        'Status',
        'Required',
    ];
}
