# Project — Frontend + Backend

This repository contains two apps:

- `frontend/product/` — Next.js app (frontend)
- `backend/product/` — Laravel app (backend API)

A single shared SQLite database file is kept at the repository root:

`project/database/database.sqlite`

## Quick setup

1. Create the shared database file (from repository root):

- PowerShell

```powershell
./scripts/create_db.ps1
```

- macOS / Linux

```bash
./scripts/create_db.sh
```

2. Configure the backend `.env` (see `backend/product/README.md`):

```env
DB_CONNECTION=sqlite
DB_DATABASE=../../database/database.sqlite
```

3. Prepare and run the backend (from `backend/product`):

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

4. Run the frontend (from `frontend/product`):

```bash
npm install
npm run dev
# open http://localhost:3000
```

## Convenience

- To start both dev servers at once (PowerShell will open two windows):

```powershell
./scripts/run_all.ps1
```

- For Unix-like systems you can run the `run_all.sh` (backend will run in background):

```bash
./scripts/run_all.sh
```

## Notes

- The frontend reads `NEXT_PUBLIC_API_URL` for the backend base URL. Example: `NEXT_PUBLIC_API_URL=http://127.0.0.1:8000`
- Do not commit `database/database.sqlite`. It's included in `.gitignore`.

---
If you'd like, I can add a root-level `package.json` for convenience scripts (e.g., `npm run dev` to run both), or wire up a simple Docker Compose file to run both services.