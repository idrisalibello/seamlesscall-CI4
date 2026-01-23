# Seamless Call — API Contract (Backend Source of Truth) (v1.4)

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
- Dashboard module: `/api/v1/dashboard` (auth required)
- Admin module: `/api/v1/admin` (auth required)
- Operations module: `/api/v1/operations` (auth required)
- System module: `/api/v1/system` (auth required)

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
### Success (recommended)
- `{ status, message, data }`

### Error (recommended)
- `{ message, errors?, error_code? }`

---

## Endpoints (CONFIRMED)

### Auth (`/api/v1`)
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Register | POST | `/api/v1/register` | No |
| Login (password) | POST | `/api/v1/login` | No |
| Request login OTP | POST | `/api/v1/auth/otp/request` | No |
| Login with OTP | POST | `/api/v1/auth/otp/login` | No |
| Apply as Provider | POST | `/api/v1/auth/apply-as-provider` | Yes |

---

### Dashboard (`/api/v1/dashboard`, filter: `auth`)
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Get dashboard stats | GET | `/api/v1/dashboard/stats` | Yes |

---

### Admin (`/api/v1/admin`, filter: `auth`)
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
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| List services by category | GET | `/api/v1/admin/categories/{categoryId}/services` | Yes |
| Create service in category | POST | `/api/v1/admin/categories/{categoryId}/services` | Yes |
| Update service | PUT | `/api/v1/admin/services/{serviceId}` | Yes |
| Delete service | DELETE | `/api/v1/admin/services/{serviceId}` | Yes |

#### Admin — User management
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| List users | GET | `/api/v1/admin/users` | Yes |
| Update user | PUT | `/api/v1/admin/users/{id}` | Yes |
| Get user roles | GET | `/api/v1/admin/users/{id}/roles` | Yes |
| Update user roles | PUT | `/api/v1/admin/users/{id}/roles` | Yes |

---

### Operations (`/api/v1/operations`, filter: `auth`)
#### Provider routes
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Get provider job details | GET | `/api/v1/operations/provider/jobs/{jobId}` | Yes |
| Update provider job status | PUT | `/api/v1/operations/provider/jobs/{jobId}/status` | Yes |
| Get provider active jobs | GET | `/api/v1/operations/provider/jobs` | Yes |

#### Admin routes (within operations)
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Get active jobs | GET | `/api/v1/operations/admin/jobs` | Yes |
| Get pending jobs | GET | `/api/v1/operations/admin/jobs/pending` | Yes |
| Get job details | GET | `/api/v1/operations/admin/jobs/{jobId}` | Yes |
| Assign provider to job | POST | `/api/v1/operations/admin/jobs/{jobId}/assign` | Yes |
| Get available providers | GET | `/api/v1/operations/admin/providers/available` | Yes |

---

### System (`/api/v1/system`, filter: `auth`)
#### Roles
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Get roles | GET | `/api/v1/system/roles` | Yes |
| Create role | POST | `/api/v1/system/roles` | Yes |

#### Permissions
| Purpose | Method | Path | Auth? |
|---|---:|---|---:|
| Get permissions | GET | `/api/v1/system/permissions` | Yes |
| Get role permissions | GET | `/api/v1/system/roles/{roleId}/permissions` | Yes |
| Update role permissions | PUT | `/api/v1/system/roles/{roleId}/permissions` | Yes |
