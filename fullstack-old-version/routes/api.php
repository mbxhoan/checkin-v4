<?php

use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\Auth\AuthenticateController;
use App\Http\Controllers\Api\V1\CheckinController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\LandingPageController;
use App\Http\Controllers\Api\V1\LanguageController;
use App\Http\Controllers\Api\V1\LanguageDefineController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\PageAccessLogController;
use App\Http\Controllers\Api\V1\PostCommentController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\PostLikeController;
use App\Http\Controllers\Api\V1\UserCommentController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserPostController;
use App\Http\Controllers\Api\V1\Webhooks\PostmarkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        // // Comments
        // Route::apiResource('comments', CommentController::class)->only('destroy');
        // Route::apiResource('posts.comments', PostCommentController::class)->only('store');

        // // Posts
        // Route::apiResource('posts', PostController::class)->only(['update', 'store', 'destroy']);
        // Route::post('/posts/{post}/likes', [PostLikeController::class, 'store'])->name('posts.likes.store');
        // Route::delete('/posts/{post}/likes', [PostLikeController::class, 'destroy'])->name('posts.likes.destroy');

        // Users
        Route::apiResource('users', UserController::class)->only('update');
        Route::apiResource('users', UserController::class)->only(['index', 'show']);

        // // Media
        // Route::apiResource('media', MediaController::class)->only(['store', 'destroy']);

        /* CHECKIN */
        Route::post('/checkin', [CheckinController::class, 'checkin']);
        Route::post('/multi-checkin', [CheckinController::class, 'multiCheckin']);
    });

    /* LANGUAGES */
    Route::apiResource('languages', LanguageController::class)->only(['index']);

    /* LANGUAGE DEFINES */
    Route::prefix('language_defines')->group(function () {
        Route::get('/{lang}/{file}', [LanguageDefineController::class, 'getLanguageFile']);
    });

    Route::post('/authenticate', [AuthenticateController::class, 'authenticate'])->name('authenticate');

    /* WEBHOOK */
    Route::prefix('webhook')->middleware('api.basic.auth.webhook')->group(function () {
        /* POSTMARK */
        Route::post('/postmark/send', [PostmarkController::class, 'handlePostmarkWebhook'])->name('postmark.webhook');
    });

    /* Assets */
    Route::prefix('assets')->group(function () {
        Route::get('/medias', [AssetController::class, 'getMedias']);
    });

    /* AUTH REGISTER */
    Route::middleware('api.basic.auth.register')->group(function () {
        /* PAGE ACCESS */
        Route::post('/page_access_logs/store', [PageAccessLogController::class, 'store']);

        /* LANDING PAGES */
        Route::prefix('landing_pages')->group(function () {
            Route::get('/slug/{slug}/{lang}', [LandingPageController::class, 'getBySlug']);
        });

        /* EVENTS */
        // Route::apiResource('events', EventController::class)->only(['detail']);

        /* CLIENTS */
        Route::prefix('clients')->group(function () {
            Route::get('/find', [ClientController::class, 'find']);
            Route::get('/generate-qrcode-on-setting/{event}', [ClientController::class, 'generateQrcodeOnSetting']);
            Route::post('/register', [ClientController::class, 'register']);
        });
    });

    Route::middleware('api.basic.auth.client')->group(function () {
        /* CLIENTS */
        Route::prefix('clients')->group(function () {
            Route::get('/qrcode', [ClientController::class, 'findByQrcode']);
            Route::get('/id/{id}', [ClientController::class, 'findById']);
            Route::post('/upsert', [ClientController::class, 'upsert']);
            Route::post('/upsert-by-id', [ClientController::class, 'upsertById']);
        });
    });

    // // Comments
    // Route::apiResource('posts.comments', PostCommentController::class)->only('index');
    // Route::apiResource('users.comments', UserCommentController::class)->only('index');
    // Route::apiResource('comments', CommentController::class)->only(['index', 'show']);

    // // Posts
    // Route::apiResource('posts', PostController::class)->only(['index', 'show']);
    // Route::apiResource('users.posts', UserPostController::class)->only('index');

    // // Users
    // Route::apiResource('users', UserController::class)->only(['index', 'show']);

    // // Media
    // Route::apiResource('media', MediaController::class)->only('index');
});
