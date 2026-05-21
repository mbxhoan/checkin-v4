<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreScannerRequest;
use App\Http\Requests\Api\V1\UpdateScannerRequest;
use App\Http\Resources\UserResource;
use App\Models\Company;
use App\Models\User;
use App\Services\UserManagementService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService
    ) {
        $this->middleware('check.permission:scanners.view')->only(['index', 'show']);
        $this->middleware('check.permission:scanners.create')->only('store');
        $this->middleware('check.permission:scanners.update')->only('update');
        $this->middleware('check.permission:scanners.delete')->only('destroy');
        $this->middleware(['throttle:api-write', 'log.api'])->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request, Company $company): JsonResponse
    {
        $scanners = $this->userManagementService->paginateCompanyUsers($company, $request->only(['search', 'status', 'per_page']), true);

        return ApiResponse::paginated($scanners, UserResource::collection($scanners), 'Scanners retrieved.');
    }

    public function store(StoreScannerRequest $request, Company $company): JsonResponse
    {
        $scanner = $this->userManagementService->createScanner($company, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new UserResource($scanner), 'Scanner created successfully.', 201);
    }

    public function show(Company $company, User $scanner): JsonResponse
    {
        abort_if($scanner->company_id !== $company->id || ! $scanner->hasRole('scanner'), 404);
        $scanner->load(['roles', 'company']);

        return ApiResponse::success(new UserResource($scanner), 'Scanner retrieved.');
    }

    public function update(UpdateScannerRequest $request, Company $company, User $scanner): JsonResponse
    {
        $scanner = $this->userManagementService->updateScanner($company, $scanner, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new UserResource($scanner), 'Scanner updated successfully.');
    }

    public function destroy(Request $request, Company $company, User $scanner): JsonResponse
    {
        $this->userManagementService->deleteScanner($company, $scanner, $request->user(), $request);

        return ApiResponse::success(null, 'Scanner deleted successfully.');
    }
}
