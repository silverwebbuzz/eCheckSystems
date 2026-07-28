<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminSuggestionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $suggestion;

    public $type;
 
    public function __construct($type, $suggestion)
    {
        $this->type = $type;
        $this->suggestion = $suggestion;
    }

    public function build()
    {
        $emailContent = EmailTemplate::find($this->type);

        $placeholders = [
            '{{ client_name }}'  => $this->suggestion?->user?->FirstName.' '.$this->suggestion?->user?->LastName  ?? '',
            '{{ client_email }}'  => $this->suggestion?->user?->Email ?? '',
            '{{ section_name }}'  => $this->suggestion?->section ?? '',
            '{{ description }}'  => $this->suggestion?->description ?? '',
        ];

        $fields = ['subject', 'content', 'body1', 'body2'];

        foreach ($fields as $field) {
            if (!empty($emailContent->{$field})) {
                $content = Str::of($emailContent->{$field});
                foreach ($placeholders as $key => $value) {
                    $content = $content->replace($key, $value);
                }
                $emailContent->{$field} = $content;
            }
        }

        $mail = $this->subject($emailContent->subject)
                     ->view('user.emails.mail')
                     ->with([
                        'emailContent' => $emailContent,
                        'isAdmin' => 1
                    ]);

        return $mail;
    }

}
