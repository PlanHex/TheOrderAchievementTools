Purpose
-------
This repository implements a small, framework-free PHP MVC app with a "feature-first" layout and a dual run-mode (Production = MySQL, Demo = CSV+Session). These instructions orient AI coding agents to the project's architecture, key patterns, and the minimal developer workflows necessary to be productive immediately.

The repository is organized into **production** (deploy to servers), **development** (local tools), **local** (Docker/tests), and **docs** (architecture/requirements).

Quick Architecture Summary
--------------------------
- Feature-first MVC: domain features live under `production/src/Modules/<Feature>/` (Controller, Domain, Repository, Views). See `docs/architecture.md` for the full layout.
- Core services live in `production/src/Core/` (Container, Router, Renderer, Database wrapper).
- Persistence implementations live in `production/src/Infrastructure/Persistence/` with two modes: MySQL and InMemory/Session (Dual Mode).
- Public webroot is `production/public/` (entry point `production/public/index.php`) and static assets under `production/public/assets/` (CSS + minimal JS). UI helpers live in `production/public/assets/js/` (searchable list helpers).

Important Files & Data
----------------------
- Data CSVs (Demo mode): `data/achievements.csv`, `data/categories.csv`, `data/users.csv`, `data/user_achievements.csv`.
- SQL schema (Production): `data/sql/sql_tables.sql` — run this on your MySQL server to create schema.
- Project spec & architecture: `docs/requirements.md` and `docs/architecture.md` (read both before changing modes or persistence).
- Helper script: `data/scripts/csv_generator.ps1` (PowerShell) — regenerates CSV outputs used in Demo mode.

Quick Links (within production/ folder)
- `config/app.php` (mode + auth)
- `config/database.php` (MySQL creds)
- `src/Core/Container.php` (wiring + service factories)
- `src/Core/Renderer.php` (view rendering + escaping)
- `src/Infrastructure/Persistence/InMemory/` (CSV seeding + session repos)
- `src/Infrastructure/Persistence/MySQL/` (PDO-backed repos)
- `public/index.php` (front controller + routes)
- `public/assets/js/` (searchable list helpers)

Development Scripts (in development/scripts/)
- `smoke.php` — validate CSV/repo counts
- `check_csvs.php` — verify CSV integrity
- `seed_demo.php` — seed demo data into session

PR Checklist
- Run `php development/scripts/check_csvs.php` and `php development/scripts/smoke.php` locally.
- Confirm `production/config/app.php` `mode` is set correctly for the change (demo vs production).
- Ensure new persistence logic is behind repository interfaces in `production/src/Modules/*/Repository/`.
- Add or update views in `production/src/Modules/<Feature>/Views/` and use `Renderer::e()` for escaping.

Developer Workflows (concrete)
------------------------------
- Run locally (quick dev server):

  PowerShell (from production/ directory)
  ```powershell
  cd production
  php -S 127.0.0.1:8000
  ```

- Switch modes: update `production/config/app.php` to set `'mode' => 'production'` or `'mode' => 'demo'`. In demo mode the app reads `data/*.csv` and keeps changes in session/in-memory repositories; in production it uses `production/config/database.php` + MySQL.
- Initialize DB: apply `data/sql/sql_tables.sql` to your MySQL 8 server and update `production/config/database.php` with credentials.
- Rebuild CSVs: run `.\data\scripts\csv_generator.ps1` from the repo root in PowerShell to regenerate CSV files if needed.

Project Conventions & Patterns (how to edit)
-------------------------------------------
- No external frameworks: keep code plain PHP (target: PHP 8.3). Avoid adding Composer deps unless explicitly approved.
- Renderer usage: Controllers return views with the Renderer. Example from the architecture notes:

  ```php
  return $this->renderer->render('Modules/Achievement/Views/index', [
      'achievements' => $achievements,
      'title' => 'All Achievements'
  ]);
  ```

- Views are colocated with the domain: add/modify view files under `production/src/Modules/<Feature>/Views/` and keep layout wrappers in `production/templates/header.php` / `production/templates/footer.php`.
- Repositories:
  - Define interfaces under `production/src/Modules/<Feature>/Repository/`.
  - Implement concrete persistence in `production/src/Infrastructure/Persistence/MySQL/` and `production/src/Infrastructure/Persistence/InMemory/` (session-backed) so controllers remain agnostic to mode.

Integration Details & Data Flow Notes
------------------------------------
- Dual Mode: The DI container (in `production/src/Core/Container.php`) decides which repository implementation to inject based on `production/config/app.php` `mode` value. Controllers should work with interfaces only.
- Reordering UI: numeric `Display_Order` inputs in server-rendered forms update repository state. Small vanilla JS helpers under `production/public/assets/js/` may be used for searchable lists or optional async saves; prefer standard POST form submissions for simplicity and CSRF protection.
- Security: Production mode expects Basic Auth (Auth module) and CSRF tokens on forms; Demo mode disables authentication. All HTML output should be escaped via Renderer.

Docker Compose: Local deployment & debugging
------------------------------------------
- Docker setup files are in `local/docker/` with Dockerfile and docker-compose.yml.
- Build and run (from local/docker directory):

```powershell
cd local/docker
docker compose up --build -d
```

- App will be available at `http://localhost:8000/` (the container maps port 80 -> 8000).
- MySQL is exposed on port `3306` (user: `app`, pass: `secret`, db: `order_achievements`, root: `root`).
- Xdebug is enabled and defaults to `client_host=host.docker.internal` and `client_port=9003`. Adjust `XDEBUG_CONFIG` in `local/docker/docker-compose.yml` or your IDE's listener config if necessary.
- For demo mode keep `production/config/app.php` `'mode' => 'demo'`. To use MySQL set `'mode' => 'production'` and update `production/config/database.php` to match the `db` service credentials above or map envs as needed.

Notes for debugging on Windows:
- Docker Desktop exposes the host via `host.docker.internal` which Xdebug uses by default in the image. If using WSL2 or a different setup, adjust `xdebug.client_host` accordingly.

What to watch for when changing code
-----------------------------------
- Keep persistence changes behind repository interfaces — do not hardcode CSV or SQL logic into controllers.
- When adding a feature, create the module skeleton under `production/src/Modules/<Feature>/` (Controller, Domain, Repository interface, Views). Wire concrete implementations into the DI container.
- Minimal JS allowed — keep business logic server-side. JS should be used only for UX (searchable lists, small fetch calls).

Examples & Starting Tasks for an AI Agent
-----------------------------------------
- Add a new achievement field: update the Domain entity under `production/src/Modules/Achievement/Domain/`, adjust the repository interface, and implement persistence in both MySQL and InMemory repos.
- Implement export pages: Master List and Roster List should render BBCode; see `docs/requirements.md` for the expected outputs and base the view markup in `production/src/Modules/Achievement/Views/` or `production/src/Modules/User/Views/`.

If anything is unclear
----------------------
- Ask for the location of `production/config/` files if missing, or request permission before adding external dependencies. Tell me which feature you plan to edit and I'll point to exact files to change.
- For detailed structure: see `REPOSITORY_STRUCTURE.md` and `DEVELOPMENT.md` at the repo root.

End of file
