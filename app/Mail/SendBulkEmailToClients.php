<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkEmailToClients extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public $emailContent;

    public $tries = 1;
    /**
     * Create a new message instance.
     */
    public function __construct($subject, $emailContent)
    {
        $this->subject = $subject;
        $this->emailContent = $emailContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'user.emails.mail',
            with: [
                'emailContent' => $this->emailContent,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
