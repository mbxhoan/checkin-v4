<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTermsAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->shouldAcceptTerms()) {
            return $next($request);
        }

        if ($request->routeIs('terms.accept*')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('auth.messages.terms_confirm_before_continue'),
            ], 403);
        }

        return redirect()
            ->route('terms.accept')
            ->withErrors(__('auth.messages.terms_required_before_continue'));
    }
}
