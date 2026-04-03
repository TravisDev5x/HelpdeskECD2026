<?php

namespace App\Support\Notifications;

use Illuminate\Support\Str;

/**
 * Evita redirecciones abiertas al seguir enlaces guardados en notificaciones (data.url).
 */
final class SafeInternalNotificationUrl
{
    public static function redirectTarget(?string $url, string $fallbackRoute = 'admin.notifications.index'): string
    {
        $fallback = route($fallbackRoute);

        if ($url === null || trim($url) === '') {
            return $fallback;
        }

        $url = trim($url);

        if (Str::startsWith($url, '//')) {
            return $fallback;
        }

        if (Str::startsWith($url, '/') && ! Str::startsWith($url, '//')) {
            $path = parse_url($url, PHP_URL_PATH) ?? '';
            if ($path === '' || ! str_starts_with($path, '/')) {
                return $fallback;
            }
            $decoded = rawurldecode($path);
            if (str_contains($decoded, '..')) {
                return $fallback;
            }

            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false || ! is_array($parts)) {
            return $fallback;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if ($scheme !== '' && ! in_array($scheme, ['http', 'https'], true)) {
            return $fallback;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $targetHost = $parts['host'] ?? '';
        if ($targetHost === '' || $appHost === null || strcasecmp($targetHost, (string) $appHost) !== 0) {
            return $fallback;
        }

        return $url;
    }
}
