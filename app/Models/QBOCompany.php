<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QBOCompany extends Model
{
    protected $table = 'qbo_companies';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'address',
        'start_date',
        'realm_id',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'status',
        'default_expense_account_id',
        'default_expense_account_name',
        'default_bank_account_id',
        'default_bank_account_name',
        'last_sync_at',
    ];

    protected $casts = [
        'access_token_expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'start_date' => 'date',
    ];

    public function localCompany()
    {
        return $this->belongsTo(Company::class, 'company_id', 'CompanyID');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'connected');
    }
}
