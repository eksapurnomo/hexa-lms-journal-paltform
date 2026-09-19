# User & Course Dashboard Audit

## 1. Scope
This audit focuses on the User Dashboard, Course Dashboard, and Journal Integration following the successful implementation of JWT authentication for the SPA. It evaluates frontend components, API endpoints, UX flows, and security boundaries.

**Target Areas:**
* `Dashboard.vue`, `DashboardHome.vue`, `DashboardProfile.vue`, `DashboardCourses.vue`, `DashboardMenu.vue`
* JWT Auth Flow (Pinia & API)
* Journal Integration points
* Role and Authorization architecture

## 2. Current Authentication Flow
**Verdict: CLEAN**

* **User SPA Flow:** `/login` (Vue) → `POST /api/login` → Returns JWT → Saved in Pinia `authStore` (localStorage) → Router redirects to `/dashboard` → Axios requests include `Authorization: Bearer <token>` → Backend `auth:api` middleware resolves `auth()->user()`.
* **Admin Web Flow:** `/admin/login` → Web Session → Admin Dashboard.
* **Separation:** The two systems are completely isolated. There is no JWT → Web Session bridge, preventing session hijacking or confusion. The SPA relies strictly on the JWT token.

## 3. User Dashboard
**Verdict: FINDINGS REQUIRE FIX**

* **Route:** `/dashboard`
* **Components:** `Dashboard.vue`, `DashboardMenu.vue`, `DashboardSidebar.vue`, `DashboardProfile.vue`, `DashboardHome.vue`
* **API Endpoints:** 
  * `GET /api/enroll_summary`
  * `POST /api/profile/update` (Wait, API route is defined as `PATCH` but frontend uses `POST`)
* **Current User Resolution:** The frontend heavily relies on Pinia `authStore.userData` to render the sidebar profile image, name, and pre-fill profile forms.
* **API Requests:** It correctly utilizes `Bearer <token>` for all requests.
* **Hardcoded Data:** None found.
* **Normal User View (`tes@yaya.com`):** The dashboard attempts to load course summaries, but API endpoints are failing (see UX findings).

## 4. Course Dashboard
**Verdict: FINDINGS REQUIRE FIX**

* **Route:** Displayed within `/dashboard` as tabs ("My Courses", "In Progress", "Completed") via `DashboardCourses.vue`.
* **API Endpoint:** `GET /api/enrolled_courses`
* **Enrollment Retrieval:** Ownership is enforced securely on the backend via `auth()->user()?->enrollments()`.
* **Authorization:** Secure. It does not rely on admin sessions.
* **Safe for Empty Users:** Yes. Users without enrollments see a polite empty state ("No Course Available in Progress").
* **Cross-User Exposure:** NOT FOUND.

## 5. Journal Integration
**Verdict: OBSERVATION / NEEDS RUNTIME VERIFICATION**

Currently, the LMS Dashboard is completely unaware of the Journal platform.
* **Journal Menu/Card:** NOT FOUND. There is no entry point for Journals in `DashboardMenu.vue`.
* **Journal Entry Point:** REQUIRED. A new tab for "My Journals" or "Memberships" is needed.
* **Membership Status:** Can serve as the central hub. Users should be able to see their active roles (Member, Reviewer, Editor) per Journal.
* **Non-Members:** Should be directed to the Journal Directory (`/journals`) to apply.
* **Submission/Author Area:** Exists in Vue Router (`/author/submissions`) but is entirely disconnected from the main User Dashboard navigation.

## 6. UX Findings
**Verdict: FINDINGS REQUIRE FIX**

* **Dead Links / Unexpected API Responses [CONFIRMED]:** 
  * `GET /api/enroll_summary` and `GET /api/enrolled_courses` are called by the frontend but **do not exist** in `routes/api.php`. Because they are missing, Laravel falls back to the `/{any}` wildcard route in `web.php` and returns the SPA HTML (`<div id="app"></div>`) with an HTTP 200 OK. This causes the dashboard charts and course lists to fail silently or break the JSON parser.
* **Missing Journal Entry [CONFIRMED]:** No navigation links exist for Author Submissions or Journal Memberships.
* **Form Method Mismatch [CONFIRMED]:** `DashboardProfile.vue` uses `axios.post('/profile/update')`, but the backend route in `api.php` is defined as `Route::patch('/profile/update')`. This will likely result in a `405 Method Not Allowed`.

## 7. Security Findings
**Verdict: CLEAN**

* **IDOR:** NOT FOUND. The backend controllers (`EnrollController`, `UserController`) correctly use `auth()->id()` instead of accepting user IDs from the frontend request payload.
* **user_id injection:** NOT FOUND.
* **Trusting frontend user identity:** NOT FOUND. The backend independently verifies the JWT token.
* **Cross-user data exposure:** NOT FOUND.
* **Web Session vs JWT confusion:** NOT FOUND. Admin and SPA APIs are strictly separated.

## 8. Existing Tests
**Verdict: OBSERVATION**
The authentication and session boundary holds strong. Frontend tests are passing, but runtime API mismatches (like missing routes and HTTP method mismatches) are not caught by existing unit tests because they span the Vue-Laravel boundary.

## 9. Recommended Next Implementation
1. **Fix Missing API Routes:** Register `/enroll_summary` and `/enrolled_courses` in `routes/api.php` (mapping to `EnrollController@summary` and `EnrollController@index`).
2. **Fix Profile Update Route:** Change `Route::patch('/profile/update')` to `Route::post('/profile/update')` OR update the frontend Axios call to use PATCH/append `_method=PATCH`.
3. **Integrate Journal Dashboard:** Add a "Journal Hub" or "My Journals" tab to `DashboardMenu.vue` that links to the Journal Directory, Membership Status, and Author Submissions.

## 10. Explicit Out-of-Scope Items
* Admin Web Dashboard
* Modifying ReviewerApplication legacy tables (Phase 8 Deferred)
* Modifying Journal models or permissions

## 11. Verdict
**FINDINGS REQUIRE FIX**

### Summary:
* **Critical:** None.
* **High:** Missing API routes (`/api/enroll_summary`, `/api/enrolled_courses`) causing the dashboard data to silently fail and return HTML instead of JSON. Profile update HTTP method mismatch.
* **Medium:** Total absence of Journal navigation in the User Dashboard.
* **Low:** None.
* **Observations:** The architecture correctly treats the USER as a single canonical identity with multiple capabilities. Security posture is solid.
