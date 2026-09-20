<?php

declare(strict_types=1);

namespace App\Mail\Registration;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class RegistrationApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly User $applicant)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APICS account approved — you may sign in',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.registration.approved',
            text: 'emails.registration.approved-text',
            with: [
                'applicantName' => $this->applicant->name,
                'loginUrl' => url('/login'),
            ],
        );
    }
}
