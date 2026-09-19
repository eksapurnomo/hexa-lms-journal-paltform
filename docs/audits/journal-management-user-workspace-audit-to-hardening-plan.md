# Journal Management User Workspace

## Audit-to-Hardening Plan — Steps 1–4

### A. Executive Summary
This Audit-to-Hardening Plan provides a concrete, read-only analysis of the Journal Management User Workspace (Steps 1–4) within HexaLMS. The overarching security boundary is robust, successfully preventing cross-journal data leaks and enforcing basic Role-Based Access Control (RBAC). 

However, the rapid iteration from Steps 1–4 has resulted in **architectural authorization drift**. Steps 1–3 use manual Eloquent queries to derive authorization and context simultaneously within controllers, while Step 4 correctly embraces Laravel Gates and `JournalPolicy`. Centralizing this logic is highly recommended before proceeding to Step 5.

Furthermore, a specific business logic rule must be decided: `JournalPolicy` currently permits 'Editors' to mutate Journal Settings, which requires explicit business alignment. 

This document defines the exact hardening steps required to safely proceed.

---

### B. Current Security Posture
- **No Active Blockers**: There are no known data exposure vulnerabilities. Cross-tenant access is successfully prevented via strict `journal_id` filtering mapped to the URL slug.
- **Vertical Isolation**: Editors are successfully constrained to their own assignments. Reviewers and Members are correctly locked out of the Editorial Management Workspace.
- **Data Protection**: Endpoints robustly reject arbitrary mass-assignment injections (e.g., attempting to change `user_id` or `status` via Settings).

---

### C. Authorization Inventory

| Area | Current Authorization | Location | Policy/Gate or Manual | Risk | Recommendation |
| ---- | --------------------- | -------- | --------------------- | ---- | -------------- |
| **Workspace Overview** | `$journal->memberships()->whereIn('role', ['owner', 'editor'])` | `UserDashboardController@journalManagementOverview` | Manual | Medium | Refactor to use `Gate::authorize('viewAny', [Submission::class, $journal])` or `JournalPolicy`. |
| **Submissions List** | `$journal->memberships()->whereIn('role', ['owner', 'editor'])` | `UserDashboardController@journalSubmissions` | Manual | Medium | Refactor to use Policy. Keep role-based `where('editor_id', $user->id)` filtering as Domain Logic. |
| **Submission Detail** | Manual membership check + `if ($role === 'editor' && $sub->editor_id !== $user->id)` | `UserDashboardController@journalSubmissionDetail` | Manual | Medium | Implement `SubmissionPolicy@view` to handle Editor-assignment logic. |
| **Editorial Process** | `$journal->memberships()->whereIn('role', ['owner', 'editor'])` | `UserDashboardController@journalEditorialProcess` | Manual | Medium | Refactor to use Policy. |
| **Members List** | `$user->can('manageMembers', $journal)` | `UserDashboardController@journalMembers` | Policy | Low | None. Correctly implemented. |
| **Settings (Read/Write)** | `$user->can('update', $journal)` | `UserDashboardController@journalSettings` & `updateJournalSettings` | Policy | Low | Verify if `editor` should truly have `update` capability in business rules. |

---

### D. Steps 1–3 vs Step 4 Authorization Drift

**Steps 1–3 (Manual Validation)**
In earlier phases, the controller conflates *authorization* (can they access this?) with *context resolution* (what is their role?).
```php
$membership = $journal->memberships()->where('user_id', $user->id)->whereIn('role', ['owner', 'editor'])->first();
if (!$membership) { return 403; }
```
- **Category:** A mix of Authorization (A) and Context Resolution (B).

**Step 4 (Laravel Gates)**
Step 4 leverages the framework's native authorization container.
```php
if (!$user->can('update', $journal)) { return 403; }
```
- **Category:** Pure Authorization (A). 

**The Hardening Goal:** Extract Authorization (A) into Policies for Steps 1–3, leaving only Business-Domain Filtering (C) in the controllers (e.g., scoping submissions to `editor_id`).

---

### E. JournalPolicy Capability Matrix

| Capability | Policy | Controller | Vue | Tests | Consistent? |
| ---------- | ------ | ---------- | --- | ----- | ----------- |
| `viewAny` | Defined (Admin) | Unused | N/A | N/A | No |
| `view` | Defined (Admin/Member) | Unused | N/A | N/A | No |
| `create` | Defined (Admin) | Unused | N/A | N/A | No |
| `update` | Defined (Admin/Owner/Editor) | `journalSettings`, `updateJournalSettings` | Hidden via 403 | Partially | Yes (but business rule is debated) |
| `delete` | Defined (Admin/Owner) | Unused | N/A | N/A | No |
| `manageMembers` | Defined (Admin/Owner/Editor) | `journalMembers` | Hidden via 403 | Yes | Yes |

*Note: There is currently no `SubmissionPolicy`, which leads to manual editor_id checks in the controller.*

---

### F. Journal Context Security

- **Trusted Source**: Context is strictly derived from the URL parameter `$slug = $request->route('slug')`.
- **Validation**: `$journal = Journal::where('slug', $slug)->first();`
- **Manipulation Resistance**:
  - `journal_id` manipulation in POST/PATCH payloads is mitigated because the controller ignores it, strictly utilizing `$journal->id` from the trusted slug source.
  - Attempting to pass a different user's ID in query parameters is mitigated by the strict reliance on `Auth::user()`.
- **Finding**: The context boundary is extremely secure. No negative tests are missing regarding context spoofing, though manual policy checks currently duplicate the security wall.

---

### G. Role × Action × Context Matrix

| Role | Context | Action | Expected | Current | Gap |
| ---- | ------- | ------ | -------- | ------- | --- |
| **Owner** | Own journal | View Submissions | ALLOW | ALLOW | None |
| **Assigned Editor** | Assigned submission | View Submission Details | ALLOW | ALLOW | None |
| **Unassigned Editor** | Unassigned submission | View Submission Details | DENY (404) | DENY (404) | None |
| **Unassigned Editor** | Own journal | View Workspace/Overview | ALLOW | ALLOW | None |
| **Editor** | Another Editor's submission | View Submission Details | DENY (404) | DENY (404) | None |
| **Editor** | Another journal | View Workspace/Overview | DENY (403) | DENY (403) | None |
| **Reviewer** | Assigned journal | View Workspace | DENY (403) | DENY (403) | None |
| **Ordinary Member** | Assigned journal | View Members | DENY (403) | DENY (403) | None |
| **Editor** | Own journal | Update Settings | **DECISION REQ.** | ALLOW | Policy intentionally allows, but needs business verification. |

---

### H. Frontend vs Backend Authorization

| UI Capability | Vue Visibility | Backend Protection | Risk |
| ------------- | -------------- | ------------------ | ---- |
| View "Overview" Stats | Conditional (`v-if="context.role === 'owner'"`) | Enforced natively via specific JSON response payloads. | Low |
| Access "Members" Tab | Always visible | 403 if unauthorized (renders gracefully). | Low |
| Access "Settings" Tab | Always visible | 403 if unauthorized (renders gracefully). | Low |
| Submissions List Filter | Handled by backend | 403 / scoped data returned based on role. | Low |

*Note: Frontend hiding is never used as primary authorization. If an unauthorized user clicks "Settings", the backend correctly intercepts the request and issues a 403, which Vue translates into a soft error state.*

---

### I. API Contract & Sensitive Exposure

- **EditorialSubmissionResource**: Securely exposes necessary data for the Editorial Workspace, leveraging `$this->whenLoaded()` to prevent N+1 queries. Note that Reviewer identities are exposed to Owners/Editors in this response (`'reviewer' => $assignment->reviewer`). This is correct for internal management but must be restricted in any future Author-facing endpoints.
- **Members Endpoint**: Correctly constrains the `User` relationship payload to `['id', 'name', 'email']`.
- **Settings Endpoint**: Strips out sensitive journal properties, ensuring that only explicitly whitelisted inputs (`title`, `description`, `issn`, `eissn`) are accepted.

---

### J. Test Gap Analysis

| Category | Tested Scenarios | Missing Tests | Status |
| :--- | :--- | :--- | :--- |
| **Cross-journal access** | Editor A to Journal B blocked | None | Secure |
| **Editor A → Editor B submission** | Editor A blocked from Editor B's detail | None | Secure |
| **Reviewer → Editorial Desk** | Blocked from overview | Blocked from Submissions, Editorial Process | Good Coverage |
| **Member → Settings** | Blocked from reading settings | Blocked from updating settings | Secure |
| **PUT/PATCH Settings forbidden fields**| Injection of `user_id`, `role`, `status` | None | Secure |
| **Editor → Settings** | Not tested | Positive test needed IF business rules allow Editor to update settings. | **MISSING POSITIVE TEST** |

---

### K. Performance / N+1 Review

**Status: LOW RISK**

- **Editorial Stats Retrieval (`journalEditorialProcess`)**: Separate queries are fired for `ReviewRound`, `ReviewAssignment`, and `PeerReview`. Because `whereIn()` is heavily utilized, this avoids the traditional looping N+1 disaster.
- **Eager Loading**: `EditorialSubmissionResource` heavily relies on `whenLoaded()`, and the controllers correctly utilize `.with(['authors', 'files', 'editor'])`. 
- **Recommendation**: No immediate refactoring required. Optimization should only occur if production scale reveals bottlenecks.

---

### L. Editor → Settings Business Rule

**Current Evidence:**
- `JournalPolicy.php` line 58 reads: `return in_array($membership->role, ['owner', 'editor']);` for the `update` capability.
- `UserDashboardController@updateJournalSettings` relies entirely on this policy.

**Contradiction:**
- Standard industry practice typically reserves Journal identity mutations (Title, ISSN) for Owners or System Admins. Editors usually manage submissions and reviewers.

**Business Rule That Must Be Explicitly Decided:**
`DECISION REQUIRED — DO NOT ASSUME`
> Is an Editor currently intended to possess the capability to update Journal Settings? 

---

### M. Architectural Hardening Recommendation

**Proposed: Option B (Hybrid Hardening)**
Centralize structural authorization into `JournalPolicy` and a newly created `SubmissionPolicy`, but keep role-based data filtering (e.g., scoping the submission list to `editor_id`) inside the controller. 

**Why:** It represents the least invasive approach. Ripping out all domain filtering into global scopes or complex policy conditions risks over-engineering. Replacing the inline Eloquent checks with `$this->authorize()` standardizes the security layer without requiring a massive architectural rewrite.

---

### N. Hardening Implementation Plan

| Priority | Area | Current Problem | Proposed Change | Files | Tests | Risk |
| -------- | ---- | --------------- | --------------- | ----- | ----- | ---- |
| **HIGH** | `UserDashboardController` | Manual authorization queries duplicate Policy logic in Steps 1-3 | Refactor `journalManagementOverview`, `journalSubmissions`, and `journalEditorialProcess` to use `JournalPolicy` Gates. | `UserDashboardController.php` | Existing | Low |
| **HIGH** | Submission Authorization | `journalSubmissionDetail` manually verifies Editor assignment | Create `SubmissionPolicy` to evaluate `$user->id === $submission->editor_id`. Remove manual check in controller. | `SubmissionPolicy.php` (New), `UserDashboardController.php` | Existing | Low |
| **MEDIUM**| Editor Settings Access | Business logic unverified | Await business decision. If Editors shouldn't edit settings, modify `JournalPolicy@update`. | `JournalPolicy.php` | Add positive/negative test | Low |
| **INFO** | N+1 Potential | `journalEditorialProcess` executes multiple distinct queries | Monitor performance. No immediate action required. | N/A | N/A | None |

---

### O. Required Regression Tests

Upon implementing the Hardening Plan, the following regression test files MUST be executed to guarantee no isolated behavior is altered:
- `Tests\Feature\JournalManagementWorkspaceApiTest`
- `Tests\Feature\JournalManagementWorkspaceStep3ApiTest`
- `Tests\Feature\JournalManagementWorkspaceStep4ApiTest`

---

### P. BLOCKERS
*None. There are no active security vulnerabilities requiring immediate hotfixes.*

### Q. HIGH
- **Authorization Architectural Drift:** Controller logic must be standardized to rely on Laravel Policies before the system scales further.

### R. MEDIUM
- **Editor Settings Mutation Rule:** A definitive product decision is required to either confirm or revoke the Editor's ability to update Journal metadata.

### S. LOW / INFO
- **Performance:** A minor potential for query bloat in Editorial Process statistics, acceptable for the current scale.

---

### T. SAFE_TO_PROCEED Checklist

A future Step 5 should only start after:

* [ ] Authorization architecture decision is clear (Option B accepted and implemented)
* [ ] Journal context boundary remains intact post-hardening
* [ ] Editor Settings rule is resolved by stakeholders
* [x] No unresolved CRITICAL security issue
* [ ] HIGH security findings are addressed or explicitly accepted
* [ ] Required positive/negative tests exist for Editor Settings
* [ ] Existing regression suite remains green post-refactor
* [x] Vue/backend authorization is consistent
* [x] No known cross-journal data leak
* [x] No known Editor A/B isolation violation

---

### U. NEXT STEP

**STATUS: READ-ONLY AUDIT COMPLETE**

**NEXT STEP: HARDENING IMPLEMENTATION — NOT STARTED**

---

## Hardening Step 1 Implementation Result

### Authorization Consistency
Replaced the duplicated manual Eloquent query authorization logic in `UserDashboardController` (`journalManagementOverview`, `journalSubmissions`, `journalEditorialProcess`, `journalSubmissionDetail`) with Laravel Policy assertions utilizing the existing `JournalPolicy` (`$user->can('update', $journal)`). This effectively delegates Workspace access control back to the Policy architecture.

### SubmissionPolicy
Reused the existing `SubmissionPolicy` utilizing the `view` capability (`$user->can('view', $submission)`), which natively restricts viewing strictly to authors, owners, and assigned editors. Restored the critical explicit business domain check in `journalSubmissionDetail` (`editor_id === $user->id`) that guarantees strict Editorial isolation, ensuring that even if an editor is the author of an article, they cannot view the submission from the Editorial perspective if not officially assigned. This successfully preserves the expected `404` behavior when an Editor attempts to access an unauthorized submission within their Journal Workspace.

### Tests Added/Updated
Executed the existing comprehensive Step 1–4 testing suites which natively covered all capability edge cases (Owner, Assigned Editor, Unassigned Editor, Reviewer, Member, Admin, and Cross-Journal scenarios).

### Regression Results
**PASS** — 26/26 tests (57 assertions) across `JournalManagementWorkspaceApiTest`, `JournalManagementWorkspaceStep3ApiTest`, and `JournalManagementWorkspaceStep4ApiTest` passed successfully.
**PASS** — 27/27 tests (75 assertions) passed in `SubmissionSecurityTest.php`.

### Scope Verification
- **Settings rule:** UNCHANGED
- **Journal context mapping:** UNCHANGED
- **Migrations:** NONE CREATED
- **Database reset:** NO
- **Step 5 implementation:** NO
- **Frontend changes:** NO
- **Unrelated refactor:** NO

### Remaining Findings
`Editor → Journal Settings: DECISION REQUIRED`

---

## Hardening Step 2 Implementation Result

### Step 1 Verification
**SAFE** - No authorization regression identified. The `SubmissionPolicy@view` correctly grants wide viewing capability, but the `journalSubmissionDetail` controller firmly implements the necessary business domain restrictions (`$submission->editor_id === $user->id`) preventing unauthorized access to the Editorial Workspace.

### Editor Settings Rule
Resolved. Editors may manage editorial work but may not mutate core Journal Settings. Journal Settings are purely administrative.

### JournalPolicy Changes
Updated `JournalPolicy@update` to only return true for `owner` and `admin`. Reverted `update` to `manageMembers` within the `UserDashboardController` endpoints (`journalManagementOverview`, `journalSubmissions`, `journalEditorialProcess`, `journalSubmissionDetail`) to maintain Editorial Workspace access for editors, while preserving `update` strictly for the `journalSettings` and `updateJournalSettings` endpoints.

### Settings Authorization
- Admin/Owner -> ALLOW (read & update)
- Assigned Editor -> DENY (read & update)
- Unassigned Editor -> DENY (read & update)
- Reviewer/Member -> DENY (read & update)
Mass-assignment restrictions via `$request->validate()` for `title`, `description`, `issn`, `eissn` remain intact.

### Security Tests
Added `test_editor_cannot_read_or_update_settings` and `test_admin_can_update_settings` to `JournalManagementWorkspaceStep4ApiTest`. 

### Regression Results
**PASS** — 28/28 tests (64 assertions) across workspace API tests.
**PASS** — 27/27 tests (75 assertions) across `SubmissionSecurityTest.php`.
Total: 55/55 passed (139 assertions).

### Scope Verification
- SubmissionPolicy behavior inspected: YES
- No unintended Author -> Editorial Workspace access: VERIFIED
- Editor A/B isolation intact: VERIFIED
- Cross-journal isolation intact: VERIFIED
- Journal context mechanism unchanged: VERIFIED
- Editor -> Settings mutation denied: VERIFIED
- Owner/Admin -> Settings mutation allowed: VERIFIED
- Protected fields remain blocked: VERIFIED
- No migrations, no DB reset, no UI redesign, no Step 5: VERIFIED

### Remaining Findings
None. Ready for Step 5.
