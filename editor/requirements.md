# **Project Specification: The Order Achievements Tool**

## **1. Project Overview**

Develop a Single Page Application (SPA) to manage a custom achievement system for an internet forum. The tool allows administrators to create achievements, categorize them, and assign them to users. The primary output is generating BBCode-formatted lists ("Master List" and "Roster List") for forum publication.

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

* **Create/Edit:** Add new categories or rename existing ones.
* **Ordering:**
* Adjust the global display order of categories.
* Reorder achievements manually within a specific category.
* **Bulk Action:** Sort all achievements within a category alphabetically by Title.



**3.2. Achievement Management**

* **CRUD:** Create and edit achievements (Title, Description, Points, Image URL).
* **Categorization:** Move achievements between categories.
* **Viewing:** Filter and view achievements by category.

**3.3. User Management**

* **CRUD:** Create and edit users (Name).
* **Assignment:** Assign specific achievements to users.
* **Ordering:** Manually adjust the display order of a user’s assigned achievements.
* **Viewing:** View a specific user and their list of achievements.

**3.4. Output Generation**
The system must generate two specific text outputs (likely BBCode):

* **The Master List:** All achievements grouped by category, respecting category and achievement sort orders.
* **The Roster List:** A list per user showing their assigned achievements, respecting the user-specific assignment order.

---

## **4. Technical Architecture & Constraints**

**4.1. Technology Stack**

* **Language:** PHP 8.3.29 (Strictly **no external libraries** or frameworks).
* **Database:** MySQL 8.0.44.
* **Frontend:** Single Page Application (SPA).

**4.2. Architecture Patterns**

* Must adhere to high-quality industry standards: Clean Architecture, Domain-Driven Design (DDD), SOLID principles, and Feature-Sliced Design.

**4.3. Operating Modes**
The application must support two distinct environments via configuration:

| Feature | **Production Mode** | **Demo Mode** |
| --- | --- | --- |
| **Data Source** | MySQL Database (Read/Write) | CSV Files (Read-only on load) |
| **Persistence** | Permanent (SQL) | Temporary (In-memory/Session only). Changes are not saved to CSV. |
| **Security** | Basic Authentication required. | Authentication disabled (Free access). |

**4.4. Security**

* **Authentication:** Basic Authentication for login (Production only).
* **Authorization:** Full access to all features once logged in (or immediately in Demo mode).