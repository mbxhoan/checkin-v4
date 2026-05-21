# Check-in V4 API

Backend Laravel cho nền tảng Check-in V4 theo mô hình multi-tenant SaaS, phục vụ quản lý công ty, sự kiện, khách mời, scanner device, check-in/check-out, RBAC và audit log.

README này là tài liệu onboarding nhanh cho dev backend sau khi hoàn tất API foundation và legacy core shim.

## 1. Mục tiêu của service

`/api` hiện là service backend chính cho các nhóm chức năng:

- Authentication bằng Laravel Sanctum.
- RBAC bằng Spatie Permission.
- Multi-tenant theo `company`.
- Event / attendee / scanner management.
- QR check-in và check-out theo `event`.
- Audit log cho auth và mutation quan trọng.
- API docs UI bằng Scribe tại `/docs`.
- Dual support cho:
  - API mới theo namespace `/api/v1/...`
  - Legacy core shim cho một số endpoint cũ để giảm rủi ro đứt client

## 2. Tech stack

- PHP `^8.3`
- Laravel `^13.8`
- Laravel Sanctum
- Spatie Permission
- Spatie Media Library
- Laravel Pint
- PHPUnit 12
- Scribe 5
- SQLite mặc định cho local dev

## 3. Trạng thái hiện tại

API foundation hiện đã có các khối chính sau:

- Auth:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/logout`
  - `GET /api/v1/auth/me`
  - `POST /api/v1/auth/refresh`
  - `PUT /api/v1/auth/change-password`
- Scanner auth:
  - `POST /api/v1/scanner/login`
  - `POST /api/v1/scanner/logout`
  - `GET /api/v1/scanner/events`
- CRUD / domain APIs:
  - `companies`
  - `system/users`
  - `companies/{company}/users`
  - `companies/{company}/events`
  - `companies/{company}/scanners`
  - `events/{event}/clients`
  - `events/{event}/checkins`
  - `events/{event}/reports`
  - `audit-logs`
  - `profile`
  - `roles`
  - `permissions`
- Legacy core shim:
  - `POST /api/v1/authenticate`
  - `POST /api/v1/checkin`
  - `POST /api/v1/multi-checkin`
  - `GET /api/v1/clients/find`
  - `GET /api/v1/clients/qrcode`
  - `GET /api/v1/clients/id/{id}`
  - `POST /api/v1/clients/register`
  - `POST /api/v1/clients/upsert`
  - `POST /api/v1/clients/upsert-by-id`

## 4. Kiến trúc thư mục

```txt
api/
  app/
    Enums/                # enum trạng thái, role, type
    Http/
      Controllers/Api/V1/ # controller cho API mới và shim legacy
      Middleware/         # tenant access, event access, permission, logging
      Requests/Api/V1/    # FormRequest cho API
      Resources/          # response resources
    Models/               # Company, Event, Client, Checkin, AuditLog, User...
    Policies/             # policy layer cho authz
    Services/             # business logic chính
    Support/              # ApiResponse, LegacyApiResponse
  config/
    permission.php
    sanctum.php
    scribe.php
  database/
    migrations/
    seeders/
  public/vendor/scribe/   # Scribe generated assets
  resources/views/scribe/ # Scribe generated Blade docs
  routes/api.php
  tests/Feature/Api/V1/
```

## 5. Dữ liệu nền và mô hình quyền

### Core models

- `companies`
- `users`
- `events`
- `clients`
- `checkins`
- `event_user`
- `audit_logs`

### Role chuẩn

- `system_admin`
- `system_audit`
- `system_support`
- `company_admin`
- `company_manager`
- `company_user`
- `scanner`

### Guardrails đang áp dụng

- User công ty chỉ được thao tác trong `company_id` của mình.
- Event-bound API luôn đi qua `event.access`.
- Scanner chỉ được scan nếu:
  - account active
  - có role `scanner`
  - có access token hợp lệ
  - được gán vào event tương ứng
- Check-in lookup luôn event-scoped, không lookup QR toàn cục.
- Duplicate check-in bị chặn mặc định, trừ khi `events.settings.allow_duplicate === true`.
- Audit log chỉ ghi cho login/logout/refresh/change-password và mutation quan trọng.

## 6. Cài đặt local

### Yêu cầu

- PHP 8.3+
- Composer 2.8+
- Node.js/NPM nếu cần build asset local

### Setup nhanh

```bash
cd api
composer setup
```

Lệnh trên sẽ:

- cài dependency
- tạo `.env` nếu chưa có
- generate `APP_KEY`
- chạy migration
- cài `npm`
- build asset

### Setup thủ công

```bash
cd api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

Nếu dùng SQLite local, đảm bảo file DB tồn tại:

```bash
touch database/database.sqlite
```

## 7. Chạy service

### Chạy đầy đủ local dev

```bash
cd api
composer dev
```

Lệnh này khởi chạy đồng thời:

- `php artisan serve`
- `php artisan queue:listen`
- `php artisan pail`
- `npm run dev`

### Chạy tối thiểu backend

```bash
cd api
php artisan serve
```

Mặc định app chạy tại:

- API: `http://localhost:8000`
- Docs UI: `http://localhost:8000/docs`

## 8. Seed credentials cho local dev

Sau khi chạy `php artisan migrate:fresh --seed`, có thể dùng các tài khoản sau:

### System users

| Role | Credential |
|---|---|
| System Admin | `sysadmin@delfi.vn` / `password` |
| System Audit | `audit@delfi.vn` / `password` |
| System Support | `support@delfi.vn` / `password` |

### Company users

| Role | Credential |
|---|---|
| Company Admin | `admin@company1.vn` / `password` |
| Company Manager | `manager1@company1.vn` / `password` |
| Company User | `user1@company1.vn` / `password` |

### Scanner device

| Type | Credential |
|---|---|
| Scanner | `device_code=SCAN101`, `pin=1234` |

## 9. API docs

Scribe được cấu hình sẵn.

### Generate docs

```bash
cd api
php artisan scribe:generate
```

### Truy cập docs

- HTML docs: `http://localhost:8000/docs`
- Postman collection: được generate trong `storage/app/private/scribe/`
- OpenAPI spec: được generate trong `storage/app/private/scribe/`

## 10. Response contract

### API mới

Chuẩn response chính:

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```

### Legacy core shim

Shim legacy giữ envelope cũ:

```json
{
  "status": "success",
  "status_code": 200,
  "message": "OK",
  "data": {}
}
```

Lưu ý:

- Shim legacy hiện chỉ đảm bảo transport compatibility và field cốt lõi.
- Không tái tạo toàn bộ special-case behavior từ `fullstack-old-version`.

## 11. Middleware và rate limit

### Middleware chính

- `ForceJson`
- `SecurityHeaders`
- `EnsureTenantAccess`
- `EnsureEventAccess`
- `CheckPermission`
- `LogApiRequest`

### Rate limiter

- `auth`: login thông thường
- `scanner-auth`: scanner login
- `api`: protected routes chung
- `api-write`: mutation routes

## 12. Verify trước khi merge

### Static / runtime checks

```bash
cd api
find app routes database -name '*.php' -print0 | xargs -0 -n1 php -l
php artisan route:list --path=api/v1
php artisan migrate:fresh --seed
php artisan scribe:generate
./vendor/bin/pint --test
php artisan test
```

### Lệnh hữu ích khác

```bash
cd api
php artisan about
php artisan config:clear
php artisan cache:clear
php artisan queue:work --once
```

## 13. Những việc chưa nằm trong scope README này

README này tập trung vào API foundation hiện có. Các phần sau hoặc chưa triển khai, hoặc chỉ tồn tại ở monolith cũ dùng làm tham chiếu:

- landing page đầy đủ
- email campaign orchestration đầy đủ
- payment/ticketing flow
- import/export/report jobs quy mô lớn
- custom field template engine đầy đủ như bản legacy

## 14. Lưu ý cho dev tiếp theo

- Không đổi API contract mới hoặc legacy shim âm thầm.
- Nếu sửa route/response/schema/permission:
  - cập nhật docs liên quan
  - cập nhật `docs/commit_prompt_map.md`
  - chạy lại `scribe:generate`
- Không bỏ tenant boundary hoặc event boundary.
- Không thêm logic business nặng vào controller; ưu tiên `Services`.
- Không dùng shim legacy cho feature mới; feature mới phải bám route mới `/api/v1/...`.

## 15. Tài liệu liên quan

- [Workspace rules](../rules/00-core.md)
- [API rules](../rules/02-api.md)
- [Security and data rules](../rules/04-security-and-data.md)
- [Testing and done format](../rules/05-testing-and-done.md)
- [Implementation plan](../docs/implementation_plan.md)
- [Commit prompt map](../docs/commit_prompt_map.md)
