<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
       'id',
       'name',
       'subject',
       'head',
       'content',
       'body1',
       'body2',
    ];
}
