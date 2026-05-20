<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Support\ApiResponse;

Route::prefix('v1')->group(function () {


    Route::get('/health', function () {
        return ApiResponse::success([
            'version' => 'v1',
            'time' => now()->toISOString(),
        ], 'Delfi Checkin API is running.');
    });

    Route::prefix('auth')->group(function () {
        // login/logout/me sau này
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->middleware('auth:sanctum');

        Route::prefix('events')->group(function () {
            // event APIs
        });

        Route::prefix('checkins')->group(function () {
            // scan/checkin APIs
        });
    });
});
