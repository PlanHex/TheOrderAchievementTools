# **Project Specification: The Order Achievements Tool**

## **1. Project Overview**

Develop a web-based application to manage a custom achievement system for an internet forum. The tool allows administrators to create achievements, categorize them, and assign them to users. The primary output is generating BBCode-formatted lists ("Master List" and "Roster List") for forum publication.

---

## **2. Domain Model & Data Schema**

The system relies on a relational model consisting of three core entities and one association table. All primary keys are auto-incremented integers.

| Entity | Attributes | Relationships |
| --- | --- | --- |
| **Category** | `ID`, `Name`, `Display_Order` | One-to-Many with Achievements. |
| **Achievement** | `ID`, `Title`, `Description`, `Points` (signed int), `Image_URL`, `Display_Order`, `Category_ID` | Many-to-One with Categories. |
| **User** | `ID`, `Name` | Many-to-Many with Achievements. |
| **User_Achievement** | `User_ID`, `Achievement_ID`, `Display_Order` | Composite PK. Links Users to Achievements with a specific sort order. |

---

## **3. Functional Requirements**

**3.1. Category Management**

* **Create:** Standard HTML form to add new categories.
* **Edit:** Modify the `Name` and `Display_Order` properties of existing categories.
* **Display:** Categories are displayed in `Display_Order` sequence. Achievements within each category are displayed in their respective `Display_Order` sequence.

**3.2. Achievement Management**

* **CRUD:** Create and edit achievements (Title, Description, Points, Image URL).
* **Categorization:** Assign achievements to categories via dropdown selection at create/edit time.
* **Sorting:** View all achievements within a category and reorder them via numeric `Display_Order` input.
* **Display Hierarchy:** Achievements are displayed grouped by category in `Display_Order` sequence, with achievements within each category sorted by their `Display_Order`.

**3.3. User Management**

* **CRUD:** Create and edit users (Name).
* **Assignment:** Assign achievements to users via a searchable list interface (name-based filtering, no external libraries).
* **Ordering:** Manually adjust the `Display_Order` value for each of a user's assigned achievements via numeric input.
* **Viewing:** View a specific user and their assigned achievements sorted by `Display_Order`.

**3.4. Output Generation**
The system must generate two specific text outputs (BBCode):

* **The Master List:** A dedicated page displaying all achievements grouped by category as raw text for easy copying.
* **The Roster List:** A dedicated page per user showing their assigned achievements as raw text.

---

## **4. Technical Architecture & Constraints**

**4.1. Technology Stack**

* **Language:** PHP 8.3.29 (Strictly **no external libraries** or frameworks).
* **Database:** MySQL 8.0.44.
* **Frontend:** Standard Server-Side Rendering (HTML/CSS).
    * **JavaScript:** Minimal Vanilla JS (ES6) used for UI enhancements (e.g., searchable lists).
    * **CSS:** Custom CSS (No frameworks like Bootstrap).

**4.2. Architecture Patterns**

* **MVC (Model-View-Controller):** Logic and Presentation must be separated.
* **Feature-Sliced Design:** Code should be organized by domain (User, Achievement) rather than technical layer (Model, View).
* **Dependency Injection:** Used to manage the "Dual Mode" requirement.

**4.3. Operating Modes**
The application must support two distinct environments via configuration:

| Feature | **Production Mode** | **Demo Mode** |
| --- | --- | --- |
| **Data Source** | MySQL Database (Read/Write) | CSV Files (Read-only on load) |
| **Persistence** | Permanent (SQL) | Temporary (In-memory/Session only). |
| **Security** | Basic Authentication required. | Authentication disabled (Free access). |

**4.4. Security**

* **Authentication:** Basic Authentication for login (Production only).
* **XSS Protection:** All output in HTML templates must be properly escaped.
* **CSRF Protection:** Forms must include anti-CSRF tokens.