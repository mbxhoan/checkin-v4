# Setup Full API Foundation — Auth, RBAC, Multi-tenant SaaS, Seeders & API Docs UI

## Tình trạng hiện tại

Project Laravel 13.8 mới scaffold, chỉ có:
- `User` model (vanilla, chưa có `HasRoles`/`HasApiTokens`)
- Sanctum & Spatie Permission đã install + migration nhưng chưa cấu hình
- Route stubbed (`v1/`) với groups rỗng
- `ApiResponse` helper đã có
- **Không có** domain models, controllers, middleware, seeders, hay API docs

---

## User Review Required

> [!IMPORTANT]
> **Scribe vs L5-Swagger**: Tôi chọn **Scribe** (knuckleswtf/scribe) cho API docs UI vì:
> - Tự động generate từ routes/FormRequests/Resources (không cần annotate thủ công từng endpoint)
> - Có **"Try It Out"** button cho fill param, edit body, gửi thử request
> - Group endpoints tự động, hiển thị auth requirements
> - DX tốt hơn nhiều so với l5-swagger cho Laravel
>
> Nếu bạn muốn Swagger UI truyền thống (OpenAPI spec), tôi sẽ chuyển sang `l5-swagger`.

> [!IMPORTANT]
> **Scanner Auth**: Scanner sẽ là user với role `scanner` (không phải model riêng). Điều này giữ auth system thống nhất và RBAC đơn giản. Scanner login bằng `device_code` + `pin` thay vì email/password.

> [!WARNING]
> **Breaking change**: Migration sẽ thêm `company_id` vào bảng `users`. Tất cả system-level users (system_admin, system_audit, system_support) sẽ có `company_id = null`.

---

## Open Questions

> [!IMPORTANT]
> 1. **Database driver**: Hiện `.env.example` dùng `sqlite`. Bạn muốn giữ SQLite cho dev hay chuyển sang MySQL/PostgreSQL?
> 2. **Sanctum token expiry**: Hiện config `null` (không hết hạn). Bạn muốn set token expiry bao lâu? (ví dụ: 24h, 7 ngày, 30 ngày?)
> 3. **Soft delete**: Có muốn bật soft delete cho tất cả domain models (companies, events, clients) không?
> 4. **Audit log**: Có cần ghi audit log cho mọi mutation hay chỉ cho các action nhạy cảm?

---

## Proposed Changes

### Phase 1: Database Foundation

#### [MODIFY] [User.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Models/User.php)
- Thêm traits: `HasRoles` (Spatie), `HasApiTokens` (Sanctum), `SoftDeletes`
- Thêm fillable: `company_id`, `phone`, `status`, `avatar`, `device_code`, `pin`, `last_login_at`
- Thêm relationships: `company()`, `events()`, `checkins()`, `auditLogs()`
- Thêm scopes: `scopeActive()`, `scopeByCompany()`

#### [NEW] [Company.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Models/Company.php)
```
Schema: companies
- id, name, slug (unique), email, phone, address, logo
- status: enum (active, inactive, suspended)
- settings: json
- max_events, max_users
- subscription_plan, subscription_expires_at
- timestamps, soft_deletes
```

#### [NEW] [Event.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Models/Event.php)
```
Schema: events
- id, company_id (FK), name, code (unique within company)
- description, location, venue
- start_date, end_date
- status: enum (draft, active, completed, cancelled)
- settings: json, max_attendees, timezone
- created_by (FK users)
- timestamps, soft_deletes
Indexes: company_id, code, status, (company_id, code) unique
```

#### [NEW] [Client.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Models/Client.php)
```
Schema: clients
- id, event_id (FK), company_id (FK), name, email, phone
- qrcode (unique within event)
- status: enum (registered, checked_in, checked_out, cancelled)
- custom_fields: json
- registered_at, checked_in_at, checked_out_at
- source: enum (import, landing, manual, api)
- timestamps, soft_deletes
Indexes: event_id, company_id, qrcode, email, (event_id, qrcode) unique
```

#### [NEW] [Checkin.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Models/Checkin.php)
```
Schema: checkins
- id, event_id (FK), client_id (FK)
- scanned_by (FK users), type: enum (check_in, check_out)
- scanned_at (timestamp), device_info, notes
- timestamps
Indexes: event_id, client_id, scanned_by, scanned_at, (event_id, client_id, type) for duplicate check
```

#### [NEW] [EventUser.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Models/EventUser.php)
```
Schema: event_user (pivot)
- id, event_id (FK), user_id (FK)
- role: enum (manager, staff, scanner)
- timestamps
Index: (event_id, user_id) unique
```

#### [NEW] [AuditLog.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Models/AuditLog.php)
```
Schema: audit_logs
- id, user_id (FK nullable), company_id (FK nullable)
- action (string), model_type, model_id
- old_values: json, new_values: json
- ip_address, user_agent
- created_at
Indexes: user_id, company_id, model_type+model_id, created_at
```

#### [NEW] Migrations (7 files)
- `create_companies_table`
- `add_company_fields_to_users_table`
- `create_events_table`
- `create_clients_table`
- `create_checkins_table`
- `create_event_user_table`
- `create_audit_logs_table`

---

### Phase 2: Enums & Support Classes

#### [NEW] `app/Enums/` — Type-safe PHP enums
- `CompanyStatus.php` — active, inactive, suspended
- `EventStatus.php` — draft, active, completed, cancelled
- `ClientStatus.php` — registered, checked_in, checked_out, cancelled
- `ClientSource.php` — import, landing, manual, api
- `CheckinType.php` — check_in, check_out
- `UserStatus.php` — active, inactive, suspended
- `EventUserRole.php` — manager, staff, scanner
- `SystemRole.php` — system_admin, system_audit, system_support, company_admin, company_manager, company_user, scanner

#### [MODIFY] [ApiResponse.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Support/ApiResponse.php)
- Thêm `paginated()` method cho paginated responses
- Thêm `unauthorized()`, `forbidden()`, `notFound()` helper methods

---

### Phase 3: Authentication System

#### [NEW] [AuthController.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Http/Controllers/Api/V1/AuthController.php)
Endpoints:
| Method | URI | Description |
|---|---|---|
| POST | `/api/v1/auth/login` | Email + password login, return Sanctum token |
| POST | `/api/v1/auth/logout` | Revoke current token |
| GET | `/api/v1/auth/me` | Get authenticated user with roles/permissions |
| PUT | `/api/v1/auth/change-password` | Change password |
| POST | `/api/v1/auth/refresh` | Revoke + re-issue token |

#### [NEW] [ScannerAuthController.php](file:///Users/leviackerman/Codes/checkin-v4/api/app/Http/Controllers/Api/V1/ScannerAuthController.php)
Endpoints:
| Method | URI | Description |
|---|---|---|
| POST | `/api/v1/scanner/login` | Device code + PIN login |
| POST | `/api/v1/scanner/logout` | Revoke scanner token |
| GET | `/api/v1/scanner/events` | List assigned events |

#### [NEW] FormRequests:
- `LoginRequest.php` — validate email, password
- `ScannerLoginRequest.php` — validate device_code, pin
- `ChangePasswordRequest.php` — validate current_password, new_password, confirmation

#### [NEW] Auth Services:
- `AuthService.php` — login logic, token generation, password change
- `ScannerAuthService.php` — device auth logic

---

### Phase 4: RBAC — Roles & Permissions

#### Roles (7 levels):

| # | Role | Guard | Scope | Description |
|---|---|---|---|---|
| 1 | `system_admin` | web | System | Quyền cao nhất, quản lý toàn hệ thống |
| 2 | `system_audit` | web | System | Xem audit logs, thống kê hệ thống |
| 3 | `system_support` | web | System | Hỗ trợ công ty, quản lý cơ bản |
| 4 | `company_admin` | web | Company | Quyền cao nhất trong công ty |
| 5 | `company_manager` | web | Company | Quản lý events, clients, campaigns |
| 6 | `company_user` | web | Company | Nhân viên, quyền hạn chế |
| 7 | `scanner` | web | Event | Thiết bị scan, chỉ scan check-in |

#### Permissions (35+ granular):

**System-level:**
- `system.manage`, `system.audit`, `system.support`
- `companies.view`, `companies.create`, `companies.update`, `companies.delete`
- `system-users.view`, `system-users.create`, `system-users.update`, `system-users.delete`

**Company-level:**
- `company.settings.view`, `company.settings.update`
- `users.view`, `users.create`, `users.update`, `users.delete`
- `events.view`, `events.create`, `events.update`, `events.delete`
- `clients.view`, `clients.create`, `clients.update`, `clients.delete`, `clients.import`, `clients.export`
- `checkins.view`, `checkins.scan`, `checkins.export`
- `reports.view`, `reports.export`
- `audit-logs.view`
- `scanners.view`, `scanners.create`, `scanners.update`, `scanners.delete`

#### Role → Permission Mapping:

| Permission | sys_admin | sys_audit | sys_support | co_admin | co_manager | co_user | scanner |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| system.manage | ✅ | | | | | | |
| system.audit | ✅ | ✅ | | | | | |
| system.support | ✅ | | ✅ | | | | |
| companies.* | ✅ | view | view+update | | | | |
| system-users.* | ✅ | view | view+update | | | | |
| company.settings.* | ✅ | | | ✅ | | | |
| users.* | ✅ | | | ✅ | view | | |
| events.* | ✅ | | | ✅ | ✅ | view | |
| clients.* | ✅ | | | ✅ | ✅ | view | |
| checkins.* | ✅ | | | ✅ | ✅ | scan | scan |
| reports.* | ✅ | ✅ | | ✅ | view | | |
| audit-logs.view | ✅ | ✅ | | limited | | | |
| scanners.* | ✅ | | | ✅ | view | | |

---

### Phase 5: Middleware & Security

#### [NEW] `app/Http/Middleware/`

| Middleware | Description |
|---|---|
| `EnsureTenantAccess.php` | Verify user belongs to the requested company, inject `company_id` |
| `EnsureEventAccess.php` | Verify user has access to the requested event |
| `CheckPermission.php` | Wrapper around Spatie permission check with custom error response |
| `ForceJson.php` | Force `Accept: application/json` trên tất cả API requests |
| `LogApiRequest.php` | Log request/response cho audit (không log sensitive data) |
| `SecurityHeaders.php` | Add security headers (X-Content-Type-Options, X-Frame-Options, etc.) |

#### Rate Limiting (trong `AppServiceProvider`):
- Auth endpoints: 5 requests/minute per IP
- Scanner login: 10 requests/minute per IP
- API general: 60 requests/minute per user
- Write operations: 30 requests/minute per user

---

### Phase 6: API Endpoints

#### [NEW] Controllers (`app/Http/Controllers/Api/V1/`):

**System Admin APIs:**

| # | Controller | Endpoints |
|---|---|---|
| 1 | `CompanyController` | GET /companies, POST /companies, GET /companies/{id}, PUT /companies/{id}, DELETE /companies/{id} |
| 2 | `SystemUserController` | GET /system/users, POST /system/users, GET /system/users/{id}, PUT /system/users/{id}, DELETE /system/users/{id} |

**Company-scoped APIs:**

| # | Controller | Endpoints |
|---|---|---|
| 3 | `UserController` | GET /companies/{company}/users, POST /companies/{company}/users, GET /companies/{company}/users/{id}, PUT /companies/{company}/users/{id}, DELETE /companies/{company}/users/{id} |
| 4 | `EventController` | GET /companies/{company}/events, POST /companies/{company}/events, GET /companies/{company}/events/{id}, PUT /companies/{company}/events/{id}, DELETE /companies/{company}/events/{id} |
| 5 | `ClientController` | GET /events/{event}/clients, POST /events/{event}/clients, GET /events/{event}/clients/{id}, PUT /events/{event}/clients/{id}, DELETE /events/{event}/clients/{id} |
| 6 | `CheckinController` | POST /events/{event}/checkins/scan, GET /events/{event}/checkins, GET /events/{event}/checkins/stats |
| 7 | `ScannerController` | GET /companies/{company}/scanners, POST /companies/{company}/scanners, PUT /companies/{company}/scanners/{id}, DELETE /companies/{company}/scanners/{id} |
| 8 | `RolePermissionController` | GET /roles, GET /permissions, POST /companies/{company}/users/{user}/roles, DELETE /companies/{company}/users/{user}/roles/{role} |
| 9 | `ReportController` | GET /events/{event}/reports/summary, GET /events/{event}/reports/checkins |
| 10 | `AuditLogController` | GET /audit-logs (system), GET /companies/{company}/audit-logs |
| 11 | `ProfileController` | GET /profile, PUT /profile |

#### [NEW] FormRequests (`app/Http/Requests/Api/V1/`) — ~20 files
Mỗi controller có các FormRequest tương ứng cho store/update.

#### [NEW] Resources (`app/Http/Resources/`) — ~10 files
- `UserResource`, `CompanyResource`, `EventResource`, `ClientResource`
- `CheckinResource`, `AuditLogResource`, `RoleResource`, `PermissionResource`
- Collection resources tự động.

#### [NEW] Services (`app/Services/`)
- `CompanyService`, `EventService`, `ClientService`, `CheckinService`
- `UserService`, `ScannerService`, `AuditLogService`, `ReportService`

#### [NEW] Policies (`app/Policies/`)
- `CompanyPolicy`, `EventPolicy`, `ClientPolicy`, `CheckinPolicy`
- `UserPolicy`, `ScannerPolicy`, `AuditLogPolicy`

---

### Phase 7: Comprehensive Seeders

#### [NEW] Seeders (`database/seeders/`)

| Seeder | Data |
|---|---|
| `RoleAndPermissionSeeder` | 7 roles + 35 permissions + mapping |
| `CompanySeeder` | 3 companies (active, inactive, suspended) |
| `SystemUserSeeder` | 3 system users (admin, audit, support) |
| `CompanyUserSeeder` | Mỗi company: 1 admin, 2 managers, 5 users, 2 scanners |
| `EventSeeder` | Mỗi company: 3-5 events (đủ status) |
| `ClientSeeder` | Mỗi event: 20-50 clients |
| `CheckinSeeder` | Random check-ins cho active events |
| `EventUserSeeder` | Gán users vào events |

#### [MODIFY] [DatabaseSeeder.php](file:///Users/leviackerman/Codes/checkin-v4/api/database/seeders/DatabaseSeeder.php)
Call tất cả seeders theo đúng thứ tự dependency.

**Test credentials (cho dev):**
| Role | Email | Password |
|---|---|---|
| System Admin | sysadmin@delfi.vn | password |
| System Audit | audit@delfi.vn | password |
| System Support | support@delfi.vn | password |
| Company Admin | admin@company1.vn | password |
| Company Manager | manager@company1.vn | password |
| Company User | user@company1.vn | password |
| Scanner | device_code: `SCAN001`, pin: `1234` | |

---

### Phase 8: API Documentation UI (Scribe)

#### [NEW] Install & configure `knuckleswtf/scribe`
- Install via composer
- Publish config
- Configure `try_it_out.enabled = true`
- Configure base URL, auth scheme (Bearer token)
- Group endpoints by controller/tag
- Auto-generate response examples từ seeders
- UI accessible tại `/docs`

#### Scribe Features:
- ✅ Auto-discover routes từ `routes/api.php`
- ✅ Parse FormRequests cho request parameters
- ✅ Parse Resources cho response shape
- ✅ **"Try It Out"** button — fill params, edit body, gửi thử
- ✅ Auth header auto-fill
- ✅ Exportable OpenAPI spec (nếu cần)

---

### Phase 9: Performance & Security Hardening

#### Performance:
- Eager loading relationships trong tất cả list queries
- Database indexes trên tất cả foreign keys và frequently-queried columns
- Pagination mặc định 15, max 100
- Route caching friendly (no closures in routes)
- Config caching friendly

#### Security:
- `ForceJson` middleware — đảm bảo API luôn trả JSON
- `SecurityHeaders` middleware — X-Content-Type-Options, X-Frame-Options
- Rate limiting per route group
- Input validation qua FormRequests
- Tenant isolation qua `EnsureTenantAccess` middleware
- Audit logging cho mutations nhạy cảm
- Password hashing (bcrypt, Laravel default)
- Token abilities/scopes cho Sanctum tokens
- No stack traces in production (`APP_DEBUG=false`)

---

## Route Structure Summary

```
/api/v1/
├── health                          # GET - Health check
├── auth/
│   ├── login                       # POST
│   ├── logout                      # POST [auth]
│   ├── me                          # GET  [auth]
│   ├── refresh                     # POST [auth]
│   └── change-password             # PUT  [auth]
├── scanner/
│   ├── login                       # POST
│   ├── logout                      # POST [auth]
│   └── events                      # GET  [auth]
├── profile                         # GET/PUT [auth]
├── roles                           # GET [auth]
├── permissions                     # GET [auth]
├── companies/                      # [auth, system roles]
│   ├── (CRUD)
│   └── {company}/
│       ├── users/                  # [auth, tenant]
│       │   ├── (CRUD)
│       │   └── {user}/roles        # POST/DELETE
│       ├── events/                 # [auth, tenant]
│       │   └── (CRUD)
│       ├── scanners/               # [auth, tenant]
│       │   └── (CRUD)
│       └── audit-logs              # GET [auth, tenant]
├── events/{event}/                 # [auth, event access]
│   ├── clients/                    # (CRUD)
│   ├── checkins/
│   │   ├── scan                    # POST
│   │   ├── (list)                  # GET
│   │   └── stats                   # GET
│   └── reports/
│       ├── summary                 # GET
│       └── checkins                # GET
├── system/
│   └── users/                      # [auth, system admin]
│       └── (CRUD)
└── audit-logs                      # GET [auth, system audit]
```

---

## File Count Estimate

| Category | Files | 
|---|---|
| Migrations | 7 |
| Models | 7 (1 modify + 6 new) |
| Enums | 8 |
| Controllers | 12 |
| FormRequests | ~22 |
| Resources | ~10 |
| Services | 8 |
| Policies | 7 |
| Middleware | 6 |
| Seeders | 8 (1 modify + 7 new) |
| Support/Helpers | 2 (1 modify + 1 new) |
| Config | 2 (modify sanctum, add scribe) |
| Routes | 1 (modify api.php) |
| **Total** | **~100 files** |

---

## Verification Plan

### Automated Tests
```bash
# 1. Run migrations
cd api && php artisan migrate:fresh

# 2. Run seeders
cd api && php artisan db:seed

# 3. Verify routes registered
cd api && php artisan route:list --path=api/v1

# 4. Test login flow
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"sysadmin@delfi.vn","password":"password"}'

# 5. Verify permission check
curl -X GET http://localhost:8000/api/v1/companies \
  -H "Authorization: Bearer {token}"

# 6. Run linter
cd api && ./vendor/bin/pint --test

# 7. Verify API docs UI loads
# Open http://localhost:8000/docs in browser
```

### Manual Verification
- Kiểm tra Scribe docs UI load đúng, "Try It Out" hoạt động
- Test RBAC: login với các role khác nhau, verify quyền đúng
- Test tenant isolation: user company A không xem được data company B
- Test scanner auth: login bằng device_code + pin
