<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Tells the account owner that a check created in QuickBooks was imported as a draft
 * and is waiting for their review. Nothing is sent to the payee automatically.
 * Uses email template 17 (editable in admin → Email Templates).
 */
class QboCheckImportedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public array $check // ['check_number', 'payee', 'amount', 'review_url']
    ) {
    }

    public function build()
    {
        $emailContent = EmailTemplate::find(17);

        $values = [
            '{{ name }}' => $this->userName,
            '{{ check_number }}' => (string) ($this->check['check_number'] ?? ''),
            '{{ payee }}' => (string) ($this->check['payee'] ?? ''),
            '{{ amount }}' => (string) ($this->check['amount'] ?? ''),
        ];
        $reviewUrl = (string) ($this->check['review_url'] ?? '');

        foreach (['subject', 'content', 'body1', 'body2'] as $field) {
            if (empty($emailContent->{$field})) {
                continue;
            }
            // body1/body2 are printed unescaped (HTML) in the mail view, so escape values there.
            // subject/content are escaped by the view itself, so insert them as plain text.
            $isHtml = in_array($field, ['body1', 'body2'], true);
            $content = Str::of($emailContent->{$field});
            foreach ($values as $key => $value) {
                $content = $content->replace($key, $isHtml ? e($value) : $value);
            }
            $content = $content->replace(
                '{{ review_link }}',
                $isHtml ? '<a href="' . e($reviewUrl) . '" target="_blank">Review check</a>' : $reviewUrl
            );
            $emailContent->{$field} = (string) $content;
        }

        return $this->subject($emailContent->subject)
            ->view('user.emails.mail')
            ->with(['emailContent' => $emailContent]);
    }
}
