# Queue Worker Ops (Card Generation)

## 1) App-level optimizations

- Card generation jobs are dispatched per-client to reduce memory spikes.
- Heavy jobs (`GenerateCard`, `GenerateImageQrcode`) can run on dedicated queue `cards`.
- Configure queue name via env:

```dotenv
QUEUE_CARDS=cards
```

If `QUEUE_CARDS` is not set, jobs stay on `default` queue.

## 2) Recommended worker management (no manual restart)

Use Supervisor (or systemd) to keep workers alive automatically.

### Option A: Horizon (recommended if Redis queue is used)

```ini
[program:checkin-horizon]
directory=/var/www/checkin.test.delfi.vn
command=php artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/checkin-horizon.log
stopwaitsecs=3600
```

### Option B: queue:work without Horizon

```ini
[program:checkin-queue-default]
directory=/var/www/checkin.test.delfi.vn
command=php artisan queue:work redis --queue=default --sleep=1 --tries=3 --timeout=120 --memory=256 --max-jobs=200 --max-time=3600
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/checkin-queue-default.log

[program:checkin-queue-cards]
directory=/var/www/checkin.test.delfi.vn
command=php artisan queue:work redis --queue=cards --sleep=1 --tries=2 --timeout=180 --memory=512 --max-jobs=100 --max-time=1800
autostart=true
autorestart=true
numprocs=1
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/checkin-queue-cards.log
```

## 3) Deploy note

After deploy, run:

```bash
php artisan queue:restart
```

This asks workers to gracefully reload new code/config.
