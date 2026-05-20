<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequestsByIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = "rate_limit:$ip";
        $maxAttempts = 120;  // 120 request
        $decayMinutes = 1;  // mỗi phút

        if (cache()->has($key) && cache()->get($key) >= $maxAttempts) {
            // abort(429, 'Too Many Requests');
        }

        cache()->add($key, 0, $decayMinutes * 60);
        cache()->increment($key);
        return $next($request);
    }
}
