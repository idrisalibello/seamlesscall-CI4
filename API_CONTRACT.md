# Seamless Call — API Contract (Backend Source of Truth) (v1)

This document defines the backend API contract. Flutter must conform to it.

## Routing authority
- Routes live in: `app/Config/Routes.php`
- Modules exist in: `app/Modules/` (Admin, Auth, Dashboard, Operations, System)
- Filters configuration: `app/Config/Filters.php`
- CORS configuration: `app/Config/Cors.php`

## Hosting modes to keep explicit
1) **Public web root mode**
- DocumentRoot: `<repo>/public`
- Base URL: `http://<host>/`
- Paths: `/api/v1/...` (verify in Routes.php)

2) **Repo root mode**
- DocumentRoot: `<repo>/`
- Rewrite must forward to `public/index.php`
- Base URL may look like: `http://<host>/seamless_call/`
- Paths still: `/api/v1/...` (verify in Routes.php)

## Authentication (contract)
- Scheme: Bearer token (`Authorization: Bearer <token>`)
- Unauthorized: HTTP 401
- Error shape: define and keep consistent (see below)

## CORS (contract)
Configured in `app/Config/Cors.php`:
- Must allow required origins for Flutter Web
- Must allow `Authorization` header
- Must allow `OPTIONS` preflight for API routes

## Response envelopes (define and enforce)
### Success (TBD — fill from real responses)
- `data`: TBD
- `message`: TBD
- `status`: TBD

### Error (TBD — fill from real responses)
- `message`: TBD
- `errors`: TBD
- `error`: TBD
- `error_code`: TBD (recommended)

## Endpoints (populate from actual routes)
| Domain | Purpose | Method | Path | Auth? | Request | Success Response | Error Response |
|---|---|---:|---|---:|---|---|---|
| Auth | Login | POST | `/api/v1/login` | No | `{ email_or_phone, password }` | TBD | TBD |
| Auth | Logout | POST | `/api/v1/logout` | Yes | none | TBD | TBD |
