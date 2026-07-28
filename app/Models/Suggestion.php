<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    protected $fillable = [
        'section',
        'user_id',
        'description'
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id','UserID');
    }
}
