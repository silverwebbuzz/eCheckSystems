<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One emailed "Get Check" link for a Send Payment check (client Point 5, Deluxe-style delivery).
 * Only the SHA-256 hash of the link token is stored; the raw token exists only in the email.
 */
class CheckDelivery extends Model
{
    protected $table = 'check_deliveries';

    protected $fillable = [
        'CheckID',
        'UserID',
        'sender_name',
        'payee_email',
        'token_hash',
        'status',
        'sent_at',
        'expires_at',
        'first_viewed_at',
        'last_viewed_at',
        'view_count',
        'print_count',
        'last_printed_at',
        'last_printed_ip',
        'payee_account_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'first_viewed_at' => 'datetime',
        'last_viewed_at' => 'datetime',
        'last_printed_at' => 'datetime',
    ];

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token_hash', static::hashToken($token))->first();
    }

    public function check()
    {
        return $this->belongsTo(Checks::class, 'CheckID', 'CheckID');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function markViewed(): void
    {
        $this->view_count++;
        $this->last_viewed_at = now();
        if (!$this->first_viewed_at) {
            $this->first_viewed_at = now();
        }
        if ($this->status === 'sent') {
            $this->status = 'viewed';
        }
        $this->save();
    }

    public function markPrinted(?string $ip): void
    {
        $this->print_count++;
        $this->last_printed_at = now();
        $this->last_printed_ip = $ip;
        $this->status = 'printed';
        if (!$this->first_viewed_at) {
            $this->first_viewed_at = now();
        }
        $this->save();
    }

    /** Status badge (Sent / Viewed / Printed) for the sender's Send Payment list; empty if never sent by link. */
    public static function senderBadgeHtml(int $checkId): string
    {
        $delivery = static::where('CheckID', $checkId)->first();
        if (!$delivery) {
            return '';
        }

        $class = match (true) {
            $delivery->status === 'printed' => 'bg-label-success',
            $delivery->isExpired() => 'bg-label-secondary',
            $delivery->status === 'viewed' => 'bg-label-info',
            default => 'bg-label-primary',
        };
        $title = 'Link sent ' . optional($delivery->sent_at)->format('m/d/Y')
            . ($delivery->last_printed_at ? ', last printed ' . $delivery->last_printed_at->format('m/d/Y') : '');

        return '<span class="badge ' . $class . ' align-self-center" title="' . e($title) . '">'
            . e($delivery->statusLabel()) . '</span>';
    }

    /** Short label for the sender's Send Payment list. */
    public function statusLabel(): string
    {
        if ($this->status === 'printed') {
            return 'Printed' . ($this->print_count > 1 ? ' (' . $this->print_count . 'x)' : '');
        }
        if ($this->isExpired()) {
            return 'Link expired';
        }
        return $this->status === 'viewed' ? 'Viewed' : 'Sent';
    }
}
