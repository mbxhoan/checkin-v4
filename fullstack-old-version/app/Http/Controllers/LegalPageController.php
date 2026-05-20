<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function privacy(): View
    {
        return $this->renderPage('privacy');
    }

    public function paymentRefund(): View
    {
        return $this->renderPage('payment_refund');
    }

    private function renderPage(string $page): View
    {
        $pageConfig = config("legal.pages.$page");
        abort_unless(is_array($pageConfig), 404);

        $locale = app()->getLocale();
        $relativePath = data_get($pageConfig, "content_paths.$locale")
            ?? data_get($pageConfig, 'content_paths.vi')
            ?? data_get($pageConfig, 'content_paths.en');

        $contentPath = is_string($relativePath) ? resource_path($relativePath) : null;
        $content = $contentPath && File::exists($contentPath)
            ? File::get($contentPath)
            : __('legal.content_fallback');

        return view('legal.page', [
            'pageTitle' => __(data_get($pageConfig, 'title_key', '')),
            'pageDescription' => __(data_get($pageConfig, 'description_key', '')),
            'content' => $content,
        ]);
    }
}
