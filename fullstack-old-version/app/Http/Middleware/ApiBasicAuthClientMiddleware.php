<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiBasicAuthClientMiddleware
{
    use ApiResponser;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = "apiclient";
        $password = "C8@lVL&N3zs91";

        if ($request->getUser() !== $username || $request->getPassword() !== $password) {
            return $this->responseError('Unauthorized', 401);
        }

        return $next($request);
    }
}
