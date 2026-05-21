# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {BEARER_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Authenticate via `POST /api/v1/auth/login` to get a Bearer token. Include it in the `Authorization` header as `Bearer {token}`. Scanner devices use `POST /api/v1/scanner/login` with device_code + PIN.
