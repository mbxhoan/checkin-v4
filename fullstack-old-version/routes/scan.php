<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Scan\ScanController;
use App\Http\Controllers\Scan\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('scan')->group(function () {
    // Auth routes
    Route::get('/login', [AuthController::class, 'viewLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected routes
    Route::get('/index', [ScanController::class, 'index'])->name('index');
    Route::get('/scan/{event}', [ScanController::class, 'scan'])->name('scan');
    Route::post('/scan/{event}/layout', [ScanController::class, 'saveLayout'])->name('layout.save');
    // Route::get('/offline/{event}', [ScanController::class, 'offline'])->name('offline');
    Route::post('/checkin', [ScanController::class, 'checkin'])->name('checkin');
    Route::post('/sync-offline', [ScanController::class, 'syncOffline'])->name('sync-offline');
    Route::get('/render-label/{label}', [ScanController::class, 'renderLabel'])->name('render-label');
});
