<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
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
    public function log(string $event, array $meta = []): AuditLog
    {
        $request = $this->request ?? request();
        $user = Auth::user();

        return AuditLog::query()->create([
            'event' => $event,
            'user_id' => $user?->id,
            'actor_name' => $user?->name,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'meta' => $meta,
        ]);
    }
}
