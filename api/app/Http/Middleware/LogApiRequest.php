<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    private const SENSITIVE_FIELDS = ['password', 'current_password', 'new_password', 'password_confirmation', 'pin', 'token', 'secret'];

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'company_id' => $request->user()?->company_id,
        ];

        // Log request body without sensitive fields
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $body = $request->except(self::SENSITIVE_FIELDS);
            $logData['body'] = $body;
        }

        Log::channel('daily')->info('API Request', $logData);

        return $response;
    }
}
