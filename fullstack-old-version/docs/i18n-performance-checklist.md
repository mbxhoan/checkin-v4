# Bilingual (EN/VI) Performance Checklist

Use this checklist before deploying bilingual updates to production.

## 1) Runtime config

- Set `LOCALE` in `.env` (`vi` or `en`).
- Keep `fallback_locale` stable (recommended: `vi` for this project).
- Ensure active language rows in `languages` table match supported app locales.

## 2) App cache warm-up

Run:

```bash
composer run optimize:production
```

Equivalent artisan commands:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 3) Frontend assets

Build assets in production mode:

```bash
npm ci
npm run build
```

## 4) Queue and workers

- Restart queue workers after deploy:

```bash
php artisan queue:restart
```

- Verify workers are online (Horizon/Supervisor/systemd).

## 5) PHP OPcache (recommended)

Suggested baseline for PHP-FPM:

- `opcache.enable=1`
- `opcache.memory_consumption=256`
- `opcache.interned_strings_buffer=16`
- `opcache.max_accelerated_files=30000`
- `opcache.validate_timestamps=0` (production with controlled deploy)
- `opcache.revalidate_freq=0`

Reload PHP-FPM after changes.

## 6) Smoke checks

- Switch locale between `vi` and `en` on login/register and campaign pages.
- Confirm toast titles display correctly (`Success`/`Thành công`, `Error`/`Lỗi`).
- Validate campaign setup/sending still works for both locales.
- Verify no hardcoded Vietnamese appears in updated auth/campaign flows when locale is `en`.
