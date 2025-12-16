

  # Project (Frontend + Backend)

This repository contains two applications and a shared database:

- `frontend/product/` — Next.js frontend (renders UI and calls the backend API)
- `backend/product/` — Laravel backend (API server; handles database access)
- `database/database.sqlite` — shared SQLite database file (repository root)

Top-level helpers:
- `scripts/create_db.*` — helper scripts to create the repository-root DB file when needed.
- `scripts/run_all.*` — convenience scripts to start frontend and backend dev servers.

Notes (what code does):
- The frontend renders pages, components, and static assets; it reads `NEXT_PUBLIC_API_URL` to locate the backend API.
- The backend manages HTTP endpoints, business logic, migrations, and direct database access (SQLite at `project/database/database.sqlite`).
- Tests can run against an in-memory SQLite DB by setting `DB_DATABASE=:memory:` for test runs.

Repository policy:
- Do not commit the shared `database/database.sqlite`; it is ignored via `.gitignore`.

Folder structure (what each folder contains):

- `frontend/product/` — Next.js frontend
  - `app/` — routes, pages and layouts
  - `app/components/` — reusable UI components (e.g. `layout/Footer.tsx`)
  - `public/` — static assets
  - `styles/` — global styles and Tailwind configuration
  - config files: `package.json`, `tsconfig.json`

- `backend/product/` — Laravel backend
  - `app/` — Controllers, Models, and application services
  - `routes/` — `api.php` (API endpoints), `web.php` (web routes)
  - `database/` — migrations and seeders (migrations live here; runtime DB is `project/database/database.sqlite`)
  - config files: `composer.json`, `phpunit.xml`

- `database/` — repository-root SQLite database file (`database.sqlite`, ignored by git)
- `scripts/` — helper scripts (`create_db.*`, `run_all.*`)
- `.gitignore` — repository ignore rules
- `README.md` — this file (overview and folder map)

Directory tree (full, key folders only; omits generated folders like `node_modules`, `.next`, `vendor`):

```
project/
├─ frontend/
│  └─ product/
│     ├─ .gitignore
│     ├─ .next/                (build output)
│     ├─ app/
│     │  ├─ components/
│     │  │  ├─ layout/
│     │  │  │  ├─ Footer.tsx
│     │  │  │  ├─ Header.tsx
│     │  │  │  └─ Navigatio.tsx
│     │  │  └─ products/
│     │  │     ├─ ProductCard.tsx
│     │  │     ├─ ProductGrid.tsx
│     │  │     ├─ ReviewCard.tsx
│     │  │     ├─ ReviewFrom.tsx
│     │  │     └─ ReviewList.tsx
│     │  ├─ lib/
│     │  ├─ products/
│     │  ├─ page.tsx
│     │  └─ layout.tsx
│     ├─ public/
│     │  ├─ file.svg
│     │  ├─ globe.svg
│     │  └─ ...
│     ├─ package.json
│     ├─ tsconfig.json
│     └─ README.md

├─ backend/
│  └─ product/
│     ├─ app/
│     │  ├─ Http/
│     │  │  ├─ Controllers/
│     │  │  ├─ Middleware/
│     │  │  └─ Requests/
│     │  ├─ Models/
│     │  └─ Providers/
│     ├─ bootstrap/
│     ├─ config/
│     ├─ database/
│     │  ├─ migrations/
│     │  ├─ seeders/
│     │  └─ database.sqlite   (symlink/pointed file at `../../database/database.sqlite`)
│     ├─ public/
│     ├─ resources/
│     ├─ routes/
│     │  ├─ api.php
│     │  └─ web.php
│     ├─ composer.json
│     └─ README.md

├─ database/
│  └─ database.sqlite

├─ scripts/
│  ├─ create_db.ps1
│  ├─ create_db.sh
│  ├─ run_all.ps1
│  └─ run_all.sh

├─ .gitignore
└─ README.md
```

If you want a deeper tree (include files like `migrations/*.php`, route files, or tests), tell me which parts to expand and I'll add them.

---

## Detailed explanations — what the code does 🔍

Below are concise, file-level descriptions to help you know where to add or find functionality.

### Frontend (`frontend/product/`)

- `app/layout.tsx` — the root layout for all pages; typically wraps pages with global providers, header and footer.
- `app/page.tsx` — top-level route (home page) that composes components and fetches data when needed.
- `app/components/layout/Footer.tsx` — footer UI (brand, product links, social icons). Keep only presentational logic here.
- `app/components/layout/Header.tsx` — site header/navigation and search; handle client-side routing with `next/link`.
- `app/components/products/*` — presentational components used to render lists and cards (`ProductCard`, `ProductGrid`, `ReviewCard`, etc.). These accept props (typed interfaces) and do not fetch data directly.
- `app/lib/` — utilities: API wrappers, fetch helpers, and shared client logic (e.g., `api.ts` with functions like `getProducts()` and `getProduct(id)` that call the backend using `NEXT_PUBLIC_API_URL`).
- `app/products/` — route-level UI for the products pages (index, show, detail pages). Place data-fetching in page/server components as appropriate.
- `public/` — static assets (images, icons) served as-is.
- `styles/` & `tailwind.config.js` — visual styling and Tailwind config.

Tip: Define shared TypeScript interfaces (e.g., `types/Product.ts`) for Product and Review to keep components typed and consistent.

### Backend (`backend/product/`)

- `app/Http/Controllers/ProductController.php` — handles API endpoints for products (index, show, store, update, destroy); it uses Eloquent models and returns API resources.
- `app/Models/Product.php` — Eloquent model representing products; define `$fillable` or `$guarded`, casts, and relationships (e.g., `reviews()`).
- `routes/api.php` — register API routes (e.g., `Route::apiResource('products', ProductController::class)`); these routes are consumed by the frontend.
- `database/migrations/` — schema migrations (create `products` table with columns such as `id`, `name`, `description`, `price`, `created_at`).
- `database/seeders/ProductSeeder.php` — creates sample products used in development and for initial data.
- `app/Http/Resources/ProductResource.php` — transforms model data for API responses (selects visible fields and relationships).
- `tests/Feature/ProductTest.php` — feature tests for API endpoints; use in-memory SQLite or the shared DB depending on test setup.

Note: The backend directly uses the shared SQLite file at `project/database/database.sqlite` (configured via `DB_CONNECTION=sqlite` and `DB_DATABASE=../../database/database.sqlite`). Keep migrations and seeders authoritative for schema and sample data.

### Shared repository-level files

- `database/database.sqlite` — single shared SQLite file (ignored in git). The backend reads/writes this file; frontend interacts only via API.
- `scripts/create_db.*` — helper scripts to create the shared DB file when needed.
- `scripts/run_all.*` — convenience scripts to start both development servers (useful during local development).
- `.gitignore` — ensures the DB and other local artifacts are not committed.

### Conventions & where to add things

- New backend endpoints → add Controller, update routes/api.php, add Migration + Seeder if new persistence is needed, and add Resource and tests.
- New frontend pages → add `app/<route>/page.tsx` and reusable components under `app/components/`; fetch via `app/lib/api` helpers that call backend routes.
- Tests → backend tests under `tests/Feature` and `tests/Unit`; frontend tests can live near components or in a top-level `tests/` depending on your test runner.

---

If you want, I can expand any section with example code snippets (e.g., sample `ProductController` methods, migration schema, or a `getProducts()` client wrapper). Tell me which part you'd like expanded.
