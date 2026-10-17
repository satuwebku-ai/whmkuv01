<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects the public scheduler endpoint with a server-side secret.
 * Configure CRON_SECRET with a long random value and send it using
 * the X-Cron-Secret header from the trusted scheduler.
 */
class VerifyCronSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.cron.secret', '');
        $provided = (string) $request->header('X-Cron-Secret', '');

        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
