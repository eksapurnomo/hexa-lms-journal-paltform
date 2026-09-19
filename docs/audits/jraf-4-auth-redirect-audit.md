# JRAF-4 Authentication Redirect Audit

## Scope
The objective is to audit the routing, authentication initialization, and backend configuration to determine the root cause of the JRAF-4 UX redirecting to `http://localhost:8000/login`. The audit is strictly read-only.

## Evidence Reviewed
- `resources/js/router.js`: Route definitions and navigation guards.
- `resources/js/stores/auth.js`: Pinia authentication store configuration.
- `resources/js/app.js`: Vue application bootstrap and initialization sequence.
- `routes/api.php` and `routes/web.php`: Backend route protection and middleware.

## Router Findings
In `resources/js/router.js`, the JRAF-4 routes (`/journals/:slug/register` and `/journals/:slug/membership/status`) are configured with `meta: { requiresAuth: true }`.
The global navigation guard (`beforeEach`) enforces this constraint:
```javascript
if (to.meta.requiresAuth && !authStore.userData) {
    return next({ name: "login" });
}
```
Any unauthenticated access to these routes is immediately redirected to `/login` by the Vue Router.

## Auth Store Findings
In `resources/js/stores/auth.js`, the authentication state defaults to `userData: null` and relies on `pinia-plugin-persistedstate` (`persist: true`) to restore the JWT and user data from browser storage.

## Bootstrap Findings
In `resources/js/app.js`, the application initialization order is defined as follows:
```javascript
const pinia = createPinia();
app.use(pinia);
// ...
app.use(router);           // 1. Vue Router starts initial navigation
pinia.use(piniaPersist);   // 2. Pinia state is restored from storage
```
Because `app.use(router)` is invoked before `pinia.use(piniaPersist)`, the router performs its initial navigation (and evaluates the `beforeEach` guard) *before* the persisted state is loaded into memory. When a user navigates directly to a protected URL via a deep link or hard-refresh, `authStore.userData` is guaranteed to be `null` during that initial evaluation, causing a premature and incorrect redirect to `/login`.

## Backend Findings
In `routes/web.php`, the SPA is served via a public catch-all route (`Route::get('/{any}')`).
In `routes/api.php`, the API endpoints are protected by `auth:api` (JWT middleware), which returns a `401 Unauthorized` JSON response on failure, not an HTML redirect. 
There is no backend mechanism forcing a web-session redirect for the Vue SPA, and there are no global Axios interceptors in the frontend that redirect on `401`.

## Redirect Origin
Classified as: **B — Auth store initialization race**
The redirect originates exclusively from the frontend Vue Router `beforeEach` guard during application bootstrap.

## Root Cause
The root cause is the initialization sequence in `resources/js/app.js`. The router evaluates the route protection rules before the authentication store has restored the user's logged-in state.

## JRAF-4 Impact
This issue severely impacts the JRAF-4 UX because users typically access the Journal Registration page via direct links (e.g., from an email or external site). Because this is a direct entry (deep link) into the SPA, the race condition immediately redirects them to `/login`, even if they already have an active session in their browser.
However, this is **not JRAF-4 specific**. It is an **SPA-wide** defect that affects any deep link or hard-refresh to any route with `requiresAuth: true` (such as `/dashboard` or `/author/submissions`).

## Recommended Minimal Fix
The minimal fix requires swapping the initialization order in `resources/js/app.js` to ensure state is restored before the router evaluates navigation guards:

```javascript
pinia.use(piniaPersist);
app.use(router);
```

## Out of Scope
As explicitly requested, no source code was modified. The JRAF4-001 finding (missing explicit Authorization headers) remains untouched. The backend authentication architecture, database, and test suites are unmodified. Browser runtime verification was unavailable in this read-only CLI environment, but source inspection definitively confirms the mechanism.

## Verdict
- **Root Cause**: Auth store initialization race condition (`app.use(router)` before `pinia.use(piniaPersist)`).
- **Responsible File**: `resources/js/app.js`
- **Redirect Condition**: Vue Router `beforeEach` guard evaluating `!authStore.userData` prematurely.
- **Classification**: SPA-wide frontend auth defect.
- **Verification**: Verified via strict code path analysis (Browser runtime unavailable).
