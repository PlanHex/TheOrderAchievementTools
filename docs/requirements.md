# **Project Specification: The Order Achievements Tool**

## **1. Project Overview**

Develop a web-based application to manage a custom achievement system for an internet forum. The tool allows administrators to create achievements, categorize them, and assign them to users. The primary output is generating BBCode-formatted lists ("Master List" and "User Roster List") for forum publication.
The project should be generated primarily by an AI, with minimal user supervision.

**1.1. Basic folder structure**
* `/data/`: Contains data assets (SQL dumps and CSV files) and legacy scripts.
* `/src/`: The application source code (PHP/HTML/CSS/JS). This is the folder deployed to the production web server.
* `/local/`: Scripts and Docker configuration for the local development environment.
* `/docs/`: Documentation, including requirements and architecture specifications.
* `/.github/`: Agent instructions and other github relevant files.

The root directory should only contain the `README.md`, `.gitignore`, and license files.

---

## **2. Domain Model & Data Schema**

The system relies on a relational model. All primary keys are auto-incremented integers.

| Entity | Attributes | Relationships |
| --- | --- | --- |
| **Category** | `ID`, `Name`, `Display_Order` | One-to-Many with Achievements. |
| **Achievement** | `ID`, `Title`, `Description`, `Points` (signed int), `Image_URL` (string), `Display_Order`, `Category_ID` | Many-to-One with Categories. |
| **User** | `ID`, `Name` | Many-to-Many with Achievements. |
| **User_Achievement** | `User_ID`, `Achievement_ID`, `Display_Order` | Composite PK (`User_ID` + `Achievement_ID`). Links Users to Achievements. |

**2.1 Data Integrity Constraints**
* **Unique Assignment:** A User cannot be assigned the same Achievement ID more than once.
* **Deletion:** Deleting a Category must handle orphaned Achievements (restrict or cascade). Deleting a User must cascade to `User_Achievement`.

---

## **3. Functional Requirements**

**3.1. Category Management**
* **Create:** Standard HTML form to add new categories.
* **Edit:** Modify the `Name` and `Display_Order` of existing categories.
* **Display:** List categories sorted by `Display_Order`.

**3.2. Achievement Management**
* **CRUD:** Create and edit achievements.
    * *Note:* `Image_URL` is a text input field for an external URL. This application **does not** handle file uploads.
* **Categorization:** Assign achievements to categories via dropdown.
* **Sorting:** View achievements within a category and reorder them via numeric `Display_Order` input.

**3.3. User Management**
* **CRUD:** Create and edit users (Name).
* **Assignment:** Assign achievements to users via a search interface.
    * *Search:* Filter achievements by name to select and add to the user.
* **Ordering:** Manually adjust the `Display_Order` for a user's assigned achievements.
* **Viewing:** View a specific user's achivements, showing their achievements sorted by `Display_Order`.

**3.4. Output Generation**
The system must generate two specific BBCode text outputs. The formatting logic must match the legacy scripts found in `data/scripts` and the reference files in `data/forumdata/`.
* **The Master List:** All achievements grouped by category.
* **The Roster List:** A list of all users and their respective assigned achievements.

---

## **4. Technical Constraints & Architecture**

**4.1. Technology Stack**
* **Language:** PHP 8.3.29.
    * **Strict Constraint:** No external libraries, frameworks, or package managers (Composer). Standard PHP library (SPL) only.
* **Database:** MySQL 8.0.44.
* **Frontend:** Server-Side Rendering (HTML/CSS). Minimal Vanilla JS allowed for UX enhancements (e.g., confirmation modals).

**4.2. Operating Modes & Configuration**
The application behavior is determined by a configuration variable.

| Feature | **Production Mode** | **Demo Mode** |
| --- | --- | --- |
| **Data Source** | MySQL Database | CSV Files (from `/data/`). |
| **Read Operations** | Read from SQL. | Load CSV data into PHP Session. |
| **Write Operations** | Write to SQL (Permanent). | Write to PHP Session (Ephemeral/Sandbox). |
| **Persistence** | Persistent. | Lost when session expires or browser closes. |
| **Authentication** | Basic Auth or Form Auth required. | Authentication bypassed (Auto-login as generic admin). |

**4.3. Security Requirements (Strict)**
Due to the "No Framework" constraint, the following must be manually implemented:
* **SQL Injection Prevention:** All database interaction must use **PDO Prepared Statements**. No variable interpolation in SQL strings.
* **XSS Prevention:** All user-generated content displayed in HTML or BBCode must be sanitized/escaped using `htmlspecialchars()` or equivalent.
* **CSRF Protection:** All state-changing forms (POST requests) must include and validate a manual CSRF token.
* **HTTPS:** In Production, the app is assumed to run behind a TLS-enabled proxy or server.

**4.4. Developer Experience**
* **Docker-First:** The `local/docker/` folder must contain a `docker-compose.yml` that spins up the PHP and MySQL services.
* **Workflow:** Scripts (PowerShell/Bash) in `/local/` must handle:
    1.  Building containers.
    2.  Initializing the database (importing `/data/*.sql`).
    3.  Toggling between Prod/Demo modes locally.