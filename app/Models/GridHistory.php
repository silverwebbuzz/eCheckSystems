<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GridHistory extends Model
{
      protected $table = 'grid_history';

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
