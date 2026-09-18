<?php

declare(strict_types=1);

namespace App\Services\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Central mail gate — respects MAIL_ENABLED from .env / config('mail.enabled').
 */
final class MailSender
{
    /**
     * Whether outbound email delivery is enabled.
     */
    public function enabled(): bool
    {
        return (bool) config('mail.enabled', true);
    }

    /**
     * Send a mailable when mail is enabled. Returns true if delivery was attempted.
     *
     * @param  object|array<int, object|string>|string  $notifiable
     */
    public function send(object|array|string $notifiable, Mailable $mailable): bool
    {
        if (! $this->enabled()) {
            Log::info('mail.skipped', [
                'mailable' => $mailable::class,
                'reason' => 'MAIL_ENABLED=false',
            ]);

            return false;
        }

        Mail::to($notifiable)->send($mailable);

        return true;
    }
}
