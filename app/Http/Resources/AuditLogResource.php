<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\Audit\AuditEventClassifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AuditLog */
class AuditLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $meta = is_array($this->meta) ? $this->meta : [];
        $event = (string) $this->event;
        $roles = [];

        if ($this->relationLoaded('user') && $this->user) {
            if (! $this->user->relationLoaded('roles')) {
                $this->user->load('roles:id,name');
            }
            $roles = $this->user->getRoleNames()->values()->all();
        }

        if ($roles === [] && isset($meta['actor_roles']) && is_array($meta['actor_roles'])) {
            $roles = array_values(array_filter($meta['actor_roles'], 'is_string'));
        }

        $diff = AuditEventClassifier::diffPayload($meta);
        $resource = AuditEventClassifier::resourceFor($event, $meta);
        $category = AuditEventClassifier::categoryFor($event);
        $severity = AuditEventClassifier::severityFor($event);

        $safeMeta = $meta;
        unset($safeMeta['password'], $safeMeta['token'], $safeMeta['otp']);

        return [
            'uuid' => $this->uuid,
            'event' => $event,
            'category' => $category,
            'category_label' => AuditEventClassifier::categoryLabels()[$category] ?? 'Other',
            'severity' => $severity,
            'badge_tone' => AuditEventClassifier::badgeTone($event),
            'actor_name' => $this->actor_name
                ?: ($meta['attempted_email'] ?? $meta['email'] ?? $meta['actor_email'] ?? null),
            'actor_roles' => $roles,
            'actor_role_label' => $this->formatRoleLabel($roles),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'client_label' => $this->clientLabel((string) ($this->user_agent ?? '')),
            'resource' => $resource,
            'request_url' => $meta['request_url'] ?? null,
            'session_id' => $meta['session_id'] ?? null,
            'old_values' => $diff['old'],
            'new_values' => $diff['new'],
            'has_diff' => $diff['old'] !== null || $diff['new'] !== null || $safeMeta !== [],
            'meta' => $safeMeta,
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'uuid' => $this->user?->uuid,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),
        ];
    }

    /**
     * @param  list<string>  $roles
     */
    private function formatRoleLabel(array $roles): ?string
    {
        if ($roles === []) {
            return null;
        }

        $map = [
            'admin' => 'System Administrator',
            'building_official' => 'Building Official',
            'evaluator' => 'Technical Evaluator',
            'inspector' => 'Inspector',
            'assessor' => 'Fee Assessor',
            'compliance' => 'Compliance Officer',
            'records' => 'Records Officer',
            'receiving' => 'Receiving Clerk',
            'staff' => 'OCBO Staff',
            'applicant' => 'Applicant',
        ];

        $labels = array_map(
            static fn (string $role): string => $map[$role] ?? str_replace('_', ' ', ucwords($role, '_')),
            $roles,
        );

        return implode(' · ', $labels);
    }

    private function clientLabel(string $ua): string
    {
        if ($ua === '') {
            return 'Unknown client';
        }

        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'Chrome/') && ! str_contains($ua, 'Edg/') => 'Chrome',
            str_contains($ua, 'Safari/') && ! str_contains($ua, 'Chrome/') => 'Safari',
            str_contains($ua, 'Firefox/') => 'Firefox',
            default => 'Browser',
        };

        $device = match (true) {
            str_contains($ua, 'iPhone') => 'iPhone',
            str_contains($ua, 'iPad') => 'iPad',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Macintosh') => 'Mac',
            str_contains($ua, 'Windows') => 'Windows',
            default => 'Desktop',
        };

        return $browser.' · '.$device;
    }
}
