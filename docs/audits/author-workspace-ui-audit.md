# Author Workspace UI Audit

## Current Route Configuration
The current router (`resources/js/router.js`) configures the Author routes as standalone pages at the root level using the `defaultLayout`:
- `/author/submissions` → `SubmissionList.vue`
- `/author/submissions/create` → `SubmissionForm.vue`
- `/author/submissions/:id` → `SubmissionDetail.vue`

## Current Layout
The `defaultLayout` (`resources/js/layouts/Default.vue`) provides only a global top `Header` and bottom `Footer`. It lacks a sidebar, nested navigation, or a workspace-specific shell.

## Reusable Components Found
- **Layout Pattern:** The `DashboardJournalWorkspace.vue` provides a clean, responsive layout structure (left sidebar `col-4 d-none d-xl-block col-xl-3`, main content `col-xl-9`) that can be used as a structural reference.
- **Header/Footer:** The global Header and Footer can still wrap the application.
- **Sidebar Shell:** `DashboardSidebar.vue` provides a good visual reference for displaying the user's avatar and name, which can be adapted.
- **Cannot directly reuse:** `DashboardMenu.vue` and `DashboardSidebar.vue` cannot be directly reused because they are tightly coupled to the LMS Dashboard features (Courses, Certificates, Payments, Bootstrap pills/tabs) and are not standard Vue Router navigation links.

## API Dependencies
- `GET /api/submissions` (Paginated list of user submissions)
- `GET /api/submissions/{id}` (Single submission details)
- `POST /api/submissions` (Draft creation)
- `POST /api/submissions/{id}/files` (Manuscript upload)
- `POST /api/submissions/{id}/submit` (Final submission)
- `GET /api/journals` (List of active journals for the create form)
- Existing authentication mechanism via `useAuthStore()` and JWT.

## Recommended Minimal Implementation
1. **Create `AuthorWorkspace.vue`**: A new shell component in `resources/js/pages/author/` that defines the responsive grid (Sidebar + Main Content area with a `<router-view>` slot).
2. **Create `AuthorSidebar.vue` and `AuthorMenu.vue`**: Place in `resources/js/components/` to render the Author-specific navigation (Overview, Submissions, Journals, Account links, Back to Dashboard). Use Vue Router `<router-link>` with active class binding.
3. **Refactor `router.js`**: Group the Author routes as `children` under the new `/author` parent route which points to `AuthorWorkspace.vue`. This ensures the sidebar is preserved while navigating.
4. **Create `Overview.vue` and `Journals.vue`**: Stub minimal components for the new `/author` (Overview) and `/author/journals` routes, utilizing existing API data where easily available, or displaying a placeholder if not.
5. **Move Existing Components**: The existing `SubmissionList`, `SubmissionForm`, and `SubmissionDetail` remain functionally intact but will now render inside the `<router-view>` of the `AuthorWorkspace`.

## Files that should NOT be modified
- Backend: Controllers, Repositories, Policies, Models, Migrations (e.g., `SubmissionController.php`, `SubmissionPolicy.php`).
- Existing Admin/Journal Management Dashboard components (`Dashboard.vue`, `DashboardJournalWorkspace.vue`, `DashboardMenu.vue`).
- Authentication API or JWT mechanisms.
