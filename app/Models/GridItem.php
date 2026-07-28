<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GridItem extends Model
{
      protected $table = 'grid_items';

    protected $primaryKey = 'id';

     public $timestamps = false;

    protected $fillable = [
        'CheckID',
        'GridHistoryID',
        'Value',
        'Row'
    ];

    public function grid_history()
    {
        return $this->belongsTo(GridHistory::class, 'GridHistoryID', 'id');
    }
}
