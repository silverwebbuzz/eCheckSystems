<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Account for someone who RECEIVES checks (Deluxe-style delivery, "Create an account to get your
 * payment"). Separate from customer accounts (App\Models\User): no plan, no admin approval.
 * Logs in with the 'payee' guard and sees every delivered check sent to its email address.
 */
class PayeeAccount extends Authenticatable
{
    protected $table = 'payee_accounts';

    protected $fillable = ['name', 'email', 'password', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /** Deliveries for this account: every check link emailed to its address. */
    public function deliveries()
    {
        return CheckDelivery::whereRaw('LOWER(payee_email) = ?', [strtolower($this->email)])
            ->orderByDesc('id');
    }
}
