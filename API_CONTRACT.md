# Seamless Call — API Contract (Backend Source of Truth) (v1.2)

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
- Auth module: `/api/v1`
- Admin module: `/api/v1/admin` (auth required)
- Operations module: `/api/v1/operations` (auth required)

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
- Browser origins for Flutter Web
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

### Auth (`/api/v1`, namespace: `App\Modules\Auth\Controllers`)
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Register | POST | `/api/v1/register` | No |
| Login (password) | POST | `/api/v1/login` | No |
| Request login OTP | POST | `/api/v1/auth/otp/request` | No |
| Login with OTP | POST | `/api/v1/auth/otp/login` | No |
| Apply as Provider | POST | `/api/v1/auth/apply-as-provider` | Yes |

### Admin (`/api/v1/admin`, namespace: `App\Modules\Admin\Controllers`, filter: `auth`)
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

#### Admin — Categories & Services (filter: auth)
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| List services by category | GET | `/api/v1/admin/categories/{categoryId}/services` | Yes |
| Create service in category | POST | `/api/v1/admin/categories/{categoryId}/services` | Yes |
| Update service | PUT | `/api/v1/admin/services/{serviceId}` | Yes |
| Delete service | DELETE | `/api/v1/admin/services/{serviceId}` | Yes |

#### Admin — Categories resource routes (expected)
Resource: `categories` under `/api/v1/admin`
- `GET /api/v1/admin/categories`
- `GET /api/v1/admin/categories/{id}`
- `POST /api/v1/admin/categories`
- `PUT|PATCH /api/v1/admin/categories/{id}`
- `DELETE /api/v1/admin/categories/{id}`

#### Admin — User management (roles & permissions)
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| List users | GET | `/api/v1/admin/users` | Yes |
| Update user | PUT | `/api/v1/admin/users/{id}` | Yes |
| Get user roles | GET | `/api/v1/admin/users/{id}/roles` | Yes |
| Update user roles | PUT | `/api/v1/admin/users/{id}/roles` | Yes |

### Operations (`/api/v1/operations`, namespace: `App\Modules\Operations\Controllers`, filter: `auth`)
#### Provider routes
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Get provider job details | GET | `/api/v1/operations/provider/jobs/{jobId}` | Yes |
| Update provider job status | PUT | `/api/v1/operations/provider/jobs/{jobId}/status` | Yes |
| Get provider active jobs | GET | `/api/v1/operations/provider/jobs` | Yes |

#### Admin routes (within operations)
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Get pending jobs | GET | `/api/v1/operations/admin/jobs/pending` | Yes |
| Get job details | GET | `/api/v1/operations/admin/jobs/{jobId}` | Yes |
| Assign provider to job | POST | `/api/v1/operations/admin/jobs/{jobId}/assign` | Yes |
| Get available providers | GET | `/api/v1/operations/admin/providers/available` | Yes |

> NOTE  
> `GET /admin/jobs` is currently defined outside the group in the module file as pasted.
> It should be moved into the `/api/v1/operations` group to avoid a broken/ambiguous route.

---

## Next modules to extract (pending)
- Dashboard routes: `app/Modules/Dashboard/Config/Routes.php`
- System routes: `app/Modules/System/Config/Routes.php`
