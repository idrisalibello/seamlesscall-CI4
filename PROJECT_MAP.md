# Seamless Call — Backend Project Map (CI4) (v1)

## Repo root (what matters)
- `app/` — application code
- `public/` — web root (`index.php`, `.htaccess`)
- DB dump present: `public/seamless_call_db.sql` (reference only, not migrations)

---

## Application structure (observed)

### Routing & HTTP entry (AUTHORITATIVE)
- `public/index.php` — CI4 front controller
- `public/.htaccess` — rewrite rules to `index.php`
- `app/Config/Routes.php` — **root router**
  - Defines base routes
  - Loads module routes explicitly
- `app/Config/Modules.php` — module system configuration
- `app/Config/Filters.php` — route + global filters

#### Root routes defined directly
- `GET /`
- `GET /testotp/send`
- `POST /auth/oauth`

#### Module routes loaded from
- `app/Modules/Auth/Config/Routes.php`
- `app/Modules/Dashboard/Config/Routes.php`
- `app/Modules/Admin/Config/Routes.php`
- `app/Modules/System/Config/Routes.php`
- `app/Modules/Operations/Config/Routes.php`

> ⚠️ IMPORTANT  
> There is **NO global `/api/v1` prefix** defined at root level.  
> Any prefix must be defined **inside individual module route files**.

---

### CORS
- `app/Config/Cors.php` — CORS policy (authoritative)
- `public/cors_test.php` — manual test script (must reflect real API behavior)

Browser clients (Flutter Web) depend on this being correct.

---

### Filters / middleware-like logic
- `app/Filters/` — filter implementations (auth, role, CORS, guards)
- `app/Config/Filters.php` — where filters are attached to routes / groups

All auth enforcement happens here or via module route groups.

---

### Data layer
- `app/Models/` — database models
- `app/Database/` — migrations & seeds (preferred)
- Raw SQL dump exists but is **not authoritative**

---

### Modular domains (PRIMARY backend organization)
`app/Modules/` contains the real API surface:

- `Auth/` — authentication & authorization
- `Admin/` — admin features
- `Dashboard/` — dashboard data aggregation
- `Operations/` — operational workflows
- `System/` — system-level configuration / utilities

Each module is expected to contain:
- Controllers
- Models
- Services
- Its own `Config/Routes.php`

---

## Golden files (check these first when debugging)
1. `app/Config/Routes.php`
2. `app/Modules/*/Config/Routes.php`
3. `app/Config/Cors.php`
4. `app/Config/Filters.php`
5. `app/Filters/*`
6. `public/.htaccess`
7. `public/index.php`

---

## Known integration hazards (explicit)
- Base URL **MUST** match hosting mode (`/public` vs repo root).
- API prefixes are **module-defined**, not global.
- CORS must allow:
  - `Authorization` header
  - `OPTIONS` preflight
  - Flutter Web origins

---

## Backend bug report format (mandatory)
- HTTP method + full path
- Request headers (`Origin`, `Authorization`)
- Status code + response body
- File paths involved
- 30–80 relevant lines only
