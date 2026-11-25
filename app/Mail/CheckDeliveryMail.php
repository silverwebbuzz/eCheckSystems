<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * "You have a check from {sender}" email with a Get Check button (no PDF attached).
 * Uses email template 18 (editable in admin → Email Templates).
 */
class CheckDeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array $data sender_name, payee_name, amount, check_number, memo, get_check_url, expires_date
     */
    public function __construct(public array $data)
    {
    }

    public function build()
    {
        $emailContent = EmailTemplate::find(config('check_delivery.email_template_id', 18));

        $url = (string) ($this->data['get_check_url'] ?? '');
        $values = [
            '{{ sender_name }}' => (string) ($this->data['sender_name'] ?? ''),
            '{{ payee_name }}' => (string) ($this->data['payee_name'] ?? ''),
            '{{ amount }}' => (string) ($this->data['amount'] ?? ''),
            '{{ check_number }}' => (string) ($this->data['check_number'] ?? ''),
            '{{ memo }}' => (string) (($this->data['memo'] ?? '') !== '' ? $this->data['memo'] : '-'),
            '{{ expires_date }}' => (string) ($this->data['expires_date'] ?? ''),
            '{{ support_email }}' => (string) config('check_delivery.support_email'),
            '{{ get_check_url }}' => $url,
        ];
        $button = '<a href="' . e($url) . '" target="_blank" '
            . 'style="background:#7367f0;color:#ffffff;text-decoration:none;padding:14px 34px;'
            . 'border-radius:6px;font-weight:bold;font-size:16px;display:inline-block;">Get Check</a>';

        foreach (['subject', 'content', 'body1', 'body2'] as $field) {
            if (empty($emailContent->{$field})) {
                continue;
            }
            // body1/body2 are printed as raw HTML by the mail view, so escape values there.
            // subject/content are escaped by the view itself, so insert plain text.
            $isHtml = in_array($field, ['body1', 'body2'], true);
            $content = Str::of($emailContent->{$field});
            foreach ($values as $key => $value) {
                $content = $content->replace($key, $isHtml ? e($value) : $value);
            }
            $content = $content->replace('{{ get_check_button }}', $isHtml ? $button : $url);
            $emailContent->{$field} = (string) $content;
        }

        return $this->subject($emailContent->subject)
            ->view('user.emails.mail')
            ->with(['emailContent' => $emailContent]);
    }
}
