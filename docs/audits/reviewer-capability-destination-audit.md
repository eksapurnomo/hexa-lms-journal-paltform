# Audit 3 — Reviewer Capability Destination Audit

## 1. Executive Summary
This audit finalizes the domain design revision for the reviewer ecosystem. Based on the fundamental architectural principle that "Academic Qualification ≠ Reviewer Authorization ≠ Reviewer Capability", this report delineates the strict boundaries between a user's academic identity, their journal-specific authorization, and their reviewer-specific capabilities. It provides the definitive mapping for legacy fields and ensures that academic degrees (S2/S3/Professor) and recruitment sources (Recommended Person) do not improperly act as hard authorization gates.

## 2. Canonical Destination Mapping

To fully retire the legacy `ReviewerApplication` while preserving its critical business data, the data must be distributed across the following domains:

### A. AcademicProfile (Academic Identity)
* **`expertise`**: Maps to `AcademicProfile.expertise` (or the `AcademicProfileExpertise` relation). General subject matter expertise is a property of the researcher, not a specific journal.
* *Note: Academic degrees (`highest_degree`) and positions (`academic_position`) are already correctly housed here.*

### B. JournalMembershipApplication (Onboarding & Sourcing)
* **`reviewer declarations`** (`agreed_confidentiality`, `agreed_conflict_of_interest`, `agreed_guidelines`): Maps to a proposed `declarations` (JSON) column on `JournalMembershipApplication`. These represent the applicant's acceptance of journal policies at the time of entry.
* **`recruitment/recommendation source`**: Maps to a proposed `recruitment_source` string/enum on `JournalMembershipApplication` (e.g., `SELF_APPLICATION`, `EDITOR_RECOMMENDED`, `INVITED`).

### C. Reviewer Capability Domain (New Implementation Required)
* **`available_for_review`**
* **`max_reviews_per_month`**
* **`years_of_experience`** (Specifically peer-review experience)
* **`previous_experience`** (Specifically peer-review track record)
* *Destination*: These fields require a dedicated capability domain. Since they are specific to a reviewer's relationship with a *particular* journal, they should be stored either as a `capability_settings` (JSON) column on the `JournalMembership` table, or as a dedicated `JournalReviewerCapability` table that `belongsTo` a `JournalMembership`.

## 3. Domain Design Constraints Verified

### 3.1 Academic Degree is Not an Authorization Gate
**Verified**: The current codebase (`EditorialDeskController@eligibleReviewers`) does not query `highest_degree` or `academic_position`. Thus, S2, S3, and Professors are all technically eligible as long as they hold an active reviewer membership. The architecture successfully isolates academic identity from authorization.

### 3.2 Recommended Person Lifecycle
**Verified Domain Rule**: A "Recommended Person" is merely a `recruitment_source`.
If an Editor recommends a person, a `JournalMembershipApplication` should be created with `recruitment_source = 'EDITOR_RECOMMENDED'` and `status = 'draft'` or `invited`. The person is merely a **Candidate**. They must accept the invitation (submitting the application) and be verified before `JournalMembership` is granted. Therefore, recommendation does not bypass authorization.

### 3.3 Separation of Reviewer Capability
By moving `max_reviews_per_month` and `available_for_review` to a Reviewer Capability domain attached to `JournalMembership`, the architecture ensures that a user can have different availability and workloads for different journals, completely isolated from their global `AcademicProfile`.

### 3.4 Manuscript Eligibility vs. Global Capability
**Verified Domain Rule**: The global `available_for_review` flag determines general willingness, but it does NOT dictate manuscript-specific eligibility. Manuscript eligibility must remain a dynamic calculation combining:
1. Global capability (Available? Has capacity?)
2. Manuscript-specific checks (Conflict of Interest with authors? Expertise match?)

## 4. Conflict of Interest Governance
COI must not be a static boolean on an academic profile. It is inherently relational. 
- **Application Level**: The `agreed_conflict_of_interest` declaration on `JournalMembershipApplication` simply confirms the reviewer understands the policy.
- **Assignment Level**: Actual COI detection must occur dynamically during `ReviewAssignment` (e.g., checking if the reviewer shares an `institution_id` with any `SubmissionAuthor`).

## 5. Recommended Database Strategy (For Future Implementation)

To fulfill this domain architecture, the future implementation phase should:
1. Add `recruitment_source` (string) and `declarations` (json) to `journal_membership_applications`.
2. Add `capabilities` (json) to `journal_memberships` (or create a `journal_reviewer_capabilities` table).
3. Update the `EditorialDeskController` to read capacity constraints from the new capability domain rather than the legacy application.

## 6. Audit Conclusion

The current HexaLMS schema correctly isolates `User`, `AcademicProfile`, and `JournalMembership`. The missing piece is strictly the **Reviewer Capability** domain and the expansion of the **Application** domain to track recruitment sources and policy declarations. Once these destinations are implemented, the legacy `ReviewerApplication` can be fully decommissioned without any loss of business rules.

---
AUDIT STATUS: COMPLETE
APPLICATION CODE CHANGED: NO
DATABASE CHANGED: NO
MIGRATIONS CREATED: NO
DATA MODIFIED: NO
ROUTES MODIFIED: NO
AUTHORIZATION MODIFIED: NO
