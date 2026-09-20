<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require Sanctum auth + at least one of the given abilities for government print routes.
 */
final class EnsurePrintAuthorized
{
    /**
     * @param  Closure(Request): Response  $next
     * @param  string  ...$abilities
     */
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        // Prefer the sanctum guard so Bearer tokens win over any sticky default user.
        $user = $request->user('sanctum') ?? $request->user();
        if ($user === null) {
            abort(401, 'Authentication required to open official prints.');
        }

        if ($abilities === []) {
            return $next($request);
        }

        foreach ($abilities as $ability) {
            if ($user->can($ability)) {
                return $next($request);
            }
        }

        abort(403, 'You are not authorized to open this print.');
    }
}
