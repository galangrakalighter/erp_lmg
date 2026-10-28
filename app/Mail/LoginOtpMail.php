<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $code, public readonly Carbon $expiresAt) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode OTP Login ERP Lighter Media Group');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.login-otp');
    }
}
