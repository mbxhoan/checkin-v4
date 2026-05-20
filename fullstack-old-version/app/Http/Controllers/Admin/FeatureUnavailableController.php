<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureUnavailableController extends Controller
{
    /**
     * Show an informational page for features that aren't included in the current package.
     */
    public function __invoke(Request $request): View
    {
        $featureKey = (string) $request->query('feature', '');
        $label = (string) $request->query('label', $featureKey ?: 'Tính năng');
        $sub = (string) $request->query('sub', '');

        return view('admin.feature-unavailable', [
            'featureKey' => $featureKey,
            'label' => $label,
            'sub' => $sub,
        ]);
    }
}

