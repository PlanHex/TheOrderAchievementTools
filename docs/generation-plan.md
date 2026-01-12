# Generation Plan — The Order Achievements Tool

Purpose: make the repo AI-driven by providing a repeatable, file-by-file generation plan and explicit next steps for implementation.

High-level phases

1. Scaffold (this commit)
   - Create `config/app.php`, `config/database.php`.
   - Add `public/index.php` as the front controller placeholder.
   - Add `templates/header.php` and `templates/footer.php`.

2. Core services
   - Implement `src/Core/Container.php`, `Router.php`, `Renderer.php`, `Database.php`.
   - Keep interfaces small and testable.

3. Domain & repositories
   - Add Domain entities and repository interfaces for `Category`, `Achievement`, `User`.
   - Define required methods: `all()`, `find($id)`, `save($entity)`, `delete($id)`, `reorder(array $orders)`.

4. Persistence implementations
   - InMemory (Demo): CSV loader + Session-backed repos under `src/Infrastructure/Persistence/InMemory/`.
   - MySQL (Production): PDO-backed repos under `src/Infrastructure/Persistence/MySQL/` using `sql/sql_tables.sql`.

5. Controllers, Views, and API
   - Implement CRUD controllers and views under `src/Modules/*`.
   - Add `/api/reorder`, `/export/master`, `/export/roster?user_id=` endpoints.

6. Security + UX
   - Basic Auth for production, CSRF tokens on all forms, output escaping in Renderer.
   - `public/assets/js/sortable.js` for drag-and-drop reorder; fetch to `/api/reorder`.

7. Tests, docs, and CI
   - Add light smoke tests (PHP scripts) and update `README.md` with run steps.

Local dev commands

```powershell
php -S 127.0.0.1:8000 -t public
.\csv_generator.ps1    # regenerate CSVs (Windows PowerShell)
```

Notes for AI agents

- Always prefer adding or modifying files under `src/` and keep changes behind repository interfaces.
- Use `config/app.php` `mode` flag to branch behavior (demo vs production).
- For views, use `Renderer` (yet to implement) so layout and escaping are centralised.

Next action: implement Core services (Container, Router, Renderer, Database).

End of plan.
