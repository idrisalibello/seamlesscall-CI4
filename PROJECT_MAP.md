# Seamless Call — Backend Project Map (CI4) (v1)

## Repo root (what matters)
- `app/` — application code (controllers, models, modules, filters, config)
- `public/` — web root (front controller `index.php`, `.htaccess`)
- DB dump present: `public/seamless_call_db.sql` (reference only; do not treat as migrations)

## CI4 structure (observed)
### `app/Config/`
- Application configuration (routing, filters, CORS policy, environment settings)

### `app/Controllers/`
- HTTP controllers (API endpoints live here or under Modules)

### `app/Models/`
- Database models (used by controllers/services)

### `app/Filters/`
- Cross-cutting concerns:
  - authentication/authorization
  - CORS handling (often here or in Config)
  - request guards

### `app/Database/`
- Migrations and seeds (preferred over raw SQL dumps for reproducibility)

### `app/Modules/` (non-default CI4, important)
- Modular organization of domain areas (likely where most feature controllers live)
- This folder must be mapped carefully because it defines the real API surface.

### `app/Helpers/`
- Utility helpers (pure functions / shared logic)

### Other folders present (not mapped yet)
- `app/Views/` — server-rendered views (if any)
- `app/Libraries/`, `app/ThirdParty/` — custom libs / external code

## Public web root (critical)
### `public/index.php`
- Entry point for all web requests (front controller)

### `public/.htaccess`
- URL rewriting and routing to `index.php`

### `public/cors_test.php`
- Local CORS testing script (exists; policy must be consistent with API)

## “Golden files” (first to reference during bugs)
1. `app/Config/Routes.php` (or routing config location)
2. `app/Filters/*` (auth/CORS enforcement)
3. `public/.htaccess` + `public/index.php` (rewrite + base path)
4. Auth controllers/services (location TBD; likely `app/Controllers` or `app/Modules/*`)
5. `.env` (existence and key settings; do not commit secrets)

## Known integration hazards (must stay explicit)
- `/public` vs non-`/public` base URL decisions depend on vhost config and rewrite rules.
- `/api/v1` routing prefix must be consistent with Flutter requests.
- CORS behavior differs between mobile (often “works”) and web (strict); policy must be verified.

## Bug report format (backend)
- Request: method + full path + headers (especially Origin/Authorization)
- Response: status + body
- Relevant file paths + 30–80 lines only
