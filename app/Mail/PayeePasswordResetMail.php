<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Password reset link for payee accounts (people who receive checks).
 * Uses the shared eCheck email layout with its own button (the layout's LOGIN button is hidden,
 * because that one points to the customer login).
 */
class PayeePasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $name, public string $resetUrl)
    {
    }

    public function build()
    {
        $url = e($this->resetUrl);
        $emailContent = (object) [
            'subject' => 'Reset your eCheckSystems password',
            'head' => 'Reset your password',
            'content' => 'Hello ' . $this->name . ',',
            'body1' => '<p>We received a request to reset the password for your eCheckSystems account (where you receive your checks).</p>'
                . '<p style="text-align:center;margin:28px 0;"><a href="' . $url . '" target="_blank" '
                . 'style="background:#7367f0;color:#ffffff;text-decoration:none;padding:14px 34px;border-radius:6px;'
                . 'font-weight:bold;font-size:16px;display:inline-block;">Reset password</a></p>'
                . '<p style="font-size:13px;color:#666;">If the button does not work, copy this link into your browser:<br>' . $url . '</p>'
                . '<p style="font-size:13px;color:#666;">This link works for 60 minutes.</p>',
            'body2' => '<p style="font-size:13px;color:#666;">If you did not ask for this, you can ignore this email. Your password will not change.</p>',
        ];

        return $this->subject($emailContent->subject)
            ->view('user.emails.mail')
            ->with(['emailContent' => $emailContent, 'hideActionButton' => true]);
    }
}
