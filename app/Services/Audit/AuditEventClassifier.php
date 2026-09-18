<?php

declare(strict_types=1);

namespace App\Services\Audit;

/**
 * Derives human-readable resource / category / severity from audit events.
 */
final class AuditEventClassifier
{
    public const CATEGORY_AUTHENTICATION = 'authentication';

    public const CATEGORY_PERMIT_ROUTING = 'permit_routing';

    public const CATEGORY_FEE_OVERRIDES = 'fee_overrides';

    public const CATEGORY_SYSTEM_CONFIG = 'system_config';

    public const CATEGORY_COMPLIANCE = 'compliance';

    public const CATEGORY_RECORDS = 'records';

    public const CATEGORY_REGISTRATION = 'registration';

    public const CATEGORY_OTHER = 'other';

    /**
     * @return array<string, string>
     */
    public static function categoryLabels(): array
    {
        return [
            self::CATEGORY_AUTHENTICATION => 'Authentication',
            self::CATEGORY_PERMIT_ROUTING => 'Permit Routing',
            self::CATEGORY_FEE_OVERRIDES => 'Fee Overrides',
            self::CATEGORY_SYSTEM_CONFIG => 'System Config',
            self::CATEGORY_COMPLIANCE => 'Compliance',
            self::CATEGORY_RECORDS => 'Records',
            self::CATEGORY_REGISTRATION => 'Registration',
            self::CATEGORY_OTHER => 'Other',
        ];
    }

    public static function categoryFor(string $event): string
    {
        $event = strtolower($event);

        if (str_starts_with($event, 'auth.')) {
            return self::CATEGORY_AUTHENTICATION;
        }
        if (str_starts_with($event, 'registration.')) {
            return self::CATEGORY_REGISTRATION;
        }
        if (str_starts_with($event, 'compliance_') || str_contains($event, 'compliance')) {
            return self::CATEGORY_COMPLIANCE;
        }
        if (str_starts_with($event, 'logbook_') || str_starts_with($event, 'archive_')) {
            return self::CATEGORY_RECORDS;
        }
        if (
            str_starts_with($event, 'fee_rule.')
            || str_starts_with($event, 'order_of_payment.')
            || str_contains($event, 'fee_override')
        ) {
            return self::CATEGORY_FEE_OVERRIDES;
        }
        if (
            str_starts_with($event, 'department.')
            || str_starts_with($event, 'form_')
            || str_starts_with($event, 'classification_rule.')
            || str_starts_with($event, 'routing_template.')
            || str_starts_with($event, 'notification_template.')
            || str_starts_with($event, 'user.')
        ) {
            return self::CATEGORY_SYSTEM_CONFIG;
        }
        if (
            str_starts_with($event, 'permit_application.')
            || str_starts_with($event, 'evaluation.')
            || str_starts_with($event, 'evaluation_time.')
            || str_starts_with($event, 'classification')
            || str_starts_with($event, 'routing_')
            || str_starts_with($event, 'inspection.')
        ) {
            return self::CATEGORY_PERMIT_ROUTING;
        }

        return self::CATEGORY_OTHER;
    }

    /**
     * @return 'success'|'warning'|'failure'
     */
    public static function severityFor(string $event): string
    {
        $event = strtolower($event);

        if (
            str_contains($event, 'failed')
            || str_contains($event, 'failure')
            || str_contains($event, 'denied')
            || str_ends_with($event, '.login_failed')
        ) {
            return 'failure';
        }

        if (
            str_contains($event, 'declined')
            || str_contains($event, 'override')
            || str_contains($event, 'deleted')
            || str_contains($event, 'rejected')
        ) {
            return 'warning';
        }

        return 'success';
    }

    /**
     * Badge tone for UI: auth | create | modify | danger.
     */
    public static function badgeTone(string $event): string
    {
        $event = strtolower($event);
        $severity = self::severityFor($event);

        if ($severity === 'failure' || str_contains($event, 'deleted') || str_contains($event, 'override')) {
            return 'danger';
        }
        if (str_starts_with($event, 'auth.')) {
            return 'auth';
        }
        if (
            str_contains($event, '.created')
            || str_contains($event, '.issued')
            || str_contains($event, '.submitted')
            || str_contains($event, '.approved')
            || str_contains($event, '.scheduled')
            || str_contains($event, '.paid')
        ) {
            return 'create';
        }
        if (
            str_contains($event, '.updated')
            || str_contains($event, '.changed')
            || str_contains($event, '.decided')
            || str_contains($event, '.completed')
            || str_contains($event, '.classified')
        ) {
            return 'modify';
        }

        return 'auth';
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array{type: string, label: string, href: string|null}
     */
    public static function resourceFor(string $event, ?array $meta): array
    {
        $meta = $meta ?? [];

        if (! empty($meta['application_no']) && is_scalar($meta['application_no'])) {
            $no = (string) $meta['application_no'];
            $uuid = isset($meta['application_uuid']) && is_scalar($meta['application_uuid'])
                ? (string) $meta['application_uuid']
                : null;

            return [
                'type' => 'Application',
                'label' => $no,
                'href' => $uuid ? '/admin/evaluation-queue?app='.$uuid : null,
            ];
        }

        if (! empty($meta['application_uuid']) && is_scalar($meta['application_uuid'])) {
            $uuid = (string) $meta['application_uuid'];

            return [
                'type' => 'Application',
                'label' => $uuid,
                'href' => '/admin/evaluation-queue?app='.$uuid,
            ];
        }

        if (! empty($meta['entry_no']) && is_scalar($meta['entry_no'])) {
            return [
                'type' => 'Logbook',
                'label' => (string) $meta['entry_no'],
                'href' => '/admin/logbooks',
            ];
        }

        if (! empty($meta['archive_no']) && is_scalar($meta['archive_no'])) {
            return [
                'type' => 'Archive',
                'label' => (string) $meta['archive_no'],
                'href' => '/admin/archives',
            ];
        }

        if (! empty($meta['notice_no']) && is_scalar($meta['notice_no'])) {
            return [
                'type' => 'Notice',
                'label' => (string) $meta['notice_no'],
                'href' => '/admin/compliance-notices',
            ];
        }

        if (! empty($meta['oop_no']) && is_scalar($meta['oop_no'])) {
            return [
                'type' => 'Order of Payment',
                'label' => (string) $meta['oop_no'],
                'href' => '/admin/orders-of-payment',
            ];
        }

        if (str_starts_with($event, 'fee_rule.') && ! empty($meta['code'])) {
            return [
                'type' => 'Config',
                'label' => 'Fee Rules · '.(string) $meta['code'],
                'href' => '/admin/fee-rules',
            ];
        }

        if (str_starts_with($event, 'classification_rule.') && ! empty($meta['code'])) {
            return [
                'type' => 'Config',
                'label' => 'Classifier · '.(string) $meta['code'],
                'href' => '/admin/classification-rules',
            ];
        }

        if (str_starts_with($event, 'routing_template.') && ! empty($meta['code'])) {
            return [
                'type' => 'Config',
                'label' => 'Routing · '.(string) $meta['code'],
                'href' => '/admin/routing-templates',
            ];
        }

        if (str_starts_with($event, 'department.') && ! empty($meta['code'])) {
            return [
                'type' => 'Department',
                'label' => (string) ($meta['name'] ?? $meta['code']),
                'href' => '/admin/departments',
            ];
        }

        if (
            (str_starts_with($event, 'registration.') || str_starts_with($event, 'user.') || str_starts_with($event, 'auth.'))
            && (! empty($meta['email']) || ! empty($meta['attempted_email']) || ! empty($meta['actor_email']))
        ) {
            $email = (string) ($meta['email'] ?? $meta['attempted_email'] ?? $meta['actor_email']);

            return [
                'type' => 'User',
                'label' => $email,
                'href' => str_starts_with($event, 'registration.') ? '/admin/registrations' : '/admin/users',
            ];
        }

        if (! empty($meta['code']) && is_scalar($meta['code'])) {
            return [
                'type' => 'Record',
                'label' => (string) $meta['code'],
                'href' => null,
            ];
        }

        if (! empty($meta['name']) && is_scalar($meta['name'])) {
            return [
                'type' => 'Record',
                'label' => (string) $meta['name'],
                'href' => null,
            ];
        }

        return [
            'type' => 'System',
            'label' => self::categoryLabels()[self::categoryFor($event)] ?? 'Event',
            'href' => null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array{old: array<string, mixed>|null, new: array<string, mixed>|null}
     */
    public static function diffPayload(?array $meta): array
    {
        $meta = $meta ?? [];

        $old = $meta['old_values'] ?? $meta['before'] ?? $meta['old'] ?? null;
        $new = $meta['new_values'] ?? $meta['after'] ?? $meta['new'] ?? $meta['changes'] ?? null;

        return [
            'old' => is_array($old) ? $old : null,
            'new' => is_array($new) ? $new : null,
        ];
    }
}
