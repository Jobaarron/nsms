<?php

namespace App\Mail;

use App\Models\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    /**
     * Create a new message instance.
     */
    public function __construct(Enrollee $application)
    {
        $this->application = $application;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Rejection Notice - Nicolites School',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application-rejection',
            with: [
                'application' => $this->application,
                'applicationId' => $this->application->application_id,
                'studentName' => $this->application->first_name . ' ' . $this->application->last_name,
                'rejectionReason' => $this->application->status_reason ?? 'Not specified',
                'rejectedDate' => $this->application->rejected_at?->format('F d, Y') ?? 'N/A',
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
