<?php

declare(strict_types=1);

namespace App\Mail\Registration;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class RegistrationPendingAdminMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly User $applicant)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APICS: new applicant registration pending review',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.registration.pending-admin',
            text: 'emails.registration.pending-admin-text',
            with: [
                'applicantName' => $this->applicant->name,
                'applicantEmail' => $this->applicant->email,
                'applicantPhone' => $this->applicant->phone,
                'reviewUrl' => url('/admin/registrations'),
            ],
        );
    }
}
