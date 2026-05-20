<?php

namespace App\Services\Web;

use App\Models\Language;
use App\Services\BaseService;
use Illuminate\Support\Facades\App;

class LanguageService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Language::class);
    }

    public function changeLanguage($session, $lang, $setLocale = false)
    {
        $sessionId = $session->getId();
        $supportedLocales = array_map('strtolower', array_keys(config('languages', [])));
        $lang = strtolower((string) $lang);

        if (! in_array($lang, $supportedLocales, true)) {
            $lang = strtolower((string) config('app.locale', 'en'));
        }

        $session->put("{$sessionId}.language", $lang);

        if ($setLocale) {
            App::setLocale($lang);
        }

        return $session->get("{$sessionId}.language");
    }
}
