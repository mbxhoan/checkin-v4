# Introduction

Event Management SaaS API. Multi-tenant, RBAC-enabled, Sanctum-authenticated.

<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>

    Welcome to the **Delfi Check-in V4** API documentation.

    This API powers a multi-tenant Event Management SaaS platform with:
    - **Token-based authentication** via Laravel Sanctum
    - **Role-based access control** (RBAC) with 7 role levels
    - **Multi-tenant isolation** — company-scoped data boundaries
    - **QR-based check-in/check-out** for event attendees

    <aside>Use the <strong>Try It Out</strong> button on any endpoint to test it directly from this page. You'll need a Bearer token — get one from the <code>POST /api/v1/auth/login</code> endpoint.</aside>

    ### Test Credentials
    | Role | Email | Password |
    |---|---|---|
    | System Admin | sysadmin@delfi.vn | password |
    | Company Admin | admin@company1.vn | password |
    | Company Manager | manager1@company1.vn | password |
    | Company User | user1@company1.vn | password |
    | Scanner | device_code: `SCAN101`, pin: `1234` |

