# PHASE 7A + 7B — POST-IMPLEMENTATION ARCHITECTURE & SECURITY AUDIT

## 1. AUDIT OBJECTIVE

This document verifies the post-implementation state of Phase 7A (Reviewer Capability Domain) and Phase 7B (Journal Membership Application Enhancement). 

The target architecture enforces that `Academic Identity ≠ Reviewer Capability ≠ Reviewer Authorization`.

---

## 2. FILES INSPECTED

The following core files were inspected during this audit:
- `app/Models/JournalReviewerCapability.php`
- `app/Models/JournalMembership.php`
- `app/Models/JournalMembershipApplication.php`
- `app/Models/ReviewerApplication.php`
- `database/migrations/2026_09_17_044444_create_journal_reviewer_capabilities_table.php`
- `database/migrations/2026_09_17_044444_add_recruitment_and_declarations_to_journal_membership_applications_table.php`
- `app/Http/Controllers/Api/EditorialDeskController.php`
- `tests/Feature/Phase7ReviewerCapabilityTest.php`

---

## 3. DATABASE SCHEMA AUDIT

### journal_reviewer_capabilities
The table matches the expected foundation model:
- `id` (bigint unsigned, primary)
- `journal_membership_id` (bigint unsigned, foreign key, UNIQUE constraint applied).
- `available_for_review` (tinyint/boolean, defaults to 1/true).
- `max_reviews_per_month` (int, defaults to 2).
- `years_of_experience` (int, nullable).
- `previous_experience` (text, nullable).
- `created_at`, `updated_at`.

No unnecessary `user_id`, `journal_id`, or duplicated academic identity fields exist in this table. The `UNIQUE` constraint guarantees a strict 1:1 schema mapping to memberships.

### journal_membership_applications
The table successfully incorporates the new enhancement fields:
- `recruitment_source` (varchar, nullable).
- `declarations` (longtext/JSON, nullable).

No accidental authorization fields were introduced.

---

## 4. MODEL RELATIONSHIP AUDIT

- **JournalMembership**: Contains a correctly defined `hasOne` relationship to `JournalReviewerCapability` (`reviewerCapability()`).
- **JournalReviewerCapability**: Contains a correctly defined `belongsTo` relationship back to `JournalMembership`.
- **JournalMembershipApplication**: Correctly casts `declarations` as an `array` inside `$casts`.

---

## 5. AVAILABLE_FOR_REVIEW COMPATIBILITY AUDIT

The `available_for_review` attribute correctly defaults to `true` at the database level. 
This preserves compatibility because the platform currently relies entirely on `JournalMembership` status for reviewer availability. By defaulting new capability records to `true`, we ensure that establishing a capability foundation does not accidentally revoke availability for newly modeled reviewers. Additionally, missing capability records are currently ignored by the legacy authorization flows, meaning the default state is functionally backward-compatible.

---

## 6. AUTHORIZATION BOUNDARY AUDIT

The architectural rule `ReviewerCapability ≠ ReviewerAuthorization` holds true.
No code in `Phase7ReviewerCapabilityTest.php` or `EditorialDeskController.php` assumes authorization from a capability record. 

An active reviewer still requires the canonical `JournalMembership` state (`role = 'reviewer'`, `status = 'active'`). Creating a capability record with `available_for_review = true` against an inactive or non-reviewer membership grants exactly zero authorization.

---

## 7. RECRUITMENT SOURCE AUDIT

The `recruitment_source` string field functions purely as descriptive metadata in `journal_membership_applications`. It currently dictates no automatic workflow rules. Specifying an applicant as `EDITOR_RECOMMENDED` or `INVITED_REVIEWER` does not skip the approval gate, nor does it bestow automatic reviewer authorization.

---

## 8. DECLARATIONS AUDIT

`declarations` operates solely as onboarding/agreement metadata (stored as an array/JSON block).
It does not override or grant reviewer authorization and exists as a supplementary data store for the application process. At this phase, declarations are stored foundation data and are not executing any runtime logic.

---

## 9. ACADEMIC PROFILE BOUNDARY AUDIT

The boundary between global academic identity and journal-specific capability is preserved. 
No academic identity fields (e.g., `academic_position`, `degree`, `institution`, `ORCID`) were duplicated into `JournalReviewerCapability`. The fields added to capability (`years_of_experience`, `previous_experience`) specifically represent journal-level peer review experience, keeping the capability strictly scoped to the review workflow context. Academic qualification remains distinct from authorization.

---

## 10. LEGACY ReviewerApplication AUDIT

The legacy `ReviewerApplication` model remains completely intact and functional. It was not deleted, renamed, or migrated.
**CURRENT LEGACY DEPENDENCIES**:
- **Legacy eligibility dependency**: Still hard-enforced in `EditorialDeskController@eligibleReviewers()`.
- **UI/Workflow dependency**: Legacy views and controllers (`ReviewerApplicationController`) remain untouched and operational.

---

## 11. ELIGIBILITY LOGIC AUDIT

The eligibility gate in `EditorialDeskController@eligibleReviewers()` remains entirely unchanged from its pre-Phase 7 state.
The exact dependencies currently checked are:
1. `JournalMembership` (role: reviewer, status: active)
2. `ReviewerApplication` (status: accepted)
3. Must have an `AcademicProfile`.

This confirms the baseline for Step 2 has been preserved without accidental modification.

---

## 12. REVIEWER CAPABILITY RUNTIME USAGE AUDIT

CAPABILITY RUNTIME ENFORCEMENT: NOT YET IMPLEMENTED.

The `JournalReviewerCapability` data is fully stored in the backend but is not yet queried or enforced by assignment logic, workload calculations, or `eligibleReviewers()`.

---

## 13. REVIEWER CAPACITY AUDIT

The `max_reviews_per_month` field acts strictly as foundation data. Phase 7A introduced no monthly quota enforcement, assignment blocking, or automatic calculation logic. 

---

## 14. COI AUDIT

No automatic COI detection was introduced. COI declarations can be passed into the new `declarations` application field conceptually, but actual manuscript-specific COI matching during reviewer assignment does not exist in this phase.

---

## 15. ROUTE / CONTROLLER / AUTHORIZATION AUDIT

Git diffs and status verify that the routes, controllers, and authorization logic for existing editorial desks, submissions, and authorship remain exactly as they were prior to Phase 7A. No unrelated routes or middlewares were modified.

---

## 16. TEST AUDIT

`Phase7ReviewerCapabilityTest.php` legitimately asserts the structural boundaries of the architecture:
- **Test 7**: Asserts that `recruitment_source` does not implicitly create an active membership.
- **Test 8**: Asserts that creating an `available_for_review = true` capability for an inactive membership does not alter the membership's inactive status, proving that capability alone doesn't grant authorization.
- **Test 9**: Directly exercises `ReviewerApplication::create()` to prove the legacy model saves successfully alongside the new architecture.

---

## 17. MIGRATION SAFETY AUDIT

The database successfully migrated the two targeted schemas.
The `journal_reviewer_capabilities` table creation was safely rolled back (`--step=1`), patched with the missing `unique()` foreign key constraint, and re-migrated cleanly. No destructive schema drops or existing data resets occurred.

---

## 18. EXISTING DATA SAFETY

Existing tables (`journal_memberships`, `journal_membership_applications`, `reviewer_applications`, `academic_profiles`) remain fully intact with no dropped columns or corrupted rows.

---

## 19. GIT DIFF AUDIT

Files modified directly in response to Phase 7A/7B:
- `app/Models/JournalMembership.php`
- `app/Models/JournalMembershipApplication.php`
- `database/migrations/2026_09_17_044444_create_journal_reviewer_capabilities_table.php`
- `database/migrations/2026_09_17_044444_add_recruitment_and_declarations_to_journal_membership_applications_table.php`
- `app/Models/JournalReviewerCapability.php`
- `tests/Feature/Phase7ReviewerCapabilityTest.php`

No unexpected controllers or application logic files were touched.

---

## 20. ARCHITECTURAL CONSISTENCY CHECK

The architecture maps strictly to the ONE USER IDENTITY mandate.
No redundant roles (like `ReviewerUser` or `JournalReviewerProfile`) were created. The 1:1 `JournalMembership` to `JournalReviewerCapability` structure elegantly avoids duplicating the user identity while isolating reviewer-specific metadata.

---

## 21. PHASE 7 STEP 2 READINESS

The codebase is **READY** for Phase 7 Step 2.
The foundation safely exists without disturbing the legacy workflow, meaning Step 2 can safely begin decoupling the `ReviewerApplication` hard-check from the `eligibleReviewers()` logic and transition to leveraging the new capabilities system.

---

## 22. REQUIRED AUDIT CONCLUSION

PHASE 7A + 7B POST-IMPLEMENTATION AUDIT STATUS: CLEAN

7A REVIEWER CAPABILITY DOMAIN: PASS
7B MEMBERSHIP APPLICATION ENHANCEMENT: PASS

DATABASE SCHEMA: PASS
MODEL RELATIONSHIPS: PASS
AUTHORIZATION BOUNDARY: PASS
ACADEMIC PROFILE BOUNDARY: PASS
RECRUITMENT SOURCE BOUNDARY: PASS
DECLARATIONS BOUNDARY: PASS
LEGACY ReviewerApplication COMPATIBILITY: PASS
ELIGIBILITY LOGIC UNCHANGED: PASS
REVIEWER ASSIGNMENT UNCHANGED: PASS
COI LOGIC UNCHANGED: PASS
MIGRATION SAFETY: PASS
DATA SAFETY: PASS
TEST COVERAGE: PASS

PHASE 7 STEP 2 READINESS: READY

APPLICATION CODE CHANGED DURING AUDIT: NO
DATABASE CHANGED DURING AUDIT: NO
MIGRATIONS CREATED DURING AUDIT: NO
DATA MODIFIED DURING AUDIT: NO
ROUTES MODIFIED DURING AUDIT: NO
AUTHORIZATION MODIFIED DURING AUDIT: NO
