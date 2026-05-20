<?php

namespace App\Providers;

use App\Models\Media;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to your application's "home" route.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        parent::boot();

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Route::model('medium', Media::class);
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        Route::domain(config('app.scan_domain'))
            ->middleware('web')
            ->as('scan.')
            ->group(base_path('routes/scan.php'));

        $this->mapApiRoutes();
        $this->mapAuthRoutes();
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        // Route::domain(config('app.frontend_domain'))
        //     ->middleware('web')
        //     ->group(base_path('routes/web.php'));

    }

    protected function mapWebScanRoutes(): void
    {
        // Đã chuyển lên trên
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "admin" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapAdminRoutes(): void
    {
        Route::prefix('admin')
            ->middleware([
                'web',
                'auth',
                // 'role:admin', 
                'verified',
                'terms.accepted',
            ])
            ->as('admin.')
            ->group(base_path('routes/admin.php'));
    }

    /**
     * Define the "auth" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapAuthRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/auth.php'));
    }
}
