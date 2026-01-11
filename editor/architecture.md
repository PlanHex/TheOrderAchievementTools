### **1. Architecture Overview**

The system relies on a **Dependency Injection (DI) Container** to handle the "Dual Mode" requirement. The Application Layer (Use Cases) never knows if it is talking to MySQL or CSV; it only relies on Interfaces.

* **Production Mode:** The Container injects `MySQLRepository` implementations.
* **Demo Mode:** The Container injects `SessionRepository` implementations (which load from CSV on start but persist to `$_SESSION`).

### **2. PHP Directory Structure**

This structure uses a **Feature-First** organization (aligning with Feature-Sliced Design). Instead of grouping by technical type (Controllers, Models), we group by Business Domain (Achievements, Users).

```text
/
├── config/                 # Configuration files
│   ├── app.php             # Toggle 'mode' => 'production' | 'demo'
│   └── database.php        # MySQL credentials
├── data/                   # Data storage for Demo mode
│   ├── achievements.csv
│   ├── categories.csv
│   └── users.csv
├── public/                 # Web root
│   ├── assets/             # CSS, JS, Images
│   ├── index.php           # Entry point (Front Controller)
│   └── .htaccess           # Rewrite rules for routing
├── src/                    # Application Source Code
│   ├── Core/               # Shared Kernel (Infrastructure agnostic)
│   │   ├── Container.php   # Simple Dependency Injection Container
│   │   ├── Router.php      # Custom Regex Router
│   │   ├── Database.php    # PDO Wrapper (Singleton)
│   │   ├── Controller.php  # Base Controller
│   │   └── View.php        # JSON/HTML Renderer
│   ├── Shared/             # Shared Domain objects
│   │   ├── Entity.php      # Base Entity class
│   │   └── ValueObject.php 
│   ├── Modules/            # FEATURE SLICES
│   │   ├── Achievement/
│   │   │   ├── Controller/      # HTTP Entry points
│   │   │   ├── Domain/          # Entity: Achievement
│   │   │   ├── UseCase/         # CreateAchievement, ReorderAchievements
│   │   │   └── Repository/      # Interface: AchievementRepository
│   │   ├── Category/
│   │   │   ├── Controller/
│   │   │   ├── Domain/
│   │   │   ├── UseCase/
│   │   │   └── Repository/
│   │   ├── User/
│   │   │   ├── Controller/
│   │   │   ├── Domain/
│   │   │   ├── UseCase/
│   │   │   └── Repository/
│   │   └── Auth/           # Basic Auth handling
│   └── Infrastructure/     # Concrete Implementations (The "Dirty" details)
│       ├── Persistence/
│       │   ├── MySQL/      # Real DB implementations
│       │   │   ├── MySQLAchievementRepository.php
│       │   │   └── ...
│       │   └── InMemory/   # Demo mode implementations
│       │       ├── CsvLoader.php
│       │       ├── SessionAchievementRepository.php
│       │       └── ...
└── templates/              # HTML Skeleton for the SPA
    └── app.html

```

---

### **3. Key Architecture Components**

#### **3.1. The "Switch" (Repository Pattern)**

This is the most critical part of the architecture to satisfy the Non-Functional Requirement of two modes.

1. **Interface (Contract):** Defined in `src/Modules/Achievement/Repository/AchievementRepositoryInterface.php`.
2. **Implementation A (Production):** `src/Infrastructure/Persistence/MySQL/MySQLAchievementRepository.php` uses raw PDO to execute SQL queries.
3. **Implementation B (Demo):** `src/Infrastructure/Persistence/InMemory/SessionAchievementRepository.php`.
* *Logic:* On `__construct`, check if `$_SESSION['achievements']` exists. If not, parse `data/achievements.csv` using `fgetcsv` and store in Session. All `save()` methods update the Session array, never the CSV file.



#### **3.2. Core Kernel (No Libraries)**

Since we cannot use Composer packages, `src/Core` must handle the heavy lifting:

* **Autoloader:** A simple `spl_autoload_register` function in `index.php` that maps namespaces to file paths (PSR-4 style).
* **Router:** A class that takes the `$_SERVER['REQUEST_URI']` and `$_SERVER['REQUEST_METHOD']`, matches it against a defined list of routes, and instantiates the correct Controller.
* **DI Container:** A simple registry where we bind interfaces to classes based on `config/app.php`.

#### **3.3. Feature-Sliced Design in PHP**

Each folder in `src/Modules/` represents a vertical slice.

* **Benefits:** If you need to change how "Users" work, you only touch the `User` folder.
* **Isolation:** The `Achievement` module should not query the `User` database table directly. It should go through the `UserRepository` interface if needed.

---

### **4. Data Flow Example**

**Scenario:** A user wants to "Create a new Achievement" in **Demo Mode**.

1. **Request:** `POST /api/achievements` hits `public/index.php`.
2. **Bootstrap:** `index.php` loads `config/app.php` and sees `'mode' => 'demo'`.
3. **Container:** The DI Container binds `AchievementRepositoryInterface` to `SessionAchievementRepository`.
4. **Routing:** Router dispatches to `Modules/Achievement/Controller/CreateController`.
5. **Use Case:** Controller calls `CreateAchievementUseCase`.
* The Use Case validates input.
* It creates a new `Achievement` Entity.
* It calls `$repository->save($achievement)`.


6. **Persistence:**
* The `SessionAchievementRepository` receives the entity.
* It pushes the data into `$_SESSION['achievements']`.
* **Crucial:** No SQL is executed, and the CSV file is untouched.


7. **Response:** Controller returns JSON `201 Created`.
