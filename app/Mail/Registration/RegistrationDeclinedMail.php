<?php

declare(strict_types=1);

namespace App\Mail\Registration;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class RegistrationDeclinedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $applicant,
        public readonly string $reason,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APICS registration declined',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.registration.declined',
            text: 'emails.registration.declined-text',
            with: [
                'applicantName' => $this->applicant->name,
                'reason' => $this->reason,
            ],
        );
    }
}
