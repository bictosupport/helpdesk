<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;

    public function __construct($otp)
    {
        $this->otp = $otp;
        $this->user = auth()->user(); // Optional: if you want to pass user info
    }

    public function build()
    {
        return $this->subject('Your OTP Code')
                    ->view('mail.otp')
                    ->with([
                        'otp' => $this->otp,
                        'user' => $this->user
                    ]);
    }
}
