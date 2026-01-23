# Seamless Call — API Contract (Backend Source of Truth) (v1)

This document defines the backend API contract. Flutter must conform to it.

---

## Routing authority (AUTHORITATIVE)
- Root router: `app/Config/Routes.php`
  - Loads module route files:
    - `app/Modules/Auth/Config/Routes.php`
    - `app/Modules/Dashboard/Config/Routes.php`
    - `app/Modules/Admin/Config/Routes.php`
    - `app/Modules/System/Config/Routes.php`
    - `app/Modules/Operations/Config/Routes.php`

### Root routes (non-API)
- `GET /`
- `GET /testotp/send`
- `POST /auth/oauth`

---

## API prefix (confirmed)
- The backend defines API routes under: `api/v1`
- Example (Auth module): `$routes->group('api/v1', ...)`

Therefore, Flutter API calls should use:
- `.../api/v1/<endpoint>`

---

## Authentication (contract)
- Scheme: Bearer token (`Authorization: Bearer <token>`)
- Auth-protected routes use filter: `auth`
- Unauthorized response: HTTP 401

### Token invalid/expired
- Must return 401 consistently
- Error body shape must be kept stable (define below)

---

## CORS (contract)
Configured in:
- `app/Config/Cors.php`

Must support:
- Browser origins for Flutter Web (allowed origins must be explicit)
- `Authorization` header
- `OPTIONS` preflight for all `/api/v1/*` routes

---

## Response envelopes (TBD — normalize)
### Success
- Envelope shape: TBD (recommend consistent `{ status, message, data }`)

### Error
- Envelope shape: TBD (recommend consistent `{ message, errors?, error_code? }`)

---

## Endpoints (CONFIRMED from Auth module)

| Domain | Purpose | Method | Path | Auth? | Notes |
|---|---|---:|---|---:|---|
| Auth | Register | POST | `/api/v1/register` | No | namespace: `App\Modules\Auth\Controllers` |
| Auth | Login (password) | POST | `/api/v1/login` | No |  |
| Auth | Request login OTP | POST | `/api/v1/auth/otp/request` | No |  |
| Auth | Login with
