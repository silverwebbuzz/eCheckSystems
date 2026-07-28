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

class SendNewSubMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $client_name;
    public $type;
    public $data;

    public function __construct($type, $client_name, $data)
    {
        $this->type = $type;
        $this->client_name = $client_name;
        $this->data = $data;
    }

    public function build()
    {
        $emailContent = EmailTemplate::find($this->type);

        $placeholders = [
            '{{ client_name }}'  => $this->client_name ?? '',
            '{{ plan_name }}'  => $this->data['plan_name'] ?? '',
            '{{ start_date }}'  => $this->data['start_date'] ?? '',
            '{{ next_billing_date }}'  => $this->data['next_billing_date'] ?? '',
            '{{ amount }}'  => $this->data['amount'] ?? '',
            '{{ verify_url }}'  => $this->data['verify_url'] ?? '',
            '{{ verify_btn }}'  => $this->data['verify_btn'] ?? ''
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
                        'emailContent'=> $emailContent,
                        'verify_url' => $this->data['verify_url']                    
                    ]);

        return $mail;
    }

}
