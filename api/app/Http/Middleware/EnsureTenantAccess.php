<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeCompany = $request->route('company');
        $company = $routeCompany instanceof Company ? $routeCompany : Company::find($routeCompany);

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        if (! $company) {
            return ApiResponse::notFound('Company not found.');
        }

        if ($user->isSystemUser()) {
            $request->attributes->set('current_company', $company);

            return $next($request);
        }

        if ($user->company_id !== $company->id) {
            return ApiResponse::forbidden('You do not have access to this company.');
        }

        $request->attributes->set('current_company', $company);

        return $next($request);
    }
}
