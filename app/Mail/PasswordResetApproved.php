<?php

namespace App\Mail;

use App\Models\PasswordResetRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PasswordResetRequest $resetRequest, public string $senderName = 'IT Helpdesk') {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your password reset has been approved',
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), $this->senderName),
        );
    }

    public function content(): Content
    {
        $url = route('password.reset.form', ['token' => $this->resetRequest->token]);
        return new Content(view: 'emails.password-reset-approved', with: [
            'userName' => $this->resetRequest->user->name,
            'resetUrl' => $url,
            'expiresAt' => $this->resetRequest->token_expires_at,
        ]);
    }
}
