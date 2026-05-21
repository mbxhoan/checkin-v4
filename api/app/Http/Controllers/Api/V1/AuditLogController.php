<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuditLogQueryRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Models\Company;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permission:audit-logs.view')->only(['index', 'companyLogs']);
    }

    public function index(AuditLogQueryRequest $request): JsonResponse
    {
        abort_if(! $request->user()->isSystemUser(), 403);

        $logs = $this->buildQuery($request)->paginate((int) ($request->validated('per_page') ?: 15));

        return ApiResponse::paginated($logs, AuditLogResource::collection($logs), 'Audit logs retrieved.');
    }

    public function companyLogs(AuditLogQueryRequest $request, Company $company): JsonResponse
    {
        $logs = $this->buildQuery($request, $company->id)->paginate((int) ($request->validated('per_page') ?: 15));

        return ApiResponse::paginated($logs, AuditLogResource::collection($logs), 'Company audit logs retrieved.');
    }

    private function buildQuery(AuditLogQueryRequest $request, ?int $companyId = null)
    {
        $query = AuditLog::query()
            ->with(['user', 'company'])
            ->latest('created_at');

        if ($companyId) {
            $query->where('company_id', $companyId);
        } elseif ($request->validated('company_id')) {
            $query->where('company_id', $request->validated('company_id'));
        }

        if ($request->validated('user_id')) {
            $query->where('user_id', $request->validated('user_id'));
        }

        if ($request->validated('action')) {
            $query->where('action', 'like', '%'.$request->validated('action').'%');
        }

        if ($request->validated('model_type')) {
            $query->where('model_type', $request->validated('model_type'));
        }

        if ($request->validated('from')) {
            $query->where('created_at', '>=', Carbon::parse($request->validated('from'))->startOfDay());
        }

        if ($request->validated('to')) {
            $query->where('created_at', '<=', Carbon::parse($request->validated('to'))->endOfDay());
        }

        return $query;
    }
}
