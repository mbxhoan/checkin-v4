<?php

namespace App\Http\Middleware;

use App\Models\Event;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEventAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeEvent = $request->route('event');

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        $event = $routeEvent instanceof Event ? $routeEvent : Event::find($routeEvent);
        if (! $event) {
            return ApiResponse::notFound('Event not found.');
        }

        if ($user->isSystemUser()) {
            $request->attributes->set('current_event', $event);

            return $next($request);
        }

        if ($user->company_id !== $event->company_id) {
            return ApiResponse::forbidden('You do not have access to this event.');
        }

        if ($user->hasRole('company_admin')) {
            $request->attributes->set('current_event', $event);

            return $next($request);
        }

        $isAssigned = $event->users()->where('user_id', $user->id)->exists();
        if (! $isAssigned) {
            return ApiResponse::forbidden('You are not assigned to this event.');
        }

        $request->attributes->set('current_event', $event);

        return $next($request);
    }
}
