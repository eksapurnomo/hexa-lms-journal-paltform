# Phase 6 Post-Implementation Security Audit

## PART 1 — VERIFY ACTUAL CHANGES

The following files were identified as containing the actual changes implemented in Phase 6:
- `app/Policies/SubmissionPolicy.php`
- `app/Repositories/SubmissionRepository.php`
- `resources/js/pages/author/SubmissionList.vue`
- `resources/js/pages/author/SubmissionDetail.vue`
- `tests/Feature/SubmissionSecurityTest.php`

## PART 2 — SUBMISSION POLICY AUDIT

Current `SubmissionPolicy` behavior:
- **VIEW =**
  Admin OR existing privileged editorial access OR creator (`created_by`) OR registered SubmissionAuthor (`authors()->where('user_id', $user->id)`)
- **UPDATE, DELETE, SUBMIT, UPLOAD, REVISION =**
  Admin OR strictly the submission creator (`created_by === $user->id`), conditional on correct submission status (e.g. draft, revision_required).
- **DOWNLOAD FILE =**
  Inherits from `view()`.

The logical rule securely separates authorship from mutation authorization.

## PART 3 — CRITICAL OR-WHERE GROUPING AUDIT

In `SubmissionRepository::getScopedQuery()`, the grouping is constructed as follows:
```php
$query->where(function ($q) use ($user) {
    // Author (Creator)
    $q->where('created_by', $user->id)
      // Registered Co-Author
      ->orWhereHas('authors', function ($authorQuery) use ($user) {
          $authorQuery->where('user_id', $user->id);
      })
      // Journal Owner
      ->orWhereHas('journal.memberships', ...)
      // Assigned Editor
      ->orWhere(function ($editorQuery) use ($user) { ... });
});
```
The effective SQL logic safely evaluates to:
```text
(
    created_by = current_user
    OR authors.user_id = current_user
    OR is_journal_owner
    OR is_assigned_editor
)
```
There are no global mandatory scope conditions (like tenant_id) residing *outside* this closure that were bypassed. The Phase 6 `orWhereHas` condition was correctly placed *inside* the existing main isolation closure.

## PART 4 — JOURNAL ISOLATION

Author access remains properly scoped. Because a user's dashboard view is restricted solely by the closure in `SubmissionRepository`, an author only sees the specific submission they authored. Belonging to Journal A does not grant access to other submissions in Journal A unless the user holds a privileged role (like `owner` or `editor`).

## PART 5 — DASHBOARD SCOPE

The dashboard queries use `SubmissionRepository::getScopedQuery()`.
- **Case 1 (creator):** visible
- **Case 2 (registered SubmissionAuthor):** visible
- **Case 3 (corresponding author):** visible
- **Case 4 (unrelated user):** not visible
- **Case 5 (User A belongs to Journal A, Submission belongs to Journal B, not author):** not visible

## PART 6 — DIRECT URL ACCESS

Direct URL access is governed by `SubmissionPolicy`.
- `GET /api/submissions/{id}` uses `view()` and allows co-authors.
- Mutation routes like `PUT /api/submissions/{id}`, `POST .../submit`, and `POST .../files` strictly use `update()`, `submit()`, and `uploadFile()`. All of these strictly enforce `created_by === current_user`.
Frontend hiding is not the only security boundary; the backend remains completely authoritative.

## PART 7 — MUTATION BOUNDARY

The mutation boundary was effectively preserved. 
- **Creator**: VIEW (YES), EDIT/UPLOAD/SUBMIT/REVISION (YES)
- **Co-Author**: VIEW (YES), EDIT/UPLOAD/SUBMIT/REVISION (NO)
- **Corresponding Author**: VIEW (YES), EDIT/UPLOAD/SUBMIT/REVISION (NO)

## PART 8 — SUBMISSION DETAIL UX SECURITY

In `SubmissionDetail.vue`, mutation buttons (upload, submit, submit revision) are wrapped in `v-if="submission.created_by === authStore.userData?.id"`. View-only authors (who are not creators) can see the manuscript data and status, but cannot access any mutation controls in the UI. 

## PART 9 — AUTHORSHIP ROLE DISPLAY

In `SubmissionList.vue`, the authorship indicator is driven directly by backend data:
- `Owner`: `sub.created_by === authStore.userData?.id`
- `Corresponding`: Evaluated from the canonical `submission_authors` relation (`a.user_id === ... && a.is_corresponding`)
- `Co-Author`: Not creator, not corresponding.
It does not assume that `created_by` is the corresponding author or the only author.

## PART 10 — POLICY VS REPOSITORY CONSISTENCY

- **Dashboard:** Exposes if user is creator or registered author.
- **Policy:** Allows detail view if user is creator or registered author.
There are no contradictions between the repository scope and the policy.

## PART 11 — EXISTING PRIVILEGED ROLES

Existing roles (Admin, Journal Owner, Editor) are unharmed. The `orWhereHas` condition added for authors is inside a parallel `OR` block alongside the privileged role checks. This does not grant editorial roles broader access, nor does it let an author bypass editorial constraints.

## PART 12 — TEST COVERAGE AUDIT

`tests/Feature/SubmissionSecurityTest.php` contains the five requested Phase 6 scenarios:
- `test_phase6_creator_access`
- `test_phase6_registered_co_author_view_access`
- `test_phase6_corresponding_author_view_access`
- `test_phase6_unrelated_user_denied`
- `test_phase6_journal_isolation`

These tests explicitly confirm that the `OR`-condition bypass does not exist. An unrelated user is correctly denied, and a user in the same journal is denied.

## PART 13 — QUERY LOGICAL MODEL

```text
CURRENT QUERY

A = admin bypass
B = creator condition
C = registered author condition
D = journal owner condition
E = assigned editor condition

Expected:
A OR (B OR C OR D OR E)

Actual:
A OR (B OR C OR D OR E)
```

## PART 14 — SECURITY FINDINGS

- **Finding**: None
- **Evidence**: All audited code strictly separates view access (which includes co-authors) from mutation access (which requires creator). The backend policy properly rejects mutation requests from co-authors. The UI properly disables mutation buttons.
- **Impact**: N/A
- **Affected component**: N/A
- **Recommended future action**: Once a business decision is made regarding Corresponding Author mutation rights, `SubmissionPolicy` and `SubmissionDetail.vue` can be safely updated to expand mutation to `is_corresponding` authors.

## PART 15 — PHASE 6 SECURITY MATRIX

| Security Area                 | Status | Evidence |
| ----------------------------- | ------ | -------- |
| Creator View                  | PASS   | `SubmissionPolicy@view`, test `test_phase6_creator_access` |
| Co-Author View                | PASS   | `SubmissionPolicy@view`, test `test_phase6_registered_co_author_view_access` |
| Corresponding Author View     | PASS   | `SubmissionPolicy@view`, test `test_phase6_corresponding_author_view_access` |
| Unrelated User Denied         | PASS   | `SubmissionPolicy@view`, test `test_phase6_unrelated_user_denied` |
| Journal Isolation             | PASS   | `SubmissionRepository::getScopedQuery()`, test `test_phase6_journal_isolation` |
| Dashboard Scope               | PASS   | `SubmissionRepository::getScopedQuery()` correctly scopes. |
| Direct URL Protection         | PASS   | `SubmissionPolicy` strictly guards direct mutation endpoints. |
| Mutation Boundary             | PASS   | `SubmissionPolicy` updates/submit restrict to `created_by`. |
| Upload Protection             | PASS   | `SubmissionPolicy@uploadFile` restricts to `created_by`. |
| Submit Protection             | PASS   | `SubmissionPolicy@submit` restricts to `created_by`. |
| Revision Protection           | PASS   | `SubmissionPolicy@submitRevision` restricts to `created_by`. |
| Editorial Roles               | PASS   | `SubmissionPolicy` retains privileged overrides. |
| Frontend UX Boundary          | PASS   | `SubmissionDetail.vue` uses strict `created_by` v-ifs. |
| Repository Query Grouping     | PASS   | `orWhere` conditions strictly contained in the main closure. |
| Policy/Repository Consistency | PASS   | Both strictly sync to creator + registered authors. |
| Regression Tests              | PASS   | `SubmissionSecurityTest` explicitly covers scenarios. |

## PART 16 — FINAL SECURITY VERDICT

SECURITY AUDIT: CLEAN

The implementation cleanly distinguishes between read-only authorship access and creator mutation authority, avoiding any accidental privileges or isolation bypasses.

## PART 17 — PHASE 7 READINESS

READY

AUDIT STATUS: COMPLETE

CODE CHANGED: NO
SCHEMA CHANGED: NO
MIGRATIONS CREATED: NO
DATABASE DATA CHANGED: NO
ROUTES CHANGED: NO
AUTHORIZATION CHANGED: NO
UI/UX CHANGED: NO
TESTS CHANGED: NO
PHASE 7 IMPLEMENTED: NO
