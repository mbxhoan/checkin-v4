<?php

use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CheckinController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\LegacyAuthController;
use App\Http\Controllers\Api\V1\LegacyCheckinController;
use App\Http\Controllers\Api\V1\LegacyClientController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RolePermissionController;
use App\Http\Controllers\Api\V1\ScannerAuthController;
use App\Http\Controllers\Api\V1\ScannerController;
use App\Http\Controllers\Api\V1\SystemUserController;
use App\Http\Controllers\Api\V1\UserController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Health check
    Route::get('/health', fn () => ApiResponse::success([
        'status' => 'ok',
        'version' => 'v1',
        'timestamp' => now()->toISOString(),
    ], 'Delfi Checkin API is running.'))->name('api.health');

    // ──────────────────────────────────────
    // Public Auth Routes (no auth required)
    // ──────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware(['throttle:auth', 'log.api'])
            ->name('api.auth.login');
    });

    Route::prefix('scanner')->group(function () {
        Route::post('/login', [ScannerAuthController::class, 'login'])
            ->middleware(['throttle:scanner-auth', 'log.api'])
            ->name('api.scanner.login');
    });

    // Legacy core shim routes (public subset)
    Route::post('/authenticate', [LegacyAuthController::class, 'authenticate'])->name('api.legacy.authenticate');
    Route::prefix('clients')->group(function () {
        Route::get('/find', [LegacyClientController::class, 'find'])
            ->middleware('throttle:api')
            ->name('api.legacy.clients.find');
        Route::post('/register', [LegacyClientController::class, 'register'])
            ->middleware(['throttle:api-write', 'log.api'])
            ->name('api.legacy.clients.register');
    });

    // ──────────────────────────────────────
    // Authenticated Routes
    // ──────────────────────────────────────
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        // Auth actions (authenticated)
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
            Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
            Route::put('/change-password', [AuthController::class, 'changePassword'])->name('api.auth.change-password');
        });

        // Scanner actions (authenticated)
        Route::prefix('scanner')->group(function () {
            Route::post('/logout', [ScannerAuthController::class, 'logout'])->name('api.scanner.logout');
            Route::get('/events', [ScannerAuthController::class, 'events'])->name('api.scanner.events');
        });

        // Profile
        Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');

        // Roles & Permissions (read-only)
        Route::get('/roles', [RolePermissionController::class, 'roles'])->name('api.roles.index');
        Route::get('/permissions', [RolePermissionController::class, 'permissions'])->name('api.permissions.index');

        // ──────────────────────────────────────
        // Company Management (system-level)
        // ──────────────────────────────────────
        Route::apiResource('companies', CompanyController::class)->names('api.companies');

        // System User Management
        Route::prefix('system')->group(function () {
            Route::apiResource('users', SystemUserController::class)->names('api.system.users');
        });

        // System-level Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('api.audit-logs.index');

        // Legacy core shim routes (protected subset)
        Route::post('/checkin', [LegacyCheckinController::class, 'checkin'])->name('api.legacy.checkin');
        Route::post('/multi-checkin', [LegacyCheckinController::class, 'multiCheckin'])->name('api.legacy.multi-checkin');
        Route::prefix('clients')->group(function () {
            Route::get('/qrcode', [LegacyClientController::class, 'findByQrcode'])->name('api.legacy.clients.qrcode');
            Route::get('/id/{id}', [LegacyClientController::class, 'findById'])->name('api.legacy.clients.id');
            Route::post('/upsert', [LegacyClientController::class, 'upsert'])
                ->middleware(['throttle:api-write', 'log.api'])
                ->name('api.legacy.clients.upsert');
            Route::post('/upsert-by-id', [LegacyClientController::class, 'upsertById'])
                ->middleware(['throttle:api-write', 'log.api'])
                ->name('api.legacy.clients.upsert-by-id');
        });

        // ──────────────────────────────────────
        // Company-scoped Routes
        // ──────────────────────────────────────
        Route::prefix('companies/{company}')->middleware(['tenant.access'])->group(function () {

            // Company Users
            Route::apiResource('users', UserController::class)->names('api.company.users');
            Route::post('/users/{user}/roles', [UserController::class, 'assignRole'])->name('api.company.users.assign-role');
            Route::delete('/users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('api.company.users.remove-role');

            // Company Events
            Route::apiResource('events', EventController::class)->names('api.company.events');

            // Company Scanners
            Route::apiResource('scanners', ScannerController::class)->names('api.company.scanners');

            // Company Audit Logs
            Route::get('/audit-logs', [AuditLogController::class, 'companyLogs'])->name('api.company.audit-logs');
        });

        // ──────────────────────────────────────
        // Event-scoped Routes
        // ──────────────────────────────────────
        Route::scopeBindings()->prefix('events/{event}')->middleware(['event.access'])->group(function () {

            // Clients
            Route::apiResource('clients', ClientController::class)->names('api.event.clients');

            // Check-ins
            Route::post('/checkins/scan', [CheckinController::class, 'scan'])->name('api.event.checkins.scan');
            Route::get('/checkins', [CheckinController::class, 'index'])->name('api.event.checkins.index');
            Route::get('/checkins/stats', [CheckinController::class, 'stats'])->name('api.event.checkins.stats');

            // Reports
            Route::get('/reports/summary', [ReportController::class, 'summary'])->name('api.event.reports.summary');
            Route::get('/reports/checkins', [ReportController::class, 'checkins'])->name('api.event.reports.checkins');
        });
    });
});
