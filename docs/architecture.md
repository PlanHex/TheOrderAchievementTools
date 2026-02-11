# **1. Architecture Overview**

The system is a **Traditional MVC (Model-View-Controller)** application designed for portability and "No Framework" constraints.
* **The Controller:** Handles the request, interacts with the Domain Layer, and selects a View (HTML or Text).
* **The View:** A simple PHP file that renders HTML (or BBCode) using data provided by the Controller.
* **The Model:** Managed via Repositories injected by the DI Container.

**Dual Mode Strategy:**
The "Dual Mode" (MySQL vs. Session/CSV) is handled entirely in the `src/Infrastructure` layer and `src/Core/Auth` service.
* **Persistence:** Controllers use interfaces (`RepositoryInterface`). The Container injects either SQL-backed or Session-backed implementations based on config.
* **Authentication:** The Auth Service checks the mode. In 'Demo', it bypasses password checks and auto-logs in a generic admin.

---

# **2. PHP Directory Structure**

To comply with strict deployment requirements, the **entire application** resides within the `src/` directory.

```text
/
├── data/                   # Data assets (SQL dumps, CSVs for Demo mode)
├── docs/                   # Documentation
├── local/                  # Local dev environment (Docker, Scripts)
├── src/                    # DEPLOYABLE ARTIFACT
│   ├── Config/             # Configuration
│   │   ├── app.php         # Mode toggle ('production' | 'demo')
│   │   └── database.php    # DB Credentials
│   ├── Core/               # Shared Kernel
│   │   ├── Auth.php        # Authentication Service (Auto-login logic)
│   │   ├── Container.php   # Dependency Injection
│   │   ├── Csrf.php        # CSRF Token Generator/Validator
│   │   ├── Router.php      # Maps URL -> Controller
│   │   ├── Renderer.php    # View Helper
│   │   └── Database.php    # PDO Wrapper
│   ├── Modules/            # FEATURE SLICES
│   │   ├── Achievement/    # Achievement Management
│   │   ├── Category/       # Category Management
│   │   ├── User/           # User & Roster Management
│   │   ├── Reports/        # BBCode Export Logic
│   │   │   ├── Controller/
│   │   │   └── Views/      # Text templates for BBCode
│   │   └── Auth/           # Login Controller
│   ├── Infrastructure/     # Concrete Implementations
│   │   ├── Persistence/
│   │   │   ├── MySQL/      # Real DB implementations
│   │   │   └── InMemory/   # Demo (Session/CSV) implementations
│   ├── Templates/          # Global HTML Layouts (Header/Footer)
│   └── public/             # Web Entry Point
│       ├── assets/         # CSS/JS
│       ├── index.php       # Front Controller
│       └── .htaccess       # Routing
└── README.md

```

---

# **3. Key Architecture Components**

## **3.1. The Renderer (View Engine)**

Instead of returning JSON, Controllers use a `Renderer` class.

* **HTML Mode:** Wraps content in `src/Templates/header.php` and `footer.php`.
* **Raw Mode (BBCode):** Used by the `Reports` module to render plain text responses without HTML layouts.

## **3.2. Security Layer (Core)**

* **CSRF Protection:** The `Core/Csrf.php` class generates a `$_SESSION` token.
* **Generation:** Injected into every `<form>` as a hidden input.
* **Validation:** The Router or Base Controller validates the token on every `POST` request before executing the action.


* **Sanitization:** All distinct outputs use `htmlspecialchars()` to prevent XSS.

## **3.3. Display Order Management**

Display order is managed via numeric `Display_Order` fields:

* **Global Sorting:** Repositories always return lists sorted by `Display_Order ASC`.
* **Reordering:** Users input integers in standard input fields to re-rank items.

## **3.4. Achievement Assignment**

* **Many-to-Many Logic:** Handled in the `User` module.
* **UI:** A dedicated view allows searching for achievements (JS filter) and adding them to a user.
* **Schema:** The `User_Achievement` composite relationship is managed by the `UserRepository`.

## **3.5. Reporting (BBCode Generation)**

A dedicated **Reports Module** handles the forum export requirements.

* **Master List:** Aggregates all Categories and Achievements.
* **Roster List:** Aggregates all User's achievements.
* **Output:** Renders a specific `text/plain` View optimized for forum copy-pasting.

---

# **4. Data Flow Example**

**Scenario:** A user wants to "Create a new Achievement" in **Demo Mode**.

1. **Request:** User submits `<form action="/achievements/store" method="POST">`.
2. **Bootstrap:** `src/public/index.php` loads `src/Config/app.php` (`'mode' => 'demo'`).
3. **Security Check:**
* Router intercepts POST.
* `Csrf::validate($_POST['csrf_token'])` verifies the session token.


4. **Container:** Injects `InMemoryAchievementRepository`.
5. **Action:**
* Controller validates data.
* Calls `$repository->save($entity)`.


6. **Persistence:**
* Repository updates `$_SESSION['achievements']` (CSV data loaded into session).


7. **Response:** Redirects to `/achievements`.
