<?php

declare(strict_types=1);

namespace App\Support\Security;

/**
 * Allow only same-origin relative paths for notification / deep-link navigation.
 */
final class SafeInternalPath
{
    /**
     * Accept paths like /applications or /admin/users?tab=1 — reject schemes and //.
     */
    public static function isValid(?string $url): bool
    {
        if ($url === null || $url === '') {
            return true;
        }

        $url = trim($url);
        if ($url === '' || str_starts_with($url, '//')) {
            return false;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url) === 1) {
            return false;
        }

        return preg_match('#^/[A-Za-z0-9/_\-.?=&%]*$#', $url) === 1;
    }

    public static function sanitize(?string $url, string $fallback = '/'): string
    {
        $url = $url !== null ? trim($url) : '';

        return self::isValid($url) && $url !== '' ? $url : $fallback;
    }
}
