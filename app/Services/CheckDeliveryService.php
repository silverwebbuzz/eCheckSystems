<?php

namespace App\Services;

use App\Mail\CheckDeliveryMail;
use App\Models\CheckDelivery;
use App\Models\Checks;
use App\Models\Payors;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Deluxe-style check delivery (client Point 5): email the payee a secure "Get Check" link
 * instead of attaching the check PDF.
 */
class CheckDeliveryService
{
    /**
     * Create (or refresh, for Resend) the delivery and email the link.
     * Resend issues a NEW token, so the previously emailed link stops working.
     *
     * @throws \Throwable when the email cannot be sent (caller shows the error)
     */
    public function send(Checks $check, Payors $payor, Payors $payee): CheckDelivery
    {
        $token = Str::random(48);

        $delivery = CheckDelivery::where('CheckID', $check->CheckID)->first() ?? new CheckDelivery([
            'CheckID' => $check->CheckID,
            'UserID' => $check->UserID,
        ]);

        $delivery->fill([
            'payee_email' => trim((string) $payee->Email),
            'token_hash' => CheckDelivery::hashToken($token),
            'sent_at' => now(),
            'expires_at' => now()->addDays(config('check_delivery.link_days', 90)),
        ]);
        // A resend keeps the history (views/prints) but goes back to "sent" until opened again.
        if (!$delivery->exists || $delivery->status !== 'printed') {
            $delivery->status = 'sent';
        }
        $delivery->save();

        Mail::to($delivery->payee_email)->send(new CheckDeliveryMail([
            'sender_name' => $payor->Name,
            'payee_name' => $payee->Name,
            'amount' => number_format((float) $check->Amount, 2),
            'check_number' => 'EC' . $check->CheckNumber,
            'memo' => (string) $check->Memo,
            'get_check_url' => route('check_delivery.show', ['token' => $token]),
            'expires_date' => $delivery->expires_at->format('m/d/Y'),
        ]));

        return $delivery;
    }
}
