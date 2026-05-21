<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCompanyRequest;
use App\Http\Requests\Api\V1\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService
    ) {
        $this->middleware('check.permission:companies.view')->only(['index', 'show']);
        $this->middleware('check.permission:companies.create')->only('store');
        $this->middleware('check.permission:companies.update')->only('update');
        $this->middleware('check.permission:companies.delete')->only('destroy');
        $this->middleware(['throttle:api-write', 'log.api'])->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $companies = $this->companyService->paginate($request->only(['search', 'status', 'per_page']));

        return ApiResponse::paginated($companies, CompanyResource::collection($companies), 'Companies retrieved.');
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->companyService->create($request->validated(), $request->user(), $request);

        return ApiResponse::success(new CompanyResource($company), 'Company created successfully.', 201);
    }

    public function show(Company $company): JsonResponse
    {
        $company->loadCount(['users', 'events']);

        return ApiResponse::success(new CompanyResource($company), 'Company retrieved.');
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $company = $this->companyService->update($company, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new CompanyResource($company), 'Company updated successfully.');
    }

    public function destroy(Request $request, Company $company): JsonResponse
    {
        $this->companyService->delete($company, $request->user(), $request);

        return ApiResponse::success(null, 'Company deleted successfully.');
    }
}
