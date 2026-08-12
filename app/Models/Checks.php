<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checks extends Model
{
    protected $table = 'Checks';

    protected $primaryKey = 'CheckID';

    public $timestamps = false;

    protected $fillable = [
        'CheckID',
        'UserID',
        'PayeeID',
        'CheckType',
        'Amount',
        'ServiceFees',
        'Total',
        'PayorID',
        'CheckNumber',
        'IssueDate',
        'ExpiryDate',
        'Status',
        'DigitalSignatureRequired',
        'DigitalSignature',
        'Memo',
        'CheckPDF',
        'signed',
        'SignID',
        'is_email_send',
        'is_seen',
        'GridSchemaHistoryID',
        'GridItems',
        'ip_address',
        'created_at',
        'qbo_id',
        'qbo_sync_status',
        'qbo_print_later',
        'qbo_company_id',
        'qbo_doc_number',
        'check_number_conflict',
    ];

    protected $casts = [
        'qbo_print_later' => 'boolean',
        'check_number_conflict' => 'boolean',
    ];

    public function payee()
    {
        return $this->belongsTo(Payors::class, 'PayeeID', 'EntityID');
    }

    public function payor()
    {
        return $this->belongsTo(Payors::class, 'PayorID', 'EntityID');
    }

    public function lineItems()
    {
        return $this->hasMany(CheckLineItem::class, 'CheckID', 'CheckID')->orderBy('line_no');
    }

    public function qboCompany()
    {
        return $this->belongsTo(QBOCompany::class, 'qbo_company_id', 'id');
    }

    public function scopeQuickBooks($query)
    {
        return $query->where(function ($q) {
            $q->where('CheckType', 'QuickBooks')
                ->orWhereNotNull('qbo_id')
                ->orWhere('Status', 'imported_from_qbo');
        });
    }
}
