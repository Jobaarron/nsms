<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Enrollee;

class EnrolleeCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public Enrollee $enrollee;
    public string $applicationId;
    public string $password;

    public function __construct(Enrollee $enrollee)
    {
        $this->enrollee = $enrollee;
        $this->applicationId = $enrollee->application_id;
        $this->password = $enrollee->getPlainPassword();
    }
    
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nicolites Portal | Adminssion Application Credentials',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.enrollee_credentials',
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
