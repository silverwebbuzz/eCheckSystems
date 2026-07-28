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

class SendCheckMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $data;
    public $type;
    public $pdfPath;

    public function __construct($type, $data, $pdfPath = null)
    {
        $this->type = $type;
        $this->data = $data;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        $emailContent = EmailTemplate::find($this->type);

        $placeholders = [
            '{{ sender_name }}'  => $this->data['sender_name'] ?? '',
            '{{ client_name }}'  => $this->data['clinet_name'] ?? '',
            '{{ amount }}'       => $this->data['amount'] ?? '',
            '{{ check_number }}' => $this->data['check_number'] ?? '',
            '{{ issued_date }}'  => $this->data['issued_date'] ?? '',
            '{{ memo }}'  => $this->data['memo'] ?? '',
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

        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $mail->attach($this->pdfPath, [
                'as' => 'CheckDetails.pdf',
                'mime' => 'application/pdf',
            ]);
        }
        return $mail;
    }

}
