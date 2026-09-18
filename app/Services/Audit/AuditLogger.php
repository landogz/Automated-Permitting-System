<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AuditLogger
{
    public function __construct(private readonly ?Request $request = null)
    {
    }

    /**
     * Log a successful mutating (or significant) event.
     *
     * @param  array<string, mixed>  $meta
     */
    public function log(string $event, array $meta = [], ?User $actor = null): AuditLog
    {
        $request = $this->request ?? request();
        $user = $actor ?? Auth::user();

        if ($user instanceof User && ! $user->relationLoaded('roles')) {
            $user->load('roles:id,name');
        }

        $roles = $user instanceof User
            ? $user->getRoleNames()->values()->all()
            : (isset($meta['actor_roles']) && is_array($meta['actor_roles']) ? $meta['actor_roles'] : []);

        $actorName = $this->resolveActorName($user, $meta);

        $context = array_filter([
            'request_url' => $request?->fullUrl(),
            'request_method' => $request?->method(),
            'session_id' => $request?->hasSession() ? $request->session()->getId() : null,
            'actor_roles' => $roles !== [] ? $roles : null,
            'actor_email' => $user?->email
                ?? (isset($meta['email']) ? (string) $meta['email'] : null)
                ?? (isset($meta['attempted_email']) ? (string) $meta['attempted_email'] : null),
        ], static fn ($value) => $value !== null && $value !== '');

        return AuditLog::query()->create([
            'event' => $event,
            'user_id' => $user?->id,
            'actor_name' => $actorName,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'meta' => array_merge($context, $meta),
        ]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function resolveActorName(?User $user, array $meta): ?string
    {
        if ($user?->name) {
            return $user->name;
        }

        foreach (['actor_name', 'email', 'attempted_email', 'username'] as $key) {
            if (! empty($meta[$key]) && is_scalar($meta[$key])) {
                return (string) $meta[$key];
            }
        }

        return null;
    }
}
