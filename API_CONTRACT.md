# Seamless Call — API Contract (Backend Source of Truth) (v1.1)

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

## API prefixes (CONFIRMED)
- Auth module routes are grouped under: `/api/v1`
- Admin module routes are grouped under: `/api/v1/admin` (and protected by `auth` filter)

---

## Authentication (contract)
- Scheme: Bearer token (`Authorization: Bearer <token>`)
- Auth-protected routes use filter: `auth`
- Unauthorized response: HTTP 401

---

## CORS (contract)
Configured in:
- `app/Config/Cors.php`

Must support:
- Browser origins for Flutter Web (allowed origins must be explicit)
- `Authorization` header
- `OPTIONS` preflight for all API routes

---

## Response envelopes (TBD — normalize)
### Success (recommend consistent)
- `{ status, message, data }` (TBD)

### Error (recommend consistent)
- `{ message, errors?, error_code? }` (TBD)

---

## Endpoints (CONFIRMED)

### Auth (namespace: `App\Modules\Auth\Controllers`)
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Register | POST | `/api/v1/register` | No |
| Login (password) | POST | `/api/v1/login` | No |
| Request login OTP | POST | `/api/v1/auth/otp/request` | No |
| Login with OTP | POST | `/api/v1/auth/otp/login` | No |
| Apply as Provider | POST | `/api/v1/auth/apply-as-provider` | Yes |

### Admin (namespace: `App\Modules\Admin\Controllers`, group filter: `auth`)
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| List provider applications | GET | `/api/v1/admin/provider-applications` | Yes |
| Approve/reject provider application | POST | `/api/v1/admin/provider-applications/status` | Yes |
| Create admin user | POST | `/api/v1/admin/users` | Yes |
| Get customers | GET | `/api/v1/admin/customers` | Yes |
| Get providers | GET | `/api/v1/admin/providers` | Yes |
| Get user details | GET | `/api/v1/admin/users/{id}` | Yes |
| Get user ledger | GET | `/api/v1/admin/users/{id}/ledger` | Yes |
| Get user refunds | GET | `/api/v1/admin/users/{id}/refunds` | Yes |
| Get user activity log | GET | `/api/v1/admin/users/{id}/activity` | Yes |
| Get provider earnings | GET | `/api/v1/admin/providers/{id}/earnings` | Yes |
| Get provider payouts | GET | `/api/v1/admin/providers/{id}/payouts` | Yes |

#### Admin — Categories & Services
| Purpose | Method | Path | Auth? | Notes |
|---|---:|---|---:|---|
| List services by category | GET | `/api/v1/admin/categories/{categoryId}/services` | Yes | Explicit route (must precede resource routes) |
| Create service in category | POST | `/api/v1/admin/categories/{categoryId}/services` | Yes |  |
| Update service | PUT | `/api/v1/admin/services/{serviceId}` | Yes |  |
| Delete service | DELETE | `/api/v1/admin/services/{serviceId}` | Yes |  |

#### Admin — Categories resource (CI4 resource routes)
Resource: `categories` via `CategoryController` under `/api/v1/admin`

Typical generated routes (confirm controller supports):
- `GET /api/v1/admin/categories`
- `GET /api/v1/admin/categ
