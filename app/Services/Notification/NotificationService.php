<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Mail\Notifications\StatusUpdateMail;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Audit\AuditLogger;
use App\Services\Mail\MailSender;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class NotificationService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly MailSender $mail,
    ) {
    }

    public function listTemplates(string $search = '', int $perPage = 25): LengthAwarePaginator
    {
        return NotificationTemplate::query()
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like): void {
                    $inner->where('code', 'like', $like)->orWhere('name', 'like', $like);
                });
            })
            ->orderBy('code')
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createTemplate(array $data): NotificationTemplate
    {
        $template = NotificationTemplate::query()->create($data);

        $this->audit->log('notification_template.created', [
            'template_id' => $template->uuid,
            'code' => $template->code,
        ]);

        return $template;
    }

    public function deleteTemplate(NotificationTemplate $template): void
    {
        $uuid = $template->uuid;
        $code = $template->code;
        $template->delete();

        $this->audit->log('notification_template.deleted', [
            'template_id' => $uuid,
            'code' => $code,
        ]);
    }

    /**
     * Paginate in-app notifications for a user (bell + full inbox).
     *
     * @param  'all'|'unread'|'read'  $status
     */
    public function listForUser(
        User $user,
        bool $unreadOnly = false,
        int $perPage = 25,
        string $search = '',
        string $status = 'all',
    ): LengthAwarePaginator {
        $resolvedStatus = $unreadOnly ? 'unread' : $status;
        if (! in_array($resolvedStatus, ['all', 'unread', 'read'], true)) {
            $resolvedStatus = 'all';
        }

        $search = trim($search);

        return UserNotification::query()
            ->where('user_id', $user->id)
            ->when($resolvedStatus === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($resolvedStatus === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like): void {
                    $inner->where('title', 'like', $like)
                        ->orWhere('body', 'like', $like)
                        ->orWhere('template_code', 'like', $like);
                });
            })
            ->latest('id')
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * @return array{total: int, unread: int, read: int}
     */
    public function countsForUser(User $user): array
    {
        $base = UserNotification::query()->where('user_id', $user->id);
        $total = (clone $base)->count();
        $unread = (clone $base)->whereNull('read_at')->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => max(0, $total - $unread),
        ];
    }

    public function unreadCount(User $user): int
    {
        return $this->countsForUser($user)['unread'];
    }

    /**
     * Always create an in-app row for the bell; optionally email the same content.
     *
     * @param  array<string, string>  $vars
     * @param  array<string, mixed>  $data
     */
    public function notifyUser(
        User $user,
        string $templateCode,
        array $vars = [],
        array $data = [],
        ?string $channelOverride = null,
        bool $sendEmail = true,
    ): UserNotification {
        $template = NotificationTemplate::query()
            ->where('code', $templateCode)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            throw ValidationException::withMessages([
                'template_code' => ['Active notification template not found: '.$templateCode],
            ]);
        }

        $title = $this->render($template->subject ?: $template->name, $vars);
        $body = $this->render($template->body_template, $vars);
        $shouldEmail = $sendEmail && $this->mail->enabled() && filled($user->email);

        // Bell listing uses in_app; email is additive (channelOverride kept for API compatibility).
        $channel = NotificationChannel::InApp;
        if ($channelOverride === NotificationChannel::Email->value && ! $shouldEmail) {
            $channel = NotificationChannel::Email;
        }

        $notification = UserNotification::query()->create([
            'user_id' => $user->id,
            'template_code' => $template->code,
            'channel' => $channel->value,
            'title' => $title,
            'body' => $body,
            'data' => array_merge($data, [
                'email_requested' => $sendEmail && filled($user->email),
                'mail_enabled' => $this->mail->enabled(),
            ]),
            'status' => NotificationDeliveryStatus::Queued->value,
        ]);

        $emailSent = false;
        try {
            if ($shouldEmail) {
                $emailSent = $this->mail->send($user->email, new StatusUpdateMail($notification));
            }

            $notification->fill([
                'status' => NotificationDeliveryStatus::Sent->value,
                'sent_at' => now(),
                'data' => array_merge($notification->data ?? [], ['email_sent' => $emailSent]),
            ])->save();
        } catch (\Throwable $e) {
            Log::warning('notification.email_failed', [
                'notification_id' => $notification->uuid,
                'user_id' => $user->uuid,
                'error' => $e->getMessage(),
            ]);

            // Keep in-app notification visible even when email fails.
            $notification->fill([
                'status' => NotificationDeliveryStatus::Sent->value,
                'sent_at' => now(),
                'data' => array_merge($notification->data ?? [], [
                    'email_sent' => false,
                    'email_error' => true,
                ]),
            ])->save();
        }

        $this->audit->log('notification.sent', [
            'notification_id' => $notification->uuid,
            'user_id' => $user->uuid,
            'template_code' => $template->code,
            'channel' => $notification->channel?->value ?? $notification->channel,
            'email_sent' => $emailSent,
            'delivery_status' => $notification->status?->value ?? $notification->status,
        ]);

        return $notification->fresh() ?? $notification;
    }

    /**
     * Never throw — used by workflow hooks so mail/template issues cannot break mutations.
     *
     * @param  array<string, string>  $vars
     * @param  array<string, mixed>  $data
     */
    public function safeNotify(
        User $user,
        string $templateCode,
        array $vars = [],
        array $data = [],
        bool $sendEmail = true,
    ): ?UserNotification {
        try {
            return $this->notifyUser($user, $templateCode, $vars, $data, null, $sendEmail);
        } catch (\Throwable $e) {
            Log::warning('notification.safe_notify_failed', [
                'user_id' => $user->uuid,
                'template_code' => $templateCode,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendManual(User $actor, array $data): UserNotification
    {
        $user = User::query()->where('uuid', $data['user_uuid'])->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'user_uuid' => ['Recipient not found.'],
            ]);
        }

        $templateCode = $data['template_code'] ?? 'status.update';

        return $this->notifyUser(
            $user,
            $templateCode,
            is_array($data['vars'] ?? null) ? $data['vars'] : [
                'name' => $user->name,
                'message' => (string) ($data['message'] ?? 'Your application status was updated.'),
            ],
            [
                'sent_by' => $actor->uuid,
                'url' => (string) ($data['url'] ?? '/applications'),
                'event' => $templateCode === 'document.correction_requested'
                    ? 'document.correction_requested'
                    : 'manual.send',
            ],
            null,
            true,
        );
    }

    public function markRead(User $user, UserNotification $notification): UserNotification
    {
        if ((int) $notification->user_id !== (int) $user->id && ! $user->can('users.manage')) {
            throw ValidationException::withMessages([
                'notification' => ['You cannot update this notification.'],
            ]);
        }

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return $notification->fresh() ?? $notification;
    }

    public function markAllRead(User $user): int
    {
        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * @param  array<string, string>  $vars
     */
    private function render(string $template, array $vars): string
    {
        $out = $template;
        foreach ($vars as $key => $value) {
            $out = str_replace('{{'.$key.'}}', (string) $value, $out);
        }

        return $out;
    }
}
