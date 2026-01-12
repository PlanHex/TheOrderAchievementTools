# **1. Architecture Overview**

The system is a **Traditional MVC (Model-View-Controller)** application.
* **The Controller:** Handles the request, interacts with the Domain Layer, and selects a View.
* **The View:** A simple PHP file that renders HTML using data provided by the Controller.
* **The Model:** Managed via Repositories injected by the DI Container.

**Dual Mode Strategy:**
The "Dual Mode" (MySQL vs. Session/CSV) is handled entirely in the `src/Infrastructure` layer. The Controllers and Views do not know which mode is active.

# **2. PHP Directory Structure**

This structure uses a **Feature-First** organization. Views (HTML templates) are co-located with their relevant domain logic, rather than hidden in a global `templates` folder.

```text
/
├── config/                 # Configuration files
│   ├── app.php             # Toggle 'mode' => 'production' | 'demo'
│   └── database.php        # MySQL credentials
├── data/                   # Data storage for Demo mode (CSV)
├── public/                 # Web root
│   ├── assets/             
│   │   ├── css/            # global.css, layout.css
│   │   └── js/             # sortable.js (Drag & Drop logic only)
│   ├── index.php           # Entry point (Front Controller)
│   └── .htaccess           # Routing rules
├── src/                    # Application Source Code
│   ├── Core/               # Shared Kernel
│   │   ├── Container.php   # Dependency Injection
│   │   ├── Router.php      # Maps URL -> Controller
│   │   ├── Renderer.php    # Helper to include View files safely
│   │   └── Database.php    # PDO Wrapper
│   ├── Modules/            # FEATURE SLICES
│   │   ├── Achievement/
│   │   │   ├── Controller/      # AchievementController.php
│   │   │   ├── Domain/          # Entity: Achievement
│   │   │   ├── Repository/      # Interface: AchievementRepository
│   │   │   └── Views/           # HTML Templates
│   │   │       ├── index.php    # List all achievements
│   │   │       ├── create.php   # Form to create
│   │   │       └── edit.php     # Form to edit
│   │   ├── Category/
│   │   │   ├── Controller/
│   │   │   ├── Domain/
│   │   │   ├── Repository/
│   │   │   └── Views/           # index.php (Reorder UI)
│   │   ├── User/
│   │   │   ├── Controller/
│   │   │   ├── Domain/
│   │   │   ├── Repository/
│   │   │   └── Views/           # show.php (User Profile)
│   │   └── Auth/           
│   └── Infrastructure/     # Concrete Implementations
│       ├── Persistence/
│       │   ├── MySQL/      # Real DB implementations
│       │   └── InMemory/   # Demo (Session/CSV) implementations
└── templates/              # Global Shared Layouts
    ├── header.php          # Navigation & <head>
    └── footer.php          # Scripts & </body>
```

---

# **3. Key Architecture Components**

## **3.1. The Renderer (View Engine)**

Instead of returning JSON, Controllers use a `Renderer` class.

* **Role:** Extracts data arrays into PHP variables and includes the specific View file.
* **Layouts:** Wraps the specific View content within `templates/header.php` and `templates/footer.php` automatically.

```php
// Example usage in Controller
return $this->renderer->render('Modules/Achievement/Views/index', [
    'achievements' => $achievements,
    'title' => 'All Achievements'
]);

```

## **3.2. Routing & POST-Redirect-GET**

To prevent form resubmission issues and keep the flow simple:

1. **GET /achievements/create:** Displays the HTML form.
2. **POST /achievements/store:**
* Controller accepts data.
* Calls `$repository->save($entity)`.
* **Redirects** to `/achievements` (HTTP 302).


3. **GET /achievements:** Displays the updated list.

## **3.3. Hybrid Frontend (Drag & Drop)**

While most pages are static HTML, "Reordering" requires JavaScript for a good UX.

* **The View:** Renders a list with `data-id` attributes.
* **The JS:** `public/assets/js/sortable.js` listens for drag events.
* **The Interaction:**
1. User drops item.
2. JS sends an asynchronous `POST /api/reorder` (fetch API).
3. Backend updates the order in DB/Session.
4. JS displays a small "Saved" toast notification (no page reload needed).



---

# **4. Data Flow Example**

**Scenario:** A user wants to "Create a new Achievement" in **Demo Mode**.

1. **Request:** User submits `<form action="/achievements/store" method="POST">`.
2. **Bootstrap:** `index.php` loads config, sees `'mode' => 'demo'`.
3. **Container:** Injects `SessionAchievementRepository` into the Controller.
4. **Action:**
* Controller validates `$_POST` data.
* Creates `Achievement` Entity.
* Calls `$repository->save($achievement)`.


5. **Persistence:**
* Repository updates `$_SESSION['achievements']`.
* (No SQL executed, CSV untouched).


6. **Response:**
* Controller sends header: `Location: /achievements`.
* Browser follows redirect and loads the List View with the new item visible.