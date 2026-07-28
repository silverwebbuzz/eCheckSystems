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

class AdminMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $plan;
    public $type;
    public $name;
    public $email;

    public function __construct($type, $plan, $name, $email)
    {
        $this->type = $type;
        $this->plan = $plan;
        $this->name = $name;
        $this->email = $email;
    }

    public function build()
    {
        $emailContent = EmailTemplate::find($this->type);

        $placeholders = [
            '{{ plan }}'  => $this->plan ?? '',
            '{{ name }}'  => $this->name ?? '',
            '{{ email }}'  => $this->email ?? '',
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
