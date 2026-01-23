# Seamless Call — Backend Project Map (CI4) (v1)

## Repo root (what matters)
- `app/` — application code
- `public/` — web root (`index.php`, `.htaccess`)
- DB dump present: `public/seamless_call_db.sql` (reference only)

## Application structure (observed)

### Routing & HTTP entry
- `public/index.php` — CI4 front controller
- `public/.htaccess` — rewrite to `index.php`
- `app/Config/Routes.php` — route definitions (customized; small file)
- `app/Config/Modules.php` — module system configuration (present)
- `app/Config/Filters.php` — global/route filters configuration (present)

### CORS
- `app/Config/Cors.php` — CORS policy configuration (present)
- `public/cors_test.php` — local test script (exists; ensure it matches real API behavior)

### Filters / middleware-like logic
- `app/Filters/` — filter implementations (auth/role/CORS/etc.)
- `app/Config/Filters.php` — where filters are attached to routes/groups

### Data layer
- `app/Models/` — models
- `app/Database/` — migrations/seeds (preferred over raw SQL dump)

### Modular domains (non-default CI4, primary feature organization)
`app/Modules/` contains:
- `Admin/`
- `Auth/`
- `Dashboard/`
- `Operations/`
- `System/`

This folder is expected to contain most feature controllers/services/models per domain.

## Golden files
