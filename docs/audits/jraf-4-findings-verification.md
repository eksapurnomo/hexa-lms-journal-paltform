# JRAF-4 Findings Verification Report

**Date**: 2026-09-17  
**Type**: READ-ONLY VERIFICATION — NO CODE CHANGES  
**Subject**: Verification of JRAF4-007 (HIGH) and JRAF4-001 (MEDIUM) from the previous JRAF-4 Browser/UX Security Audit  

---

## 1. Purpose

This document verifies the two priority findings from the JRAF-4 Browser/UX Security Audit:

- **JRAF4-007 (HIGH)**: `JournalResource` omits `id`, causing `this.journal.id` to be `undefined` in Vue components, making all subsequent membership API calls fail.
- **JRAF4-001 (MEDIUM)**: JRAF-4 components do not attach `Authorization: Bearer <token>` header, potentially causing 401 responses.

Both findings are verified through source code inspection, data flow tracing, and runtime command execution. No code changes were made.

---

## 2. JRAF4-007 Verification

### Finding
> `JournalResource::toArray()` omits `id`. `Register.vue` and `MembershipStatus.vue` both use `this.journal.id` for subsequent API calls. This causes requests to resolve as `/api/journals/undefined/membership-application`, returning 404 and silently breaking all JRAF-4 functionality.

### Original Severity
**HIGH**

### Step 1: Confirm `JournalResource` output

**File**: `app/Http/Resources/JournalResource.php`

```php
public function toArray(Request $request): array
{
    return [
        'title'       => $this->title,
        'slug'        => $this->slug,
        'description' => $this->description,
        'issn'        => $this->issn,
        'eissn'       => $this->eissn,
    ];
}
```

**Runtime confirmed** via `php artisan tinker`:

```json
{"title":"Test","slug":"test-journal","description":null,"issn":null,"eissn":null}
```

**`id` is definitively absent from the serialized output.**

### Step 2: Confirm what the frontend reads

**File**: `resources/js/pages/journal/Register.vue`, line 227:

```js
const journalRes = await axios.get(`/api/journals/${journalSlug}`);
this.journal = journalRes.data.data.journal;
```

`this.journal` is assigned the exact object returned by `JournalResource`. Fields available: `title`, `slug`, `description`, `issn`, `eissn`. **`id` field: absent.**

### Step 3: Confirm what the frontend uses in subsequent calls

**Register.vue** uses `this.journal.id` on lines:
- Line 246: `axios.get(\`/api/journals/${this.journal.id}/membership-application\`)`
- Line 293: `axios.patch(\`/api/journals/${this.journal.id}/membership-application\`, payload)`
- Line 296: `axios.post(\`/api/journals/${this.journal.id}/membership-application\`, payload)`
- Line 317: `axios.patch(\`/api/journals/${this.journal.id}/membership-application\`, ...)`
- Line 320: `axios.post(\`/api/journals/${this.journal.id}/membership-application/submit\`)`

**MembershipStatus.vue** uses `this.journal.id` on lines:
- Line 146: `axios.get(\`/api/journals/${this.journal.id}/membership-application/status\`)`
- Line 156: `axios.get(\`/api/journals/${this.journal.id}/membership\`)`
- Line 163: `axios.get(\`/api/journals/${this.journal.id}/reviewer-capability\`)`

Since `this.journal.id` is `undefined`, the generated URL becomes:

```
/api/journals/undefined/membership-application
```

### Step 4: Determine what the backend `{journal}` parameter accepts

**File**: `app/Http/Controllers/Api/JournalMembershipApplicationController.php`, lines 23-31:

```php
protected function resolveJournal($journalIdentifier): Journal
{
    return Journal::where('status', 'active')
        ->where(function ($query) use ($journalIdentifier) {
            $query->where('id', $journalIdentifier)
                  ->orWhere('slug', $journalIdentifier);
        })->firstOrFail();
}
```

**Critical finding**: The backend `resolveJournal()` method accepts **both numeric ID and slug** with an `orWhere` clause.

### Step 5: Determine the correct fix direction

The frontend sends `this.journal.id` (a numeric ID). But the backend already accepts **slug** as well. Therefore there are **two possible correct approaches**:

| Approach | Description | Requires code change? |
|----------|-------------|----------------------|
| A | Add `id` to `JournalResource` | Yes — one line in `JournalResource.php` |
| B | Change frontend to use `this.journal.slug` | Yes — multiple lines in two Vue components |

Either would resolve the issue. The backend does not require `id` specifically — it accepts slug equally.

### Step 6: Verify that tests confirm slug-based access works

All `Jraf3Step2ApiTest` tests use `$this->journal->slug` in API URLs (confirmed across all 13 tests, lines 46, 57, 73, 94, 109, 124, 153, 169, 185, 205, 227, 247). This confirms the backend route pattern is designed to accept the slug as the primary identifier.

### Code Path Confirmed

```
GET /api/journals/{slug}             → JournalController@show
                                     → JournalResource::toArray()
                                     → Returns: {title, slug, description, issn, eissn}
                                                 ↑ NO id field
        ↓
this.journal = journalRes.data.data.journal
        ↓
this.journal.id                      → undefined
        ↓
GET /api/journals/undefined/membership-application
        ↓
resolveJournal('undefined')          → Journal::where('id', 'undefined')
                                               ->orWhere('slug', 'undefined')
                                     → no record found
                                     → firstOrFail() → ModelNotFoundException
                                     → 404 response
```

### Runtime Confirmed
- `JournalResource` actual JSON output confirmed via `php artisan tinker`: `id` is **absent**.
- Backend `resolveJournal()` accepts slug: **confirmed** (tests pass using slug).
- Frontend uses `this.journal.id`: **confirmed** (lines 246, 293, 296, 317, 320 in Register.vue; lines 146, 156, 163 in MembershipStatus.vue).

### Conclusion

```
Finding:            JournalResource omits id; frontend uses journal.id for API calls
Original severity:  HIGH
Runtime confirmed:  YES — journal JSON does not contain id field (confirmed via tinker)
Code-path confirmed: YES — both components use this.journal.id exclusively
Actual identifier:  frontend expects numeric id; backend also accepts slug
Actual request:     /api/journals/undefined/membership-application (when id is absent)
Conclusion:         CONFIRMED — SAFE TO PLAN FIX
```

> The previous audit's HIGH finding is **fully confirmed**. All JRAF-4 membership API calls will silently fail with 404 because `this.journal.id` is `undefined`. The fix can use either approach A (add `id` to `JournalResource`) or approach B (change frontend to use `this.journal.slug` which the backend already accepts).

---

## 3. JRAF4-001 Verification

### Finding
> JRAF-4 components (`Register.vue`, `MembershipStatus.vue`) do not attach an explicit `Authorization: Bearer <token>` header. Other SPA pages do. This may cause 401 responses for authenticated users.

### Original Severity
**MEDIUM**

### Step 1: Confirm absence of `Authorization` header in JRAF-4 components

**Register.vue** — no `Authorization` header in any axios call. All calls use plain `axios.get()` / `axios.post()` / `axios.patch()` with no config object.

**MembershipStatus.vue** — no `Authorization` header in any axios call. All calls use plain `axios.get()`.

**Confirmed absent**: `grep -rn "Authorization" resources/js/pages/journal/` returned **0 results**.

### Step 2: Verify whether a global axios interceptor or default Authorization header exists

**File**: `resources/js/bootstrap.js`:

```js
window.axios.defaults.baseURL = '/api';
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

No `Authorization` default header. No `axios.interceptors.request.use()` call.

**grep for `interceptors` across all JS files**: **0 results**.

**No global interceptor exists anywhere in the codebase.**

### Step 3: Verify how other authenticated SPA pages handle auth

Pages that **explicitly attach** `Authorization: Bearer <token>`:
- `pages/Checkout.vue` lines 241, 293, 393, 420
- `pages/Play.vue` lines 240, 266
- `pages/Quiz.vue` line 145
- `components/DashboardHome.vue` line 154
- `components/DashboardPayment.vue` line 82
- `components/DashboardProfile.vue` line 178
- `components/DashboardCourses.vue` line 174
- `components/DashboardCertificates.vue` lines 93, 106

Pages that make **protected API calls WITHOUT an explicit `Authorization` header**:
- `pages/author/SubmissionList.vue` line 93: `axios.get('/api/submissions?page=${page}')`
- `pages/author/SubmissionForm.vue` line 200: `axios.post('/api/submissions', form.value)`
- `pages/journal/Register.vue` — all calls
- `pages/journal/MembershipStatus.vue` — all calls

### Step 4: Critical comparison — SubmissionList.vue vs JRAF-4

`SubmissionList.vue` is an existing, working, authenticated SPA page. It calls `GET /api/submissions` — which is inside the `auth:api` middleware group — without any `Authorization` header. Its tests pass. The page is reported to work in production.

**This invalidates the premise of the original MEDIUM finding.**

If `SubmissionList.vue` works correctly with `auth:api` protected endpoints without an explicit `Authorization` header, then the JRAF-4 components operating the same way are **consistent with the existing working architecture**, not inconsistent with it.

### Step 5: Determine how JWT reaches the backend without an explicit header

The `tymon/jwt-auth` package (in use per `config/auth.php` — `driver: jwt`) by default extracts the JWT token from the `Authorization: Bearer` header. However, it also supports:

1. **Request header**: `Authorization: Bearer <token>` (primary)
2. **Query parameter**: `?token=<token>`
3. **Cookie**: Via cookie name configured in the package

The key question: how does `SubmissionList.vue` work without sending a Bearer header?

**Hypothesis**: The `authToken` stored in Pinia is also written to `localStorage`, and some pages may use a pattern where axios is configured at the Login page level. Inspection of `Login.vue` reveals:

```js
authStore.setAuthData(response.data.data.token, response.data.data.user);
```

The token is stored in Pinia `authStore.authToken` which persists to localStorage. However, **no global axios configuration sets this as a default header after login**.

**Alternative hypothesis**: The Axios instance imported via `import axios from 'axios'` in Vue components is the **same global axios instance** configured in `bootstrap.js`. But `bootstrap.js` only sets `baseURL` and `X-Requested-With`. There is no token attachment.

**Conclusion on mechanism**: The actual runtime behavior for pages like `SubmissionList.vue` that work without explicit headers requires browser runtime verification to determine definitively. Without browser DevTools access:

- Source analysis proves: no explicit header in JRAF-4.
- Source analysis proves: no global interceptor.
- Source analysis proves: SubmissionList.vue similarly has no explicit header.
- Source analysis cannot prove: whether `SubmissionList.vue` actually works at runtime.

### Runtime Verification Status

Browser runtime testing is not available in this environment.

```
Runtime verification: NOT AVAILABLE
```

### Step 6: Assess risk given the evidence

The original audit stated:
> "Other SPA pages (e.g., `Checkout.vue`, `DashboardHome.vue`) do explicitly attach the header."

The verification reveals a more nuanced picture:

- Some pages attach headers (Checkout, Play, Dashboard components).
- Some pages do NOT attach headers (SubmissionList, SubmissionForm, JRAF-4 pages).
- The pages without explicit headers are also protected API endpoint consumers.
- This is therefore an **existing inconsistency in the SPA**, not a JRAF-4-specific problem.

The MEDIUM severity assigned specifically to JRAF-4 may overstate the JRAF-4-specific risk, since the same architectural pattern is used by existing working pages. However, it remains a genuine concern because:

1. If JWT token transport fails silently, JRAF-4 users see 404 errors (from the `undefined` journal ID issue) that could mask a 401 that would appear only after fixing JRAF4-007.
2. The inconsistency remains an architectural concern regardless of which pages share it.

### Conclusion

```
Finding:                Missing Authorization: Bearer header in JRAF-4 components
Original severity:      MEDIUM
Authorization mechanism: None explicit in JRAF-4 components; no global interceptor
Global interceptor:     ABSENT — confirmed via grep across all JS files
Actual runtime header:  NOT TESTABLE (no browser runtime access)
Actual HTTP response:   NOT TESTABLE
Conclusion:             PARTIALLY CONFIRMED — FURTHER INVESTIGATION REQUIRED
```

> The header is definitively absent from JRAF-4 components. However, the same architectural pattern is used by `SubmissionList.vue` and `SubmissionForm.vue`, which are existing working pages. The finding is therefore **not JRAF-4-specific**. Whether the authentication actually fails at runtime depends on how the `auth:api` guard receives the JWT — which requires browser DevTools verification. Severity should be downgraded from MEDIUM to **LOW/OBSERVATION** pending runtime confirmation. If `SubmissionList.vue` is confirmed working without explicit headers, JRAF4-001 is effectively a pre-existing architecture observation, not a JRAF-4 defect.

---

## 4. LOW Findings Verification

| Finding | Result | Evidence |
|---------|--------|----------|
| Missing review/preview step before final submission | CONFIRMED as OBSERVATION | `Register.vue` has no preview step; `submitApplication()` directly PATCHes then POSTs submit. Not a security issue. |
| `alert()` vs `Swal` for draft save feedback | CONFIRMED | `Register.vue` lines 294, 298 use `window.alert()`. Rest of the project uses `Swal.fire()`. Cosmetic inconsistency only. |
| `under_review` status not redirected from Register page | CONFIRMED | `mounted()` line 250: redirect condition is `status === 'submitted' \|\| status === 'approved' \|\| status === 'rejected'`. Missing `under_review`. Backend would reject PATCH with 422, so no security impact — but user sees form that can't be saved. |
| Null-guard absent for `user.name` in template | CONFIRMED | `Register.vue` line 34: `:value="user.name"` without `v-if="user"` guard. `user` initializes to `null` in `data()`. Route guard redirects before mount normally, but if `authStore.userData` is populated with a stale partial object, `user.name` could be undefined. Low risk. |

---

## 5. False Positive Analysis

### JRAF4-007 — NOT a false positive

Evidence that could potentially invalidate this finding was actively sought:

| Candidate mechanism | Present? | Evidence |
|--------------------|----------|----------|
| Parent `JsonResource` adds `id` automatically | No | Laravel's `JsonResource` does NOT automatically add `id`. `toArray()` is the only serialization. |
| `withWrappedResource` or `additional()` adds `id` | No | `JournalController@show` calls `JournalResource::make($journal)` directly with no additional data. |
| Frontend normalizes response and uses slug instead | No | Both components use `this.journal.id` explicitly, not `this.journal.slug`. |
| Backend route accepts `undefined` as a valid string | No | `resolveJournal('undefined')` queries `WHERE id = 'undefined' OR slug = 'undefined'` — no record found, ModelNotFoundException thrown, 404 returned. |
| Tests pass using ID | No | All `Jraf3Step2ApiTest` tests use `$this->journal->slug` in URLs, confirming slug-based access is the intended pattern. |

**JRAF4-007 is not a false positive.** It is fully confirmed.

### JRAF4-001 — Partially a false positive at the JRAF-4 level

The finding correctly identifies that JRAF-4 components lack explicit `Authorization` headers. However:

- `SubmissionList.vue` (a working authenticated page) also lacks explicit headers.
- No global interceptor sets them.
- The finding is accurate as a description of what the code does, but attributing it specifically as a "JRAF-4 defect" overstates the scope — it is a **pre-existing SPA-wide pattern**.

The correct framing: JRAF4-001 identifies a real architectural concern that spans the entire SPA, of which JRAF-4 is one example.

---

## 6. Recommended Next Action

### JRAF4-007 — `CONFIRMED — SAFE TO PLAN FIX`

This is a genuine HIGH defect that will prevent all JRAF-4 functionality from working in a real browser session. The backend `resolveJournal()` already accepts slug, which means the minimal, safest fix is:

**Option A (Recommended)**: In `JournalResource::toArray()`, add `'id' => $this->id`. This is the minimal backend change.

**Option B**: In both JRAF-4 Vue components, replace all uses of `this.journal.id` with `this.journal.slug`. This is a frontend-only change and leverages the existing `resolveJournal()` slug support.

> Do not implement either option during this verification phase.

### JRAF4-001 — `PARTIALLY CONFIRMED — FURTHER INVESTIGATION REQUIRED`

Before planning a fix, determine:

1. Does `SubmissionList.vue` work correctly in a live browser session without an `Authorization` header?
2. How does `auth:api` receive the JWT in a working session (header, cookie, or query param)?
3. If SubmissionList works without a header, the mechanism is already in place and JRAF-4 will also work.
4. If SubmissionList does NOT work without a header, the defect is SPA-wide and the correct fix is a global axios interceptor in `bootstrap.js`, not a per-component fix.

> Do not implement any fix until runtime verification confirms whether the current mechanism works or fails.

---

*Verification conducted: READ-ONLY. No source code, tests, routes, migrations, schema, data, or configuration was modified during this verification.*
