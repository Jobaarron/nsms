<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;
    public $resetCode;
    public $userType;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $token, $resetCode, $userType)
    {
        $this->user = $user;
        $this->token = $token;
        $this->resetCode = $resetCode;
        $this->userType = $userType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Request - Nicolites Montessori School',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.forgot-password',
            with: [
                'user' => $this->user,
                'token' => $this->token,
                'resetCode' => $this->resetCode,
                'userType' => $this->userType,
                'resetLink' => route('password.reset.form', ['token' => $this->token])
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
