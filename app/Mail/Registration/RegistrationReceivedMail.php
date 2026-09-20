<?php

declare(strict_types=1);

namespace App\Mail\Registration;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class RegistrationReceivedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly User $applicant)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APICS registration received — pending approval',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.registration.received',
            text: 'emails.registration.received-text',
            with: [
                'applicantName' => $this->applicant->name,
            ],
        );
    }
}
