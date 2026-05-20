<?php

namespace App\Http\Controllers;

use App\Services\Web\HomeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    public function __construct(HomeService $service)
    {
        $this->service = $service;
    }

    public function home()
    {
        return view('web.home');
    }

    public function changeLanguage(Request $request, $lang)
    {
        $session = $request->session();
        $locale = $this->service->language()->changeLanguage($session, $lang);

        if ($request->expectsJson() || $request->ajax()) {
            return response($locale, Response::HTTP_OK);
        }

        $previousUrl = url()->previous();
        if (! empty($previousUrl)) {
            return redirect()->to($this->stripLocaleQueryParam($previousUrl));
        }

        return redirect()->route('home');
    }

    public function getPlaceholderQrcode()
    {
        $info = config('info.placeholders');
        $path = $info['qrcode'];

        if (file_exists($path)) {
            return response()->file($path);
        }

        return redirect()->route('web.home')->withErrors('Không tìm thấy thông tin');
    }

    private function stripLocaleQueryParam(string $url): string
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $queryParams = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $queryParams);
            unset($queryParams['locale']);
        }

        $rebuilt = '';

        if (! empty($parts['scheme']) && ! empty($parts['host'])) {
            $rebuilt .= $parts['scheme'] . '://' . $parts['host'];
            if (! empty($parts['port'])) {
                $rebuilt .= ':' . $parts['port'];
            }
        }

        $rebuilt .= $parts['path'] ?? '/';

        $queryString = http_build_query($queryParams);
        if ($queryString !== '') {
            $rebuilt .= '?' . $queryString;
        }

        if (! empty($parts['fragment'])) {
            $rebuilt .= '#' . $parts['fragment'];
        }

        return $rebuilt;
    }
}
