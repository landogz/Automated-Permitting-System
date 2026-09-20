<?php

declare(strict_types=1);

namespace App\Mail\Notifications;

use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusUpdateMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly UserNotification $notification)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->title,
        );
    }

    public function content(): Content
    {
        $data = is_array($this->notification->data) ? $this->notification->data : [];
        $actionUrl = isset($data['action_url']) && is_string($data['action_url'])
            ? $data['action_url']
            : null;
        $actionLabel = isset($data['action_label']) && is_string($data['action_label'])
            ? $data['action_label']
            : 'Open in APICS';

        return new Content(
            html: 'emails.notifications.status-update',
            text: 'emails.notifications.status-update-text',
            with: [
                'title' => $this->notification->title,
                'body' => $this->notification->body,
                'actionUrl' => $actionUrl,
                'actionLabel' => $actionLabel,
            ],
        );
    }
}
