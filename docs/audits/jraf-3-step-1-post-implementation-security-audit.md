# JRAF-3 Step 1 — Post-Implementation Security & Architecture Audit

## 1. Audit Scope

This audit evaluates the post-implementation state of **JRAF-3 Step 1 (3A & 3B)**, specifically focusing on the newly introduced `JournalMembershipApplicationService` and the `JournalReviewerCapability` provisioning logic within the approval flow. The review ensures that these implementations respect the core architectural requirements (strict separation of JWT API vs Web Session), domain isolation, security boundaries, and legacy compatibility.

## 2. Files Inspected

* `app/Services/JournalMembershipApplicationService.php`
* `app/Http/Controllers/JournalMembershipApplicationController.php`
* `app/Http/Controllers/WebAdmin/MembershipVerificationController.php`
* `routes/api.php`
* `routes/web.php`
* `tests/Feature/Jraf3Step1Test.php`

## 3. 3A Application Service Audit

### 3A.1 Service Extraction
The business logic surrounding journal membership applications has been successfully extracted from `JournalMembershipApplicationController` into `JournalMembershipApplicationService`. This achieves the intended goal of decoupling domain logic from the Blade controller, preparing the foundation for future REST API implementation.

### 3A.2 Draft Creation
Draft creation safely prevents duplicates by verifying `user_id`, `journal_id`, and `requested_role` while ensuring the status is not already active or rejected. Academic identity is synchronized correctly with `AcademicProfile`.

### 3A.3 Draft Update
Draft updates accurately reject unauthorized users (enforcing `user_id` matching) and restrict modifications to `DRAFT` or `NEEDS_REVISION` statuses. The service specifically omits `requested_role` and `journal_id` from modifications, preventing improper journal switching or role escalation.

### 3A.4 Submission
Submission enforces strict constraints, requiring the application to be owned by the user and currently in an editable state (`DRAFT` or `NEEDS_REVISION`).

### 3A.5 Controller Delegation
The controller properly delegates to the service without accidental logic duplication. Validation correctly remains in the controller before passing the DTO/array into the service.

## 4. Authorization Audit

**PASS**
* All lookups in the controller strictly utilize `where('user_id', Auth::id())`.
* The service layers provide an additional deep-check explicitly throwing exceptions if `$application->user_id !== $user->id`.
* The `requested_role` cannot be manipulated to anything beyond the validated list (`owner, editor, reviewer`), and cannot be modified after the initial draft creation.

## 5. Journal Isolation Audit

**PASS**
* Journal relationships are strongly isolated. Applications lookup is bounded by `journal_id` during duplicate prevention.
* Approval logic references the specific `journal_id` tied to the application when generating the subsequent `JournalMembership`.
* A user cannot cross-reference a capability or application belonging to a different journal.

## 6. 3B Approval Flow Audit

The approval flow correctly routes through `MembershipVerificationController@updateStatus`. 
1. The status is evaluated.
2. The transaction begins.
3. It ensures an active membership doesn't already exist to prevent duplication.
4. It safely upgrades a `pending` membership to `active` or creates a brand new `active` membership.
5. It checks if the role requires reviewer capability provisioning.

## 7. Reviewer Capability Provisioning

**PASS**
The logic explicitly verifies `if ($application->requested_role === 'reviewer')` and correctly provisions the `JournalReviewerCapability` linked via `journal_membership_id`. The fallback `user_id` anti-pattern is successfully avoided, locking the capability directly to the journal scope. Default provisioning appropriately sets `available_for_review = true` and `max_reviews_per_month = 2`.

## 8. Member / Non-Reviewer Isolation

**PASS**
Because the logic is wrapped in a strict `=== 'reviewer'` conditional, `member`, `owner`, and `editor` roles do not receive a spurious `JournalReviewerCapability`. The separation of privileges and domain models is strictly preserved.

## 9. Idempotency Audit

**PASS**
The capability generation utilizes `firstOrCreate(['journal_membership_id' => $membership->id])`. If a database anomaly, a race condition, or a manual re-trigger occurs on the approval, the capability will simply be returned rather than duplicated. This provides safe idempotency at the capability boundary.

## 10. Transaction Integrity

**PASS**
The logic in `MembershipVerificationController` wraps the application save, the membership creation/update, and the reviewer capability provisioning inside `DB::beginTransaction()` and `DB::commit()`. A failure during capability provisioning will correctly roll back the application status update and the membership creation, avoiding inconsistent or orphaned states.

## 11. Role Escalation Audit

**PASS**
* The `requested_role` is safely distinct from the authorized role. It serves only as a request parameter.
* The system does not grant authorization upon draft creation or submission.
* Privileges are only granted once an admin manually sets the status to `APPROVED` during verification, which subsequently generates the `active` membership.

## 12. Reviewer Capability Authorization Boundary

**PASS**
The capability model does not accidentally usurp authorization checks. It only acts as a metadata container (`max_reviews_per_month`, `available_for_review`). The actual authorization for peer review assignments remains tightly bound to `JournalMembership` where `status = active` and `role = reviewer`.

## 13. AcademicProfile Boundary

**PASS**
The implementation syncs data back into `AcademicProfile` using `firstOrCreate` and specific `$updateData` extraction. No identity or academic data fields (e.g., ORCID, institution, degrees) leaked into `JournalReviewerCapability`, successfully maintaining the unified canonical identity.

## 14. Recruitment Source & Declarations

**PASS**
These two fields are safely attached to the `JournalMembershipApplication` entity via the service. They do not trigger unintended eligibility checks, bypass validations, or create elevated privileges.

## 15. ReviewerApplication Compatibility

**PASS**
Legacy fallback paths remain untouched. The legacy `ReviewerApplication` forms and admin approval routes were completely bypassed during this Step 1 refactor, keeping them operational for existing unmigrated users.

## 16. Reviewer Eligibility Regression

**PASS**
The assignment flows and diagnostic tests continue to pass (`Phase7CanonicalReviewerEligibilityTest` and `EditorialDeskDiagnosticTest`). The queries for reviewer eligibility successfully recognize the newly provisioned canonical capabilities without issues.

## 17. UI/UX Impact

**Existing UI/UX:**
* The Blade UI remains entirely functional, powered by `JournalMembershipApplicationController`.
* Forms submit correctly.
* Validation errors and success redirects work identically to the pre-refactor state.

**Missing UI/UX (Observation Only):**
* `Recruitment Source` and `Declarations` are not exposed in the Blade forms.
* `JournalReviewerCapability` fields are not currently manageable in the UI.

## 18. JRAF-3 Step 2 API Scope Check

**PASS**
A scan of `routes/api.php` and `routes/web.php` confirms no new API endpoints were prematurely exposed. The boundary constraints have been strictly respected.

## 19. Database / Schema Impact

**PASS**
* No migrations were created.
* No schema alterations occurred.
* No data loss or resets were triggered.

## 20. Test Coverage Review

The newly implemented `tests/Feature/Jraf3Step1Test.php` correctly leverages isolated models and rigorously verifies:
1. Academic identity synchronization.
2. Duplicate active application prevention.
3. Reviewer capability generation upon reviewer approval.
4. Capability isolation (non-generation) upon non-reviewer approval.

The existing regression suites (95 assertions) ran cleanly, proving the foundational changes did not compromise existing authorization or legacy boundaries.

## 21. Findings

**FINDING-1: Service Successfully Decoupled**
* **Type:** OBSERVATION
* **Evidence:** `JournalMembershipApplicationService` and `JournalMembershipApplicationController` interaction.
* **Impact:** High maintainability. Code is ready for API consumption.
* **Recommendation:** None.

**FINDING-2: Admin Transaction Scope is Safe**
* **Type:** PASS
* **Evidence:** `MembershipVerificationController@updateStatus` utilizes `DB::beginTransaction()`.
* **Impact:** Protects against orphaned reviewer capabilities.
* **Recommendation:** None.

## 22. Final Verdict

**CLEAN**

The JRAF-3 Step 1 implementation fully satisfies the architectural, security, and functional requirements. Authorization checks are solid, idempotency is guaranteed, transaction boundaries are respected, and legacy capability flows remain fully intact.

## 23. Recommended Next Step

Proceed to **JRAF-3 Step 2** to implement the JWT-authenticated API endpoints utilizing the newly extracted `JournalMembershipApplicationService`.
