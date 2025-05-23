<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomEmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user; //constructor passed from controller
    }

    public function build(){
        $frontend_url = config('app.frontend_url') . '/verify-submit?token=' . $this->user->verification_token; //FOR FRONTEND FORWARDING
        $temp = 'http://127.0.0.1:8000/verify-submit?token='.$this->user->verification_token;
        return $this->subject('Verify your email') //title of the email
            ->view('email.verification') //blade template
            ->with([
                // 'url' => route('verify.email',['token'=>$this->user->verification_token]), //url = {{url}}/email/verify/token FOR CLASSIC BACKEND
                'url' => $temp,
                'user'=>$this->user 
            ]);
    }
}
