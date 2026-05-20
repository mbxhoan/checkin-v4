<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // Keep Laravel broker logic (token/throttle) and override only the delivery channel.
            $status = Password::sendResetLink(
                $request->only('email'),
                function (CanResetPassword $user, string $token): void {
                    $this->sendResetLinkViaPostmark($user, $token);
                }
            );
        } catch (\Throwable $exception) {
            Log::error('Forgot password send failed', [
                'email' => $request->input('email'),
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('auth.reset_link_failed')]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }

    private function sendResetLinkViaPostmark(CanResetPassword $user, string $token): void
    {
        $postmarkToken = (string) config('services.postmark.token');
        $fromAddress = (string) config('mail.from.address');
        $fromName = (string) config('mail.from.name', config('app.name'));

        if ($postmarkToken === '' || $fromAddress === '') {
            throw new \RuntimeException('Postmark config missing (token/from address).');
        }

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]);

        $apiUrl = rtrim((string) env('POSTMARK_API_URL', 'https://api.postmarkapp.com'), '/');
        $subject = __('auth.reset_password') . ' - ' . config('app.name');
        $htmlBody = view('auth.emails.password-reset-postmark', [
            'user' => $user,
            'resetUrl' => $resetUrl,
            'expiresInMinutes' => (int) config('auth.passwords.users.expire', 60),
        ])->render();

        $from = $fromName !== '' ? "{$fromName} <{$fromAddress}>" : $fromAddress;

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Postmark-Server-Token' => $postmarkToken,
        ])->post("{$apiUrl}/email", [
            'From' => $from,
            'To' => $user->getEmailForPasswordReset(),
            'Subject' => $subject,
            'HtmlBody' => $htmlBody,
            'TextBody' => __('auth.password_reset_email_text', ['url' => $resetUrl]),
            'MessageStream' => env('POSTMARK_MESSAGE_STREAM', 'outbound'),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Postmark request failed: {$response->status()} {$response->body()}");
        }
    }
}
