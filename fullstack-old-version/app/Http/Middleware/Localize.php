<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Localize
{
    private const ACTIVE_LANGUAGE_CODES_CACHE_KEY = 'languages.active.codes';

    private const ACTIVE_LANGUAGE_CODES_CACHE_TTL_SECONDS = 600;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session();
        $sessionId = $session->getId();

        $locale = strtolower(
            $session->get("{$sessionId}.language")
                ?: config('app.locale')
        );

        $supportedLocales = $this->getSupportedLocales();

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        if ($session->get("{$sessionId}.language") !== $locale) {
            $session->put("{$sessionId}.language", $locale);
        }

        return $next($request);
    }

    private function getSupportedLocales(): array
    {
        $configLocales = array_map('strtolower', array_keys(config('languages', [])));

        if (empty($configLocales)) {
            $configLocales = [strtolower((string) config('app.locale', 'en'))];
        }

        try {
            return Cache::remember(
                self::ACTIVE_LANGUAGE_CODES_CACHE_KEY,
                self::ACTIVE_LANGUAGE_CODES_CACHE_TTL_SECONDS,
                function () use ($configLocales) {
                    $activeCodes = Language::query()
                        ->where('status', Language::STATUS_ACTIVE)
                        ->pluck('code')
                        ->filter()
                        ->map(fn (string $code) => strtolower($code))
                        ->unique()
                        ->values()
                        ->all();

                    return ! empty($activeCodes) ? $activeCodes : $configLocales;
                }
            );
        } catch (Throwable $e) {
            return $configLocales;
        }
    }
}
