<?php

namespace App\Mail;

use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SendWebFormMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $client_name;
    public $type;
    public $user;
    public $check;

    public function __construct($type, $client_name, $user,$check)
    {
        $this->type = $type;
        $this->client_name = $client_name;
        $this->user = $user;
        $this->check = $check;
    }

    public function build()
    {
        $emailContent = EmailTemplate::find($this->type);

        $placeholders = [
            '{{ user }}'  => $this->user ?? '',
            '{{ client_name }}'  => $this->client_name ?? '',
            '{{ check_number }}'  => $this->check->CheckNumber ?? '',
            '{{ amount }}'  => $this->check->Amount ?? '',
            '{{ service_fee }}'  => $this->check->ServiceFees ?? '',
            '{{ total }}'  => $this->check->Total ?? '',
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
                     ->with('emailContent', $emailContent);

        return $mail;
    }

}
