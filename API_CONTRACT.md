# Seamless Call — API Contract (Backend Source of Truth) (v1)

This document defines the backend API. Flutter must conform to this contract.

## Base URL / Hosting modes
Two common modes must be supported and explicitly maintained:

1) **Public web root mode**
- Server points to: `<repo>/public`
- Base URL example: `http://<host>/`
- API paths look like: `/api/v1/...`

2) **Non-public web root mode (repo root as DocumentRoot)**
- Server points to: `<repo>/`
- Rewrite must forward to `public/index.php`
- Base URL example: `http://<host>/seamless_call/` (example only)
- API paths still must look like: `/api/v1/...`

## Routing
- Route file location: `app/Config/Routes.php` (verify)
- API prefix: `/api/v1` (verify)

## Authentication
- Scheme: Bearer token via `Authorization` header
- 401 behavior:
  - invalid/expired token returns HTTP 401
  - error shape must be consistent (define below)

## Standard response envelopes (define and enforce)
### Success
- `status`: TBD
- `message`: TBD
- `data`: TBD

### Error
- `message`: TBD
- `errors`: TBD
- `error_code`: TBD (recommended)

## Endpoints (populate as implemented)
| Domain | Purpose | Method | Path | Auth? | Request | Success Response | Error Response |
|---|---|---:|---|---:|---|---|---|
| Auth | Login | POST | `/api/v1/login` | No | `{ email_or_phone, password }` | TBD | TBD |
| Auth | Logout | POST | `/api/v1/logout` | Yes | none | TBD | TBD |

## CORS
- Must allow browser-based Flutter Web dev/test origins as needed (define allowed origins)
- Must allow `Authorization` header
- Must allow `OPTIONS` preflight for API routes
