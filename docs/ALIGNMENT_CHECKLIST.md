# **Alignment Checklist: Code vs. Requirements & Architecture**

**Last Updated:** January 24, 2026  
**Recent Changes:** Removed drag-and-drop, replaced with numeric display_order inputs; added searchable achievement list for user assignment; clarified sorting requirements for achievements within categories.

---

## **Executive Summary**

The codebase currently **does NOT align** with the updated requirements and architecture. The main divergences are:

1. **Drag-and-Drop UI is still implemented** — must be replaced with numeric input fields for `Display_Order`
2. **sortable.js still exists** — should be removed or repurposed for searchable list functionality
3. **No dedicated view for sorting achievements within a category** — needs to be added
4. **User achievement assignment lacks searchable list UI** — needs vanilla JS search/filter implementation
5. **Copilot instructions still reference drag-and-drop** — must be updated
6. **Category view shows outdated UI** — "Sort achievements A-Z" button should be removed

---

## **Detailed Misalignments**

### **1. CATEGORY MANAGEMENT**

#### **Requirement:**
- Create: Standard HTML form to add new categories
- Edit: Modify `Name` and `Display_Order` properties only
- **Display:** Categories displayed in `Display_Order` sequence

#### **Current Implementation:**
✅ Create and edit views exist (`src/Modules/Category/Views/create.php`, `edit.php`)  
❌ **Category index view (`index.php`) shows:**
  - Drag-and-drop UI (data-id, draggable="true", drag-handle)
  - "Sort achievements A-Z" button (no longer in requirements)
  - sortable.js initialization for reordering categories

#### **What Needs to Change:**
1. Remove drag-and-drop HTML (data-id, draggable, drag-handle span)
2. Remove the "Sort achievements A-Z" button/form
3. Add numeric input fields for editing category `Display_Order` inline or link to edit form
4. Update sortable.js initialization call or remove it entirely
5. Display categories sorted by `Display_Order`

#### **Files to Update:**
- `src/Modules/Category/Views/index.php`
- `src/Modules/Category/Controller/CategoryController.php` (if needed for reorder API)

---

### **2. ACHIEVEMENT MANAGEMENT**

#### **Requirement:**
- CRUD for achievements (Title, Description, Points, Image URL)
- Assign achievements to categories via dropdown at create/edit time
- **Sorting:** View all achievements within a category and reorder them via numeric `Display_Order` input
- **Display Hierarchy:** Grouped by category in `Display_Order` sequence; achievements within each category sorted by `Display_Order`

#### **Current Implementation:**
✅ Create, edit views exist  
✅ Index view filters by category (GET param)  
✅ Index view groups achievements by category and sorts  
❌ **Missing:**
  - Dedicated **category-specific sorting view** to reorder achievements within that category
  - Numeric input UI for adjusting `Display_Order` (currently drag-and-drop)
  - Achievement index still uses drag-and-drop for reordering

#### **What Needs to Change:**
1. Create a new view: `src/Modules/Achievement/Views/sort.php` (or similar)
   - Display all achievements in a single category
   - Show numeric `Display_Order` input for each achievement
   - POST to `/achievements/{categoryId}/reorder` to save order
2. Add controller action to display sorting view and handle POST reorder
3. Remove drag-and-drop code from `src/Modules/Achievement/Views/index.php`
4. Update achievements index to NOT use sortable.js
5. Ensure index view still groups and displays correctly by category/order

#### **Files to Update:**
- `src/Modules/Achievement/Views/index.php` (remove drag-and-drop)
- `src/Modules/Achievement/Views/sort.php` (create new)
- `src/Modules/Achievement/Controller/AchievementController.php` (add sort/reorder actions)
- `public/index.php` (add routes: GET /achievements/{categoryId}/sort, POST /achievements/{categoryId}/reorder)

---

### **3. USER MANAGEMENT & ACHIEVEMENT ASSIGNMENT**

#### **Requirement:**
- CRUD: Create and edit users (Name)
- **Assignment:** Assign achievements to users via a **searchable list interface** (name-based filtering, no external libraries)
- **Ordering:** Manually adjust `Display_Order` value for each assigned achievement via numeric input
- **Viewing:** View specific user and their achievements sorted by `Display_Order`

#### **Current Implementation:**
✅ Create/store user actions exist  
✅ Show user view lists assigned achievements  
❌ **Major Gaps:**
  - No "edit user" view or action to assign/reassign achievements
  - Achievement assignment UI does not exist
  - No searchable list implementation for achieving selection
  - Show view uses drag-and-drop for user achievement reordering (should be numeric input)
  - No numeric input UI for editing user achievement `Display_Order`

#### **What Needs to Change:**
1. Create `src/Modules/User/Views/edit.php`
   - Show form with Name field
   - Add searchable achievement list:
     - Input field for search (filters by achievement name)
     - List of achievements below (vanilla JS filtering)
     - Click/button to add achievement to user
   - Show table/list of currently assigned achievements with:
     - Achievement name
     - Numeric `Display_Order` input
     - Remove button
2. Add controller actions:
   - `edit(id)` — GET to display edit form
   - `update(id)` — POST to update user name
   - `addAchievement(userId)` — POST to assign achievement to user
   - `removeAchievement(userId, achId)` — POST to unassign
   - `reorderAchievements(userId)` — POST to update `Display_Order` values
3. Create vanilla JS helper for searchable list:
   - Listen on input field changes
   - Filter achievements by name (case-insensitive substring match)
   - Display/hide items dynamically
4. Update show view to display achievements sorted by `Display_Order`
5. Remove drag-and-drop from show view

#### **Files to Create/Update:**
- `src/Modules/User/Views/edit.php` (create new)
- `src/Modules/User/Controller/UserController.php` (add edit, update, addAchievement, removeAchievement, reorderAchievements)
- `public/index.php` (add routes for edit/update/add/remove/reorder)
- `public/assets/js/search.js` (create new for searchable list) or update existing JS
- `src/Modules/User/Views/show.php` (remove drag-and-drop, sort by display_order)

---

### **4. VIEWS & DISPLAY ORDER**

#### **Requirement:**
- All views must display achievements grouped by category first, then by `Display_Order` within category
- Master List and Roster List export BBCode with same hierarchy

#### **Current Implementation:**
✅ Achievement index groups by category and sorts correctly  
⚠️ User show view displays assigned achievements but needs display_order sorting  
❌ sortable.js and drag-and-drop UIs conflict with display_order model

#### **What Needs to Change:**
1. User show view: ensure achievements are sorted by user_achievement.display_order
2. All views: replace drag-and-drop HTML with numeric input forms
3. Export views (master.php, roster.php): verify they sort by display_order

#### **Files to Update:**
- `src/Modules/User/Views/show.php`
- `src/Modules/Achievement/Views/master.php` (verify sorting)
- `src/Modules/User/Views/roster.php` (verify sorting)

---

### **5. API & REORDER ENDPOINTS**

#### **Current State:**
- `/api/reorder` endpoint exists (used by sortable.js)
- Handles POST with JSON payload { type, orders, user_id, csrf_token }
- Updates display_order in repositories

#### **What Needs to Change:**
1. Keep `/api/reorder` endpoint or repurpose for numeric input forms
2. If using form POST instead of fetch API:
   - Change reorder handlers to accept form POST data
   - Routes: `/achievements/{categoryId}/reorder`, `/users/{userId}/reorder`
   - No need for async fetch; can use standard form submission
3. If keeping fetch API for numeric input updates:
   - Adjust endpoints and payload format as needed
   - Ensure CSRF tokens are included

#### **Files to Check/Update:**
- `public/index.php` (routing)
- Core API handlers (likely in Controllers or dedicated API controller)

---

### **6. COPILOT INSTRUCTIONS**

#### **Misalignments:**
- Lines mentioning "Drag-and-drop" or "sortable.js": should reference numeric inputs and searchable lists
- "public/assets/js/sortable.js (drag & drop client)" — should update to "searchable list" or remove
- "Reordering UI: drag-and-drop in the browser posts to an API route" — should describe numeric input forms
- "Minimal JS allowed — keep business logic server-side. JS should be used only for UX (drag & drop, small fetch calls)" — should update to mention searchable list filtering

#### **What Needs to Change:**
1. Update Quick Architecture Summary to remove drag-and-drop mention
2. Update "Important Files" to remove sortable.js reference or change to searchable list
3. Update Integration Details section about reordering
4. Update "What to watch for" examples to reflect new approach

#### **Files to Update:**
- `.github/copilot-instructions.md`

---

### **7. JAVASCRIPT & ASSETS**

#### **Current State:**
- `public/assets/js/sortable.js` — drag-and-drop implementation (OBSOLETE)
- No search/filter JS for achievement selection

#### **What Needs to Change:**
1. **Option A:** Remove `sortable.js` entirely if moving to pure form-based reordering
2. **Option B:** Keep JS directory but add `search.js` for searchable list functionality
3. Create searchable list helper:
   - Filters achievement list by name
   - Uses vanilla ES6
   - No external dependencies
   - Emits click/select events for adding achievements

#### **Files to Create/Update:**
- `public/assets/js/sortable.js` (remove or repurpose)
- `public/assets/js/search.js` (create new for searchable lists)

---

### **8. ROUTES & CONTROLLERS**

#### **Missing Routes:**
1. `GET /categories/{id}/edit` — edit category (may exist)
2. `GET /achievements/{categoryId}/sort` — view sorting UI for achievements in category
3. `POST /achievements/{categoryId}/reorder` — handle reorder POST
4. `GET /users/{id}/edit` — edit user and assign achievements
5. `POST /users/{id}/update` — update user name
6. `POST /users/{id}/achievements/add` — assign achievement
7. `POST /users/{id}/achievements/{achId}/remove` — unassign achievement
8. `POST /users/{id}/achievements/reorder` — update display_order for user achievements

#### **Files to Update:**
- `public/index.php` (add new routes)
- Controllers as noted above

---

## **Priority Order for Implementation**

1. **High Priority (Core Functionality):**
   - [ ] Remove drag-and-drop from Category index view
   - [ ] Remove drag-and-drop from Achievement index view
   - [ ] Create Achievement sorting view and controller actions
   - [ ] Create User edit view and assignment UI
   - [ ] Implement searchable list for achievements
   - [ ] Add routes for new views/actions

2. **Medium Priority (User Experience):**
   - [ ] Add numeric input forms for all display_order fields
   - [ ] Ensure all views display results sorted by display_order
   - [ ] Update CSS for new form layouts
   - [ ] Test all reorder flows

3. **Low Priority (Documentation & Cleanup):**
   - [ ] Update copilot-instructions.md
   - [ ] Remove or repurpose sortable.js
   - [ ] Update any internal documentation
   - [ ] Remove unused code/endpoints

---

## **Testing Checklist**

After implementation, verify:

- [ ] Categories display in display_order sequence
- [ ] Can create and edit categories
- [ ] Can create and edit achievements
- [ ] Can view and sort achievements within a category (numeric input)
- [ ] Can create users
- [ ] Can edit users and assign achievements via searchable list
- [ ] Can remove achievements from users
- [ ] Can reorder user's achievements via numeric input
- [ ] User show view displays achievements in display_order
- [ ] Master List exports correctly sorted
- [ ] Roster List exports correctly sorted
- [ ] All CSRF tokens validate
- [ ] Both Demo and Production modes work

---

## **Deprecated Features**

These should be removed/disabled:
- ❌ Drag-and-drop UI (`draggable`, `drag-handle`, `data-id` attributes where used for DnD)
- ❌ `sortable.js` (drag-and-drop logic)
- ❌ `/api/reorder` endpoint (if not repurposed)
- ❌ "Sort achievements A-Z" button on Category view
- ❌ Bulk alphabetical sorting functionality

---

## **Summary Table**

| Component | Status | Action | Priority |
|-----------|--------|--------|----------|
| Category views | ❌ Misaligned | Remove DnD, add edit links | High |
| Achievement sorting | ❌ Missing | Create sort view + controller | High |
| Achievement index | ❌ Misaligned | Remove DnD | High |
| User assignment | ❌ Missing | Create edit view + searchable list | High |
| User show view | ⚠️ Partial | Remove DnD, ensure sorting | High |
| Routes | ❌ Missing | Add new routes for sort/edit/assign | High |
| Copilot instructions | ❌ Misaligned | Update references to DnD | Medium |
| sortable.js | ❌ Obsolete | Remove or repurpose | Medium |
| Tests | ⚠️ Unknown | Verify or create | Medium |

---

## **Notes for Future Reference**

- The numeric `Display_Order` approach is simpler and doesn't require JS for basic functionality
- Searchable list for achievement assignment is the only JS-heavy UX feature now
- All persistence logic (repository) should already support the display_order model; only views/controller/routes need updates
- Demo mode (CSV + Session) and Production mode (MySQL) should both work the same way from the controller perspective
\n---

## **9. Domain & Repository Layer Misalignments**

- **AchievementRepository (MySQL) ordering:** `src/Infrastructure/Persistence/MySQL/AchievementRepository.php` currently orders by `category_id` then `display_order`. This is misaligned with the requirement that categories themselves are ordered by `categories.display_order`. Action: update `all()` (when `categoryId` is null) to JOIN `categories` and ORDER BY `categories.display_order ASC, achievements.display_order ASC, achievements.id ASC`. (High)

- **User repository interface inconsistency:** Controllers expect to read a user's assigned achievements map (achievement_id => display_order). Implementations (`InMemory` and `MySQL`) expose `getUserAchievements()` but `src/Modules/User/Repository/UserRepositoryInterface.php` does NOT declare this method. Also there are no explicit `addAchievement` / `removeAchievement` methods on the interface. Action: add `getUserAchievements(int $userId): array`, `addAchievement(int $userId, int $achievementId, int $displayOrder = 0): void`, and `removeAchievement(int $userId, int $achievementId): void` to the `UserRepositoryInterface`, then implement them in `src/Infrastructure/Persistence/MySQL/UserRepository.php` and `src/Infrastructure/Persistence/InMemory/UserRepository.php`. Update controllers to use the interface methods (replace `method_exists` checks). (High)

- **Consistency in APIs:** `AchievementRepositoryInterface::reorder(array $orders)` is fine, but ensure repository implementations validate that supplied ids belong to the intended category when called from a category-specific reorder flow (optional defensive check). Consider adding `reorderForCategory(int $categoryId, array $orders)` if preferred. (Medium)

- **Domain objects:** Domain entities (`Achievement`, `Category`, `User`) include `displayOrder` where appropriate; no domain changes required now. Note: `User` does not include assigned-achievement data (by design) — assignment is repository-managed. (Low)

### Actions to add (repo/domain layer)

1. Update `src/Infrastructure/Persistence/MySQL/AchievementRepository.php` to JOIN `categories` and order by category display_order when returning all achievements. (High)
2. Extend `src/Modules/User/Repository/UserRepositoryInterface.php` with methods: `getUserAchievements(int $userId): array`, `addAchievement(int $userId, int $achievementId, int $displayOrder = 0): void`, `removeAchievement(int $userId, int $achievementId): void`. (High)
3. Implement those methods in `src/Infrastructure/Persistence/MySQL/UserRepository.php` and `src/Infrastructure/Persistence/InMemory/UserRepository.php`. (High)
4. Update `src/Modules/User/Controller/UserController.php` to call the new interface methods and to provide assignment/reorder APIs. Replace `method_exists` checks with typed interface usage. (High)
5. Add/adjust repository integration tests in `tests/Integration/RepositoryIntegrationTest.php` to assert ordering and user-assignment behavior (Medium)

#### Files to change (summary):
- `src/Infrastructure/Persistence/MySQL/AchievementRepository.php`
- `src/Modules/User/Repository/UserRepositoryInterface.php`
- `src/Infrastructure/Persistence/MySQL/UserRepository.php`
- `src/Infrastructure/Persistence/InMemory/UserRepository.php`
- `src/Modules/User/Controller/UserController.php`
- `tests/Integration/RepositoryIntegrationTest.php`

---
