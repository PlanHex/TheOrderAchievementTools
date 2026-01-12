Purpose
-------
This repository implements a small, framework-free PHP MVC app with a "feature-first" layout and a dual run-mode (Production = MySQL, Demo = CSV+Session). These instructions orient AI coding agents to the project's architecture, key patterns, and the minimal developer workflows necessary to be productive immediately.

Quick Architecture Summary
--------------------------
- Feature-first MVC: domain features live under `src/Modules/<Feature>/` (Controller, Domain, Repository, Views). See `docs/architecture.md` for the full layout.
- Core services live in `src/Core/` (Container, Router, Renderer, Database wrapper).
- Persistence implementations live in `src/Infrastructure/Persistence/` with two modes: MySQL and InMemory/Session (Dual Mode).
- Public webroot is `public/` (entry point `public/index.php`) and static assets under `public/assets/` (CSS + minimal JS). Drag-and-drop JS lives in `public/assets/js/sortable.js`.

Important Files & Data
----------------------
- Data CSVs (Demo mode): `data/achievements.csv`, `data/categories.csv`, `data/users.csv`, `data/user_achievements.csv`.
- SQL schema (Production): `sql/sql_tables.sql` — run this on your MySQL server to create schema.
- Project spec & architecture: `docs/requirements.md` and `docs/architecture.md` (read both before changing modes or persistence).
- Helper script: `csv_generator.ps1` (PowerShell) — regenerates CSV outputs used in Demo mode.

Quick Links
- `config/app.php` (mode + auth)
- `config/database.php` (MySQL creds)
- `src/Core/Container.php` (wiring + service factories)
- `src/Core/Renderer.php` (view rendering + escaping)
- `src/Infrastructure/Persistence/InMemory/` (CSV seeding + session repos)
- `src/Infrastructure/Persistence/MySQL/` (PDO-backed repos)
- `public/index.php` (front controller + routes)
- `public/assets/js/sortable.js` (drag & drop client)
- `scripts/smoke.php`, `scripts/check_csvs.php`, `scripts/seed_demo.php`

PR Checklist
- Run `php scripts/check_csvs.php` and `php scripts/smoke.php` locally.
- Confirm `config/app.php` `mode` is set correctly for the change (demo vs production).
- Ensure new persistence logic is behind repository interfaces in `src/Modules/*/Repository/`.
- Add or update views in `src/Modules/<Feature>/Views/` and use `Renderer::e()` for escaping.

Developer Workflows (concrete)
------------------------------
- Run locally (quick dev server):

  PowerShell
  ```powershell
  php -S 127.0.0.1:8000 -t public
  ```

- Switch modes: update `config/app.php` to set `'mode' => 'production'` or `'mode' => 'demo'`. In demo mode the app reads `data/*.csv` and keeps changes in session/in-memory repositories; in production it uses `config/database.php` + MySQL.
- Initialize DB: apply `sql/sql_tables.sql` to your MySQL 8 server and update `config/database.php` with credentials.
- Rebuild CSVs: run `.\csv_generator.ps1` from the repo root in PowerShell to regenerate CSV files if needed.

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

- Views are colocated with the domain: add/modify view files under `src/Modules/<Feature>/Views/` and keep layout wrappers in `templates/header.php` / `templates/footer.php`.
- Repositories:
  - Define interfaces under `src/Modules/<Feature>/Repository/`.
  - Implement concrete persistence in `src/Infrastructure/Persistence/MySQL/` and `src/Infrastructure/Persistence/InMemory/` (session-backed) so controllers remain agnostic to mode.

Integration Details & Data Flow Notes
------------------------------------
- Dual Mode: The DI container (in `src/Core/Container.php`) decides which repository implementation to inject based on `config/app.php` `mode` value. Controllers should work with interfaces only.
- Reordering UI: drag-and-drop in the browser posts to an API route (e.g., `/api/reorder`) which updates display_order via the injected repository — check `public/assets/js/sortable.js` for client behavior and follow POST->repository flow on the server.
- Security: Production mode expects Basic Auth (Auth module) and CSRF tokens on forms; Demo mode disables authentication. All HTML output should be escaped via Renderer.

What to watch for when changing code
-----------------------------------
- Keep persistence changes behind repository interfaces — do not hardcode CSV or SQL logic into controllers.
- When adding a feature, create the module skeleton under `src/Modules/<Feature>/` (Controller, Domain, Repository interface, Views). Wire concrete implementations into the DI container.
- Minimal JS allowed — keep business logic server-side. JS should be used only for UX (drag & drop, small fetch calls).

Examples & Starting Tasks for an AI Agent
-----------------------------------------
- Add a new achievement field: update the Domain entity under `src/Modules/Achievement/Domain/`, adjust the repository interface, and implement persistence in both MySQL and InMemory repos.
- Implement export pages: Master List and Roster List should render BBCode; see `docs/requirements.md` for the expected outputs and base the view markup in `src/Modules/Achievement/Views/` or `src/Modules/User/Views/`.

If anything is unclear
----------------------
- Ask for the location of `config/` files if missing, or request permission before adding external dependencies. Tell me which feature you plan to edit and I'll point to exact files to change.

End of file
