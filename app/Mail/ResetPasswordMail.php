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

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $type;
    public $user_name;

    public function __construct($type, $user_name)
    {
        $this->type = $type;
        $this->user_name = $user_name;
    }

    public function build()
    {
        $emailContent = EmailTemplate::find($this->type);

        $placeholders = [
            '{{ name }}' => $this->user_name,
        ];

        $content = Str::of($emailContent->content);

        foreach ($placeholders as $key => $value) {
            $emailContent->content = $content->replace($key, $value);
        }
        return $this->subject($emailContent->subject)
                    ->view('user.emails.mail')
                    ->with('emailContent', $emailContent);
    }

}
