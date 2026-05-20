<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TermsAcceptanceController extends Controller
{
    public function showPublic(): View
    {
        return view('auth.terms-public');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->shouldAcceptTerms()) {
            return redirect()->intended('/');
        }

        return view('auth.terms-accept');
    }

    public function accept(Request $request): RedirectResponse
    {
        $request->validate([
            'accept_terms' => ['accepted'],
        ], [
            'accept_terms.accepted' => __('auth.messages.terms_accept_required'),
        ]);

        $request->user()->update([
            'must_accept_terms' => false,
            'terms_accepted_at' => now(),
        ]);

        return redirect('/')
            ->withSuccess(__('auth.messages.terms_accept_success'));
    }
}
