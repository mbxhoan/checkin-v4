<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.reset_password') }}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6fb;font-family:Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:10px;border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:24px;">
                <h2 style="margin:0 0 12px 0;font-size:20px;line-height:1.4;">
                    {{ __('auth.reset_password') }}
                </h2>
                <p style="margin:0 0 12px 0;line-height:1.6;">
                    {{ __('Hello') }} {{ $user->name ?? $user->email }},<br>
                    {{ __('You requested a password reset for your account.') }}
                </p>
                <p style="margin:0 0 20px 0;line-height:1.6;">
                    {{ __('Click the button below to continue. This link will expire in :minutes minutes.', ['minutes' => $expiresInMinutes]) }}
                </p>
                <p style="margin:0 0 20px 0;">
                    <a href="{{ $resetUrl }}" style="display:inline-block;padding:12px 20px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;">
                        {{ __('auth.reset_password') }}
                    </a>
                </p>
                <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.6;">
                    {{ __('If you did not request this, please ignore this email.') }}<br>
                    {{ $resetUrl }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
