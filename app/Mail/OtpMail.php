<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $otp) {}

    public function build()
    {
        $ttl = config('otp.ttl_minutes', 10);
        return $this->subject('Your login code')
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
                'ttl' => $ttl,
            ]);
    }
}
