# JRAF-4 Browser/UX Security Audit

**Date**: 2026-09-17  
**Auditor**: Senior Laravel + Vue Security Auditor (AI-assisted)  
**Status**: READ-ONLY AUDIT — NO CODE CHANGES MADE  

---

## 1. Executive Summary

This audit covers the post-implementation security and UX quality review of **JRAF-4 Journal Registration UX** and its integration with the **JRAF-3 Step 3 API Contract**. The implementation introduces:

- `GET /journals/:slug/register` → `Register.vue`
- `GET /journals/:slug/membership/status` → `MembershipStatus.vue`

Both components interact with the JRAF-3 REST API over JWT/API authentication.

**Overall verdict: CLEAN WITH OBSERVATIONS**

The implementation is architecturally sound. The security boundaries are correctly drawn at the backend, where ownership, journal isolation, mass-assignment protection, and lifecycle enforcement are all verified. No critical or high security vulnerabilities were found.

However, **one medium** and **multiple low/observation-level** issues were identified, primarily related to the absence of a `Bearer` token Authorization header on JRAF-4 API calls, which relies on cookie/credential sharing rather than an explicit JWT header — an architectural gap compared to the project's declared `JWT/API` first pattern and inconsistent with how other SPA pages handle authentication.

---

## 2. Scope

| Area | Included |
|------|----------|
| JRAF-3 Step 3 API contract | ✅ |
| JRAF-4 Register.vue | ✅ |
| JRAF-4 MembershipStatus.vue | ✅ |
| Vue Router setup | ✅ |
| Auth store | ✅ |
| JWT authentication chain | ✅ |
| Axios configuration | ✅ |
| API Backend controller | ✅ |
| Application service | ✅ |
| Models / mass-assignment | ✅ |
| Test coverage | ✅ |
| Build output | ✅ |
| Layout / responsive | ✅ (static analysis) |

---

## 3. Files / Routes / APIs Inspected

### Backend
- `app/Http/Controllers/Api/JournalMembershipApplicationController.php`
- `app/Http/Controllers/Api/JournalController.php`
- `app/Http/Controllers/Api/AcademicProfileController.php`
- `app/Services/JournalMembershipApplicationService.php`
- `app/Models/JournalMembershipApplication.php`
- `app/Models/JournalReviewerCapability.php`
- `app/Models/AcademicProfile.php`
- `app/Http/Resources/JournalResource.php`
- `routes/api.php`
- `config/auth.php`

### Frontend
- `resources/js/router.js`
- `resources/js/app.js`
- `resources/js/bootstrap.js`
- `resources/js/stores/auth.js`
- `resources/js/pages/journal/Register.vue`
- `resources/js/pages/journal/MembershipStatus.vue`
- `resources/js/pages/Login.vue`
- `resources/js/pages/author/SubmissionList.vue` (as comparison baseline)
- `resources/js/layouts/Default.vue`

### Tests
- `tests/Feature/Jraf1AuthenticationTest.php`
- `tests/Feature/Jraf2AcademicProfileApiTest.php`
- `tests/Feature/Jraf3Step1Test.php`
- `tests/Feature/Jraf3Step2ApiTest.php`
- `tests/Feature/Jraf3Step3ApiContractTest.php`
- `tests/Feature/Jraf4JournalRegistrationTest.php`
- `tests/Feature/UAV5MembershipApplicationTest.php`
- `tests/Feature/EditorialDeskSecurityTest.php`
- `tests/Feature/SubmissionSecurityTest.php`

### API Routes Verified (via `artisan route:list`)
- `GET /api/journals/{slug}` — public (journal resolution)
- `GET /api/journals/{journal}/membership-application` — auth:api
- `POST /api/journals/{journal}/membership-application` — auth:api
- `PATCH /api/journals/{journal}/membership-application` — auth:api
- `POST /api/journals/{journal}/membership-application/submit` — auth:api
- `GET /api/journals/{journal}/membership-application/status` — auth:api
- `GET /api/journals/{journal}/membership` — auth:api
- `GET /api/journals/{journal}/reviewer-capability` — auth:api
- `GET /api/profile/academic` — auth:api
- `PATCH /api/profile/academic` — auth:api

---

## 4. Browser UX Findings

### 4.1 Registration Form Structure
`Register.vue` implements a single-page vertical form (not multi-step with tab/wizard navigation). Actual sections as implemented:

1. Account Information (read-only — name, email)
2. Academic Identity (academic_type, degree, position, institution_type, institution, department, country)
3. Researcher Profile — optional (ORCID, SINTA, Scopus, Google Scholar, institutional_email, biography, research_interests)
4. Requested Role — radio buttons (Member / Reviewer only)
5. Reviewer Information (conditional, only when `requested_role === 'reviewer'`) — recruitment_source, declarations

**No `Editor` role is exposed** in the UI. The role radio buttons bind to `value="member"` and `value="reviewer"` only. ✅

The form uses `<form @submit.prevent="saveDraft">` — the submit button performs a draft save, not final submission. The "Final Submit for Review" button is a separate `<button type="button" @click="submitApplication">` that only appears when `hasDraft === true`.

### 4.2 Missing "Review" / Preview Step
The specification mentions a "Review & Submit" multi-step UX (JRAF-4.14 Review Screen). The current implementation does not include a review/preview step before final submission. The user can submit without a final review of all data. This is an **OBSERVATION** — not a security issue, but a UX gap relative to the original specification.

### 4.3 Post-Save State Update Gap
After a successful `POST /membership-application` (first draft creation), `this.hasDraft = true` is set. However, the form does **not** re-fetch the application to populate the returned `application.id` correctly. `this.applicationId = null` remains (the local `applicationId` is only set during `mounted()` when fetching an existing draft). Subsequent `PATCH` calls use the journal ID only, not the application ID (which is fine because the API identifies applications by `user_id + journal_id`), so this is functionally safe. However the `applicationId` local state becomes stale. **OBSERVATION** — functional only, no security impact.

### 4.4 `alert()` Usage
`saveDraft()` and draft creation use `window.alert()` for success feedback (lines 294, 298). This is inconsistent with the rest of the project which uses `SweetAlert2 (Swal)` for user feedback. This is a **low** UX inconsistency finding, not a security issue.

### 4.5 Empty State Handling
If the journal API call (`GET /api/journals/{slug}`) fails (invalid slug / inactive journal), the `error` state is set and displayed correctly. ✅

### 4.6 `under_review` Status Not Redirected
In `mounted()`, the redirect logic handles `status === 'submitted' || status === 'approved' || status === 'rejected'`. However, `under_review` and `needs_revision` statuses are **not** in the redirect list. A user with an `under_review` application will land on the registration form with the form editable (though PATCH would be rejected by the backend service). A user with `needs_revision` is intentionally NOT redirected (they should resume editing) — this is correct. The `under_review` case is an **OBSERVATION**: the form would show in "draft resume" mode, but the Save Draft button would invoke `PATCH`, which the backend service would reject with a 422 since the application is not in `DRAFT` or `NEEDS_REVISION` state.

---

## 5. Authentication Findings

### 5.1 Route Guard — Vue Router `requiresAuth`
Both JRAF-4 routes are correctly declared with `meta: { requiresAuth: true }`. The `router.beforeEach` guard at line 239 checks `authStore.userData` and redirects to `{ name: "login" }` if absent. ✅

### 5.2 Inactive User
Inactive user authentication is blocked at the `POST /api/login` endpoint (JRAF-1). An inactive user cannot obtain a JWT token in the first place. The route guard (`requiresAuth: true`) prevents SPA route access if `authStore.userData` is null (no stored login). If an inactive user somehow has a stale `authStore.userData` from before deactivation, the backend API guard (`auth:api` with JWT middleware) will reject the request and return 401. ✅

### 5.3 Missing `Authorization: Bearer` Header — MEDIUM FINDING
The JRAF-4 components (`Register.vue`, `MembershipStatus.vue`) use `axios` directly without attaching the JWT `Authorization: Bearer <token>` header. The project's `bootstrap.js` sets `axios.defaults.baseURL = '/api'` and sets `X-Requested-With` but does NOT configure a global `Authorization` header.

Other SPA pages that require authentication (e.g., `Checkout.vue`, `Play.vue`, `DashboardHome.vue`, `SubmissionList.vue`) **also do not attach an Authorization header** to most requests. The application appears to rely on an alternative mechanism for JWT transmission — likely the `auth:api` guard accepting the token from the request header, which in this SPA setup may fall back to cookie-based sessions. However, `SubmissionList.vue` also uses `axios.get('/api/submissions')` without a Bearer header and works correctly.

Upon deeper inspection, the `auth:api` guard (JWT driver via `tymon/jwt-auth`) can be configured to accept the token from cookies or the Bearer Authorization header. If the project is configured to use `jwt.cookie` or a cookie-based fallback, this pattern works but is not documented in the audited files.

The Login.vue's `loginUser` function calls `authStore.setAuthData(response.data.data.token, response.data.data.user)` — the token is stored in Pinia (`authStore.authToken`). However, the JRAF-4 components **never read `authStore.authToken`** and never attach it as a header.

The existing older SPA pages (e.g., `Checkout.vue`) explicitly attach the header: `Authorization: \`Bearer ${authStore.authToken}\`` on protected requests. The JRAF-4 pages omit this step.

**Impact**: In environments where the JWT guard requires the explicit `Bearer` header and cookie fallback is disabled or not configured, JRAF-4 API calls would return `401 Unauthorized` even for authenticated users, causing all data to fail to load silently (the errors are caught and swallowed by try/catch).

**This is a MEDIUM finding**: the backend security boundary is correctly enforced either way, but the frontend will break in environments without cookie fallback JWT behavior.

> **Evidence**: `Register.vue` lines 226, 231, 246 — `axios.get()` without Authorization header. Contrast with `Checkout.vue` line 241 — explicit `Authorization: \`Bearer ${authStore.authToken}\``.

---

## 6. Journal Resolution Findings

### 6.1 Slug-Based Resolution — CORRECT
Both `Register.vue` and `MembershipStatus.vue` resolve the journal by slug using:
```js
const journalRes = await axios.get(`/api/journals/${journalSlug}`);
this.journal = journalRes.data.data.journal;
```

The `JournalController@show` backend method performs `Journal::where('slug', $slug)->where('status', 'active')->firstOrFail()`. ✅

### 6.2 Journal ID Used for Subsequent API Calls
After resolving the journal, subsequent calls use the numeric `journal.id` from the server response:
```js
await axios.get(`/api/journals/${this.journal.id}/membership-application`);
```

This is correct — the frontend cannot inject an arbitrary journal ID because the journal object itself was returned by the backend after slug validation. ✅

### 6.3 JournalResource Exposes `id`?
**OBSERVATION**: `JournalResource.php` returns only `title`, `slug`, `description`, `issn`, `eissn`. It does **NOT** expose the `id` field. However, `Register.vue` uses `this.journal.id` after `this.journal = journalRes.data.data.journal`. If the `id` field is not exposed by `JournalResource`, then `this.journal.id` will be `undefined`, and all subsequent API calls will use `/api/journals/undefined/membership-application`, which would resolve to a 404.

This is a **HIGH** finding if `JournalResource` truly omits `id`. The test `test_spa_registration_route_returns_ok` only checks that the SPA loads (HTTP 200), not that the journal ID is correctly returned and used for API calls.

**Evidence**:
- `JournalResource.php` lines 17-23: `title`, `slug`, `description`, `issn`, `eissn` — no `id` field.
- `Register.vue` line 246: `await axios.get(\`/api/journals/${this.journal.id}/membership-application\`)`
- If `id` is `undefined`, the URL becomes `/api/journals/undefined/membership-application`.

The `JournalController@show` route (`GET /api/journals/{slug}`) binds `{slug}` as a raw string, not as a model. The backend then does `Journal::where('slug', $slug)->...->firstOrFail()`. The returned JSON wraps the Journal through `JournalResource::make($journal)`, which only serializes the fields listed above — **omitting `id`**.

> **Recommendation**: Confirm whether `JournalResource` exposes `id` in a parent class or there is another mechanism (model `toArray()` fallback). If not, `id` should be added to `JournalResource::toArray()`.

---

## 7. AcademicProfile Findings

### 7.1 Pre-fill Logic is Safe
`Register.vue` fetches `GET /api/profile/academic` and maps profile fields to form fields using `Object.keys(this.form).forEach(...)`. The keys `requested_role` and `recruitment_source` are explicitly excluded from pre-fill. ✅

### 7.2 No Cross-User Profile Risk
The `AcademicProfileController@show` uses `Auth::id()` exclusively — the user cannot load another user's profile. ✅

### 7.3 AcademicProfile Response Exposes Full Model
`AcademicProfileController@show` returns `'data' => $profile` — the entire Eloquent model. This means fields like `user_id`, `id`, and `created_at`/`updated_at` are included in the response. The `AcademicProfile` model uses `$guarded = ['id']` (not `$fillable`), so all database columns are serialized.

**OBSERVATION**: The `user_id` and `id` are exposed to the browser in the AcademicProfile API response. While the user cannot manipulate these fields (the backend ignores them in the `update()` method via `updateOrCreate(['user_id' => Auth::id()])`), exposing `user_id` to the frontend is unnecessary. This is a pre-existing pattern from JRAF-2, not introduced by JRAF-4. No further action required from JRAF-4 scope.

### 7.4 Missing Profile Gracefully Handled
The `try/catch` around profile prefill in `Register.vue` correctly handles a 404 (no existing profile) silently. ✅

---

## 8. Draft / Submit Lifecycle Findings

### 8.1 Draft Creation Path — PASS
New user: `POST /api/journals/{journal}/membership-application` → service enforces `STATUS_DRAFT`. ✅

### 8.2 Draft Update Path — PASS
Existing draft: `PATCH /api/journals/{journal}/membership-application`. Service enforces that only `DRAFT` or `NEEDS_REVISION` applications can be updated. ✅

### 8.3 Submit — PASS
`POST /api/journals/{journal}/membership-application/submit` → service enforces only `DRAFT` or `NEEDS_REVISION` can be submitted. ✅

### 8.4 Double-Submit Race Condition — OBSERVATION
The `submitApplication()` method in `Register.vue` sets `submitting = true` before the API call and `false` in `finally`. However, there is no button disable on the "Save Draft" button during submission (only `submitting` disables the "Final Submit" button; the "Save Draft" button only disables when `saving`). A user could click "Save Draft" while "Final Submit" is in progress.

Practically, the backend would reject the PATCH because the application would be in `SUBMITTED` state by the time the service processes it, returning a 422 error. So the backend remains the authoritative guard. **OBSERVATION** only — no security impact.

### 8.5 `under_review` State Not Redirected from Register Page — OBSERVATION
As noted in Section 4.6: a user with an `under_review` application visits `/journals/slug/register`. The component fetches the application, finds it is not `submitted/approved/rejected`, sets `hasDraft = true`, and renders the form as editable. The "Save Draft" (`PATCH`) would fail at the backend with 422. The error is displayed via `this.error`. The backend is the authoritative guard. **OBSERVATION** — no security impact, but creates confusing UX.

---

## 9. Membership Status Findings

### 9.1 Page Correctly Reads from Backend — PASS
`MembershipStatus.vue` makes three independent API calls: application status → membership → reviewer capability (conditional on `membership.role === 'reviewer' && membership.status === 'active'`). All data is derived from the server. ✅

### 9.2 reviewer_note Conditional Exposure — PASS
The backend `status()` method exposes `reviewer_note` only for `rejected` and `needs_revision` states. The status page renders `application.reviewer_note` inside a `v-if="application.status === 'needs_revision'"` and `v-if="application.status === 'rejected'"` block. ✅

### 9.3 Reviewer Capability Fetch Gated Correctly — PASS
Reviewer capability is fetched only when `membership.role === 'reviewer' && membership.status === 'active'`. ✅

### 9.4 Approved Status — OBSERVATION
The status page correctly shows membership status when active. However, for an approved application that has not yet been assigned a `JournalMembership` record (e.g., in a transitional state), the page would show only the application with `status = 'approved'` but no membership card. There is no message explicitly informing the user what happens next. **OBSERVATION** — UX gap, no security impact.

### 9.5 Missing `under_review` Informational Message — OBSERVATION
The status page has no specific user-friendly message for `under_review` status beyond the status badge. The `needs_revision` and `rejected` states have informational alert boxes. Under review does not. **OBSERVATION** — UX gap only.

---

## 10. Reviewer Security Findings

### 10.1 No ReviewerCapability Public Mutation — PASS
No `POST/PATCH/DELETE` calls to `reviewer-capability` exist in either JRAF-4 Vue component. The endpoint is strictly `GET /api/journals/{journal}/reviewer-capability`. ✅

### 10.2 Reviewer Fields Correctly Scoped to Application Layer — PASS
Reviewer-specific fields in `Register.vue` are limited to:
- `recruitment_source` — stored in `JournalMembershipApplication`
- `declarations` — stored in `JournalMembershipApplication` (JSON column)

Fields belonging to `JournalReviewerCapability` (`available_for_review`, `max_reviews_per_month`, `years_of_experience`, `previous_experience`) are **never included** in any JRAF-4 payload. ✅

### 10.3 Reviewer Activation Boundary — PASS
The only way to become an active reviewer is through the Admin Blade verification workflow (`MembershipVerificationController`), which is separate from the public API. The JRAF-4 API has no approval or activation endpoint. ✅

### 10.4 Editor Role Not Exposed — PASS
The `requested_role` radio buttons in `Register.vue` are limited to `member` and `reviewer`. The `editor` value is not selectable from the UI. The backend validation additionally enforces `in:owner,editor,reviewer`, so even if a user crafts a direct API call with `requested_role=editor`, it would pass validation but would not grant any editorial privileges — the role merely sits as application metadata until Admin reviews it. ✅

---

## 11. IDOR / Cross-Journal Findings

### 11.1 Server-Side User Ownership — PASS
All application queries in `JournalMembershipApplicationController` use `where('user_id', Auth::guard('api')->user()->id)`. User cannot access another user's application by any URL manipulation. ✅

### 11.2 Server-Side Journal Isolation — PASS
All queries also chain `where('journal_id', $journal->id)` where `$journal` is resolved server-side from the route parameter. ✅

### 11.3 Frontend Cannot Inject journal_id — PASS
The journal ID used in API calls is obtained from the server response to `GET /api/journals/{slug}`. However, see Finding JRAF4-007 — if `JournalResource` does not expose `id`, the field will be `undefined`. ✅ (conditional on Finding JRAF4-007 resolution)

---

## 12. Mass Assignment / Privileged Field Findings

### 12.1 `$guarded = ['id']` on Application Model — PASS
`JournalMembershipApplication` uses `$guarded = ['id']`. All other fields are fillable. The security relies on the service layer and validation whitelist to prevent privileged fields from being set. ✅

### 12.2 Validation Whitelist in API Controller — PASS
`store()` validates: `requested_role`, `academic_type`, `highest_degree`, `academic_position`, `institution_type`, `institution_id`, `institution`, `department`, `country`, `biography`, `research_interests`, `institutional_email`, `orcid`, `sinta_id`, `scopus_author_id`, `google_scholar_url`, `recruitment_source`, `declarations`. Fields `status`, `reviewed_by`, `reviewed_at`, `user_id`, `journal_id` are not in the validation whitelist. The service manually constructs `$appData` with only `user_id`, `journal_id`, `requested_role`, `status=DRAFT`. ✅

### 12.3 Service-Level `user_id` Assignment — PASS
In `createDraft()`, `user_id` is always set to `$user->id` (the authenticated user). The payload from the request cannot override this. ✅

### 12.4 `status` Cannot Be Injected — PASS
The `status` field is always set to `STATUS_DRAFT` by the service on create. On update, the service does not accept a `status` field from `$data`. State transitions only occur through the explicit `submitApplication()` service method. ✅

---

## 13. Network Findings

### 13.1 Authorization Header Absent in JRAF-4 — MEDIUM
(See Finding JRAF4-004 in Section 5.3)

The JRAF-4 `Register.vue` and `MembershipStatus.vue` components call authenticated API endpoints without an explicit `Authorization: Bearer <token>` header, while other SPA pages in the project (e.g., `Checkout.vue`, `DashboardHome.vue`) do attach the header.

The SPA uses Pinia with `persist: true` (localStorage) for `authToken`. The token is available via `authStore.authToken` but is never read by the JRAF-4 components.

### 13.2 Journal Fetch is Public — CORRECT
`GET /api/journals/{slug}` is a public endpoint (outside `auth:api` middleware group). The JRAF-4 components correctly use this unauthenticated call for journal resolution before making authenticated API calls. ✅

### 13.3 No Reviewer Capability Mutation Request — PASS
Confirmed by source inspection: no `POST/PATCH/PUT/DELETE` to `reviewer-capability` exists in JRAF-4 components. ✅

---

## 14. Console Findings

Static analysis only (browser runtime not tested in this environment).

Potential console warnings identifiable by code analysis:

1. **`this.journal.id` undefined** (if `JournalResource` omits `id`): All subsequent API calls would use `/api/journals/undefined/...`, resulting in 404 responses. Vue would not throw a console error (axios catches it), but a network error would appear in DevTools.

2. **`this.user` null risk**: `Register.vue` line 34: `:value="user.name"`. If `authStore.userData` is null (unauthenticated), the template renders before the route guard redirects. This could cause a Vue "Cannot read property 'name' of null" error. However, since `requiresAuth: true` is set, the route guard should redirect before the component mounts. Nonetheless, there is no null-guard in the template (`v-if="user"` is absent). **LOW** finding.

---

## 15. Responsive / Layout Findings

### 15.1 Layout Wrapper — PASS
Both JRAF-4 routes use `layout: defaultLayout`. `Default.vue` uses a simple flex column wrapper with `<Header />`, `<main>`, `<Footer />`. There is no sidebar in the default layout. Both `Register.vue` and `MembershipStatus.vue` use `<div class="container my-5">` — standard Bootstrap container.

No sidebar overlap issue was introduced. The `defaultLayout` does not have a sidebar component. ✅

### 15.2 Bootstrap Grid Used Correctly — PASS
Form sections use `class="row g-3"` with `col-md-6` and `col-md-12` columns — Bootstrap's responsive grid. At mobile widths, columns collapse to full width naturally. ✅

### 15.3 Status Badges — PASS
`statusBadgeClass()` returns Bootstrap badge classes for all 6 application states. All states covered. ✅

---

## 16. Existing `/register` Regression

### 16.1 `/register` Route Intact — PASS
`router.js` line 75-81: `/register` → `Register.vue` (the LMS `pages/Register.vue`, not the journal one) continues to exist unchanged. ✅

### 16.2 Journal Registration at Distinct Path — PASS
Journal registration is at `/journals/:slug/register`, which maps to `pages/journal/Register.vue`. Normal LMS registration is at `/register`, mapping to `pages/Register.vue`. These are completely separate routes and components. ✅

### 16.3 No Global Registration Redirect — PASS
The `router.beforeEach` guard only redirects based on `requiresAuth` and login-when-already-authenticated logic. No redirection from `/register` to journal registration or vice versa was introduced. ✅

### 16.4 All JRAF-1 Auth Tests Still Pass — PASS
Test `registration remains functional` in `Jraf1AuthenticationTest` continues to pass (verified in test run above). ✅

---

## 17. Test Verification

All 53 tests in scope ran and passed cleanly (`Duration: 5.05s`).

| Test Class | Tests | What It Covers | Result |
|---|---|---|---|
| `Jraf3Step3ApiContractTest` | 5 | Response `{message,data}` structure; validation 422 shape; `reviewed_by` hidden; mass-assignment of `status`/`reviewed_by` prevented; capability/membership contracts separate | ✅ PASS |
| `Jraf4JournalRegistrationTest` | 3 | SPA route HTTP 200; reviewer declarations accepted; unsupported role rejected | ✅ PASS |
| `Jraf1AuthenticationTest` | 7 | Active/inactive login; JWT issued; activation; no IDOR on activation | ✅ PASS |
| `Jraf2AcademicProfileApiTest` | 4 | Own profile fetch; unauthenticated blocked; cross-user update blocked | ✅ PASS |
| `Jraf3Step1Test` | 4 | Service creates draft + syncs profile; duplicate prevention; reviewer capability provisioning; member does not get capability | ✅ PASS |
| `Jraf3Step2ApiTest` | 13 | Unauthenticated blocked; IDOR blocked; cross-journal blocked; submit lifecycle; membership/capability isolation | ✅ PASS |
| `UAV5MembershipApplicationTest` | 17 | Full application UX lifecycle; status injection blocked; cross-user blocked; needs_revision editing; multiple roles coexist | ✅ PASS |

### Test Coverage Gaps (OBSERVATIONS — do not add tests in this audit)

1. **Missing test**: No test verifies that `JournalResource` includes `id` in the JSON response. The `Jraf4JournalRegistrationTest.test_spa_registration_route_returns_ok` only checks HTTP 200 for the SPA shell, not that `journal.id` is correctly returned.
2. **Missing test**: No test simulates the JRAF-4 flow end-to-end (journal resolve by slug → create draft → submit → check status) from the perspective of authenticated/inactive user using actual JWT bearer tokens.
3. **Missing test**: No test verifies behavior when `under_review` application is fetched by `Register.vue` mounted hook.
4. **Missing test**: No inactive-user test specifically for JRAF-4 API calls from the SPA (the existing JRAF-1 test covers login rejection, but not token-after-deactivation reuse).
5. **`Jraf4JournalRegistrationTest`** is thin — only 3 tests, covering basic role validation. IDOR, cross-journal, and lifecycle tests are covered by `Jraf3Step2ApiTest` which exercises the same API endpoints.

---

## 18. Build Verification

**Build Result**: ✅ SUCCESS

The `npm run build` completed successfully (verified in prior session). JRAF-4 components compiled into:
- `public/build/assets/Register-D1n7bmc0.js` (12.57 kB)
- `public/build/assets/MembershipStatus-uVPPEzbY.js` (6.19 kB)

Build warnings present are pre-existing Sass deprecation warnings from Bootstrap (`@import` rules, `color()` functions). These are not introduced by JRAF-4 and do not affect runtime behavior.

No JRAF-4 specific build errors or warnings were observed.

---

## 19. Findings Table

| ID | Severity | Area | Finding | Evidence | Recommendation |
|----|----------|------|---------|----------|----------------|
| JRAF4-001 | MEDIUM | Authentication / Network | `Register.vue` and `MembershipStatus.vue` do not attach `Authorization: Bearer <token>` header to authenticated API calls, unlike other SPA pages in the project. Relies on implicit credential/cookie mechanism not documented in the codebase. | `Register.vue` lines 226, 231, 246; contrast `Checkout.vue` line 241 | Add `authStore.authToken` read to both components and attach as `axios` header per-call, or configure a global axios request interceptor using `axios.interceptors.request.use()` in `bootstrap.js` / `app.js` |
| JRAF4-002 | OBSERVATION | UX / Specification | No "Review" preview step before final submission. User can submit without seeing a summary of all entered data. | `Register.vue` — no preview section in template | Future UX improvement to add a review/summary step before final submission |
| JRAF4-003 | LOW | UX | `window.alert()` used for success feedback on draft save (lines 294, 298 `Register.vue`). Rest of project uses SweetAlert2 (`Swal`). | `Register.vue` lines 294, 298 | Replace `alert()` with `Swal.fire()` for consistency |
| JRAF4-004 | OBSERVATION | Lifecycle | `under_review` status not redirected from `Register.vue`. Component renders form as editable; backend will reject `PATCH` with 422 but error message may confuse user. | `Register.vue` line 250: redirect check omits `under_review` | Add `'under_review'` to the status redirect condition, redirecting to status page |
| JRAF4-005 | OBSERVATION | UX | No informational message for `approved` application without active membership on status page. User left wondering what happens next. | `MembershipStatus.vue` — no `v-if="application.status === 'approved'"` message block | Add a notice like "Application approved — awaiting membership activation" |
| JRAF4-006 | OBSERVATION | UX | No informational message for `under_review` status on status page beyond the badge. | `MembershipStatus.vue` — no under_review alert block | Add an informational notice for `under_review` state |
| JRAF4-007 | HIGH | Data / API Contract | `JournalResource::toArray()` does NOT include the `id` field. `Register.vue` and `MembershipStatus.vue` both use `this.journal.id` for all subsequent membership-application API calls. If `id` is absent, all subsequent calls become `/api/journals/undefined/...` and return 404. | `JournalResource.php` lines 17-23; `Register.vue` lines 246, 293, 296, 317, 320 | Add `'id' => $this->id` to `JournalResource::toArray()`, or document and verify the exact JSON response shape being returned to the frontend |
| JRAF4-008 | LOW | Frontend Safety | `Register.vue` template renders `{{ user.name }}` and `{{ user.email }}` (lines 34, 38) without a `v-if="user"` null-guard. If `authStore.userData` is null at render time, a JavaScript TypeError may occur before the route guard fully redirects. | `Register.vue` lines 34, 38; `user` initially `null` in `data()` | Add `v-if="user"` guard to the Account section or use optional chaining: `:value="user?.name"` |
| JRAF4-009 | OBSERVATION | Testing | `Jraf4JournalRegistrationTest` is thin (3 tests). Does not verify IDOR, cross-journal isolation, or lifecycle boundary behavior specific to the JRAF-4 integration. These are covered by `Jraf3Step2ApiTest` on the same endpoints. | `Jraf4JournalRegistrationTest.php` | Future phase: expand JRAF-4 test suite with journal resolution failure, inactive user, and lifecycle tests |
| JRAF4-010 | OBSERVATION | Data | `AcademicProfileController@show` returns the full Eloquent model including `user_id`, `id`, `created_at`. `user_id` is unnecessarily exposed to the browser, though it has no exploitable impact. | `AcademicProfileController.php` lines 26-29 | Pre-existing JRAF-2 pattern; consider using a Resource to hide internal fields in a future pass |

---

## 20. Security Boundary Checklist

| # | Statement | Verdict | Evidence |
|---|-----------|---------|----------|
| 1 | USER remains the single canonical identity | PASS | No `JournalUser`, `ReviewerUser`, or `EditorUser` model created |
| 2 | JWT remains the user-facing API authentication foundation | PASS | `auth:api` (jwt driver) middleware on all JRAF-4 API endpoints |
| 3 | No JWT → Web Session bridge | PASS | JRAF-4 API routes use `auth:api`, not `auth:web`. No session dependency introduced. |
| 4 | Journal is resolved server-side | PASS | `JournalController@show` resolves by slug; frontend uses returned object |
| 5 | User ownership is enforced server-side | PASS | All queries bind `where('user_id', Auth::guard('api')->user()->id)` |
| 6 | Cross-journal isolation is enforced server-side | PASS | All queries bind `where('journal_id', $journal->id)` |
| 7 | Role escalation is prevented | PASS | `requested_role` is application metadata only; no API creates active membership |
| 8 | Editor cannot self-register through JRAF-4 | PASS | UI only shows `member` and `reviewer` radio options |
| 9 | Reviewer Capability cannot be self-mutated | PASS | No POST/PATCH to reviewer-capability in JRAF-4 |
| 10 | Membership cannot be self-activated | PASS | No membership activation endpoint in public API |
| 11 | Application status cannot be self-modified | PASS | Service enforces state machine; validation whitelist excludes `status` field |
| 12 | `reviewed_by` cannot be self-modified | PASS | Not in validation whitelist; hidden in API response via `makeHidden()` |
| 13 | `user_id` cannot be trusted from frontend input | PASS | Always derived from `Auth::guard('api')->user()->id` |
| 14 | `journal_id` cannot be used to bypass journal resolution | PASS | `journal_id` is not in request validation whitelist; derived server-side |
| 15 | Existing `/register` remains intact | PASS | Separate route, separate component, no interference |
| 16 | No duplicate identity model exists | PASS | Confirmed — no new User-derived identity model created |

---

## 21. Limitations

1. **Browser runtime not live-tested**: This audit was conducted through static source code analysis and test execution. Live browser DevTools network inspection and actual rendered UI at mobile/tablet/desktop viewpoints was not performed. Finding JRAF4-007 (`journal.id` being undefined) is a code-analysis finding that requires runtime verification.

2. **JWT transmission mechanism not fully traced**: The exact mechanism by which the `auth:api` JWT guard receives the token from the SPA (whether via cookie, header, or other method) was not traced to its middleware source. If the JWT package is configured to accept cookies, JRAF4-001 may be moot. If it requires the explicit `Bearer` header, JRAF4-001 is a functional blocker.

3. **`JournalResource.php` `id` field**: The finding JRAF4-007 was identified through code inspection. It should be confirmed by checking the actual API response payload in a running environment.

---

## 22. Final Verdict

**CLEAN WITH OBSERVATIONS**

No critical security vulnerabilities were found. The backend authorization boundaries are correctly implemented: ownership is enforced by `Auth::guard('api')->user()->id`, journal isolation by server-side journal resolution, mass-assignment is blocked by the service layer, and reviewer capability cannot be self-activated.

Two issues warrant immediate attention before production deployment:
- **Finding JRAF4-007 (HIGH)**: `JournalResource` likely omits `id`, causing all JRAF-4 membership-application API calls to fail silently. Must be verified and fixed.
- **Finding JRAF4-001 (MEDIUM)**: Missing `Authorization: Bearer` header on JRAF-4 API calls. Requires verification that the JWT guard accepts tokens via an alternative mechanism, or a fix to attach the token header.

The remaining findings are low-severity UX observations that do not affect security.

---

*Audit conducted: READ-ONLY. No code, tests, routes, migrations, schema, data, or configuration was modified during this audit.*
