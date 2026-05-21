<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        foreach ($permissions as $permission) {
            if (! $user->tokenCan('*') && ! $user->tokenCan($permission)) {
                return ApiResponse::forbidden('Your token does not grant access to this action.');
            }

            if (! $user->hasPermissionTo($permission)) {
                return ApiResponse::forbidden("You do not have the required permission: {$permission}");
            }
        }

        return $next($request);
    }
}
