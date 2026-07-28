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

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $type;
    public $user_name;
    public $token;

    public $verify_btn;
    public $verify_url;

    public function __construct($type, $user_name, $token=null, $verify_btn=null,$verify_url=null)
    {
        $this->type = $type;
        $this->user_name = $user_name;
        $this->token = $token;
        $this->verify_btn = $verify_btn;
        $this->verify_url = $verify_url;
    }

    // public function build()
    // {
    //     $emailContent = EmailTemplate::find($this->type);

    //     $placeholders['{{ name }}'] = $this->user_name;
    //     $placeholders['{{ verify_btn }}'] = $this->verify_btn;
    //     $placeholders['{{ verify_url }}'] = $this->verify_url;

    //     if(!empty($this->token)) {
    //         $placeholders1['{{ link }}'] = '<a href="'.route('user.showResetForm', ['token' => $this->token]).'">Reset Password</a>';
    //         $content1 = Str::of($emailContent->body1);
    //         foreach ($placeholders1 as $key => $value) {
    //             $emailContent->body1 = $content1->replace($key, $value);
    //         }
    //     }

    //     $content = Str::of($emailContent->content);

    //     foreach ($placeholders as $key => $value) {
    //         $emailContent->content = $content->replace($key, $value);
    //     }
    //     return $this->subject($emailContent->subject)
    //                 ->view('user.emails.mail')
    //                 ->with('emailContent', $emailContent);
    // }

    public function build()
    {
        $emailContent = EmailTemplate::find($this->type);

        $placeholders = [
            '{{ name }}'  => $this->user_name ?? '',
            '{{ verify_url }}'  => $this->verify_url ?? '',
            '{{ verify_btn }}'  => $this->verify_btn ?? ''
        ];
        if(!empty($this->token)) {
            $placeholders['{{ link }}'] = '<a href="'.route('user.showResetForm', ['token' => $this->token]).'">Reset Password</a>';
        }
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
                        'verify_url' => $this->verify_url
                    ]);

        return $mail;
    }

}
