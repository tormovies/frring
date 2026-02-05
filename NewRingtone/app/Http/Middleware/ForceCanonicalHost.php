<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * На продакшене редирект с www на основной домен (например https://freeringtones.ru без www).
 * В .env задать APP_URL=https://freeringtones.ru
 */
class ForceCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        $url = config('app.url');
        if ($url === '' || $url === null) {
            return $next($request);
        }

        $parsed = parse_url($url);
        $canonicalHost = $parsed['host'] ?? null;
        if ($canonicalHost === null) {
            return $next($request);
        }

        $requestHost = $request->getHost();
        if (strtolower($requestHost) === 'www.' . strtolower($canonicalHost)) {
            $scheme = $parsed['scheme'] ?? 'https';
            $url = $scheme . '://' . $canonicalHost . $request->getRequestUri();
            return redirect()->away($url, 301);
        }

        return $next($request);
    }
}
