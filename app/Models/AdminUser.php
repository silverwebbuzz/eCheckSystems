<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'AdminUser';

    protected $primaryKey = 'AdminID';

    public $timestamps = false;

    protected $fillable = ['Email', 'PasswordHash', 'CreatedAt', 'UpdatedAt'];

    protected $hidden = ['PasswordHash', 'remember_token'];


    /**
     * Get the password for authentication.
     */
    public function getAuthPassword()
    {
        return $this->PasswordHash; // Use PasswordHash for authentication
    }

    /**
     * Set email field for case-sensitive search.
     */
    public function getAuthIdentifierName()
    {
        return 'Email'; // Use Email for authentication
    }
}
