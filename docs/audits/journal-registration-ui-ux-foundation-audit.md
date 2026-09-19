# JOURNAL REGISTRATION UI/UX FOUNDATION AUDIT

## HexaLMS Journal Platform

## OBJECTIVE
Perform a STRICTLY READ-ONLY audit of the current Journal registration, Journal Membership Application, Academic Profile, Journal Membership, Reviewer Capability, and verification UX foundations.

---

## 1. CURRENT REGISTRATION FLOW AUDIT

Trace:
```text
/api/register
   ↓
UserController@register
   ↓
StudentRegisterRequest validation
   ↓
UserRepository::storeByStudentRequest
   ↓
JWTAuth::fromUser
   ↓
Returns JSON (JWT token)
```

**Findings:**
- Normal LMS registration happens via API (`/api/register`).
- It generates and returns a JWT token for a Vue SPA frontend.
- It does **not** create a Laravel web session (`Auth::login()`).
- Normal LMS users are created with student-level defaults.
- The Journal Membership Application flow resides in `routes/web.php` and is protected by the `auth` (web session) middleware.
- **Critical Gap:** A normal LMS user registering via the Vue SPA cannot seamlessly transition into the Journal Membership Application Blade views without logging in again via the web portal (if one exists for users), because the JWT token does not bridge to the session-based web guard.

---

## 2. JOURNAL MEMBERSHIP APPLICATION AUDIT

Trace:
```text
/journal/membership-applications/create (GET)
/journal/membership-applications (POST)
        ↓
JournalMembershipApplicationController@store
        ↓
Creates/Updates AcademicProfile
        ↓
Creates JournalMembershipApplication
```

**Existing Fields in Controller:**
- `journal_id` (JournalMembershipApplication - Required)
- `requested_role` (JournalMembershipApplication - Required)
- `academic_type` (AcademicProfile - Required)
- `highest_degree`, `academic_position`, `institution_type`, `institution_id`, `institution`, `department`, `country`, `biography`, `research_interests`, `institutional_email`, `orcid`, `sinta_id`, `scopus_author_id`, `google_scholar_url` (AcademicProfile - Optional)

**Domain:**
- The current controller heavily mixes Academic Identity with Membership Intent.
- It does **not** handle Reviewer Capability fields.
- It does **not** handle Declarations or Recruitment Source.

---

## 3. ACADEMIC PROFILE AUDIT

**Canonical Implementation:**
- Fields: `academic_type`, `highest_degree`, `academic_position`, `institution_type`, `institution_id`, `institution`, `department`, `country`, `biography`, `research_interests`, `institutional_email`, `orcid`, `sinta_id`, `scopus_author_id`, `google_scholar_url`, `expertise` (array).
- Table: `academic_profiles`
- Scope: GLOBAL (Not journal-specific).
- Current Write Path: `JournalMembershipApplicationController@store`, `JournalMembershipApplicationController@update`, `AcademicProfileController@update`.
- It supports Lecturers, Researchers, and Independent profiles.
- Academic fields are correctly decoupled from reviewer authorization.

---

## 4. USER IDENTITY AUDIT

The canonical identity architecture is clean:
```text
USER
 ├── AcademicProfile (Global)
 ├── Course Membership / Enrollment (LMS)
 ├── JournalMembership (Per Journal)
 └── SubmissionAuthor (Per Submission)
```
There are no competing models like `JournalUser` or `ReviewerUser`. `AcademicProfile` successfully centralizes the researcher identity.

---

## 5. JOURNAL MEMBERSHIP AUDIT

- Implemented via `JournalMembership`.
- Can hold roles like `owner`, `editor`, `reviewer`.
- A single user can hold different roles in different journals.
- Creation happens downstream of `JournalMembershipApplication` verification/approval.

---

## 6. REVIEWER CAPABILITY AUDIT

**Canonical Implementation:**
- Model: `JournalReviewerCapability`
- Table: `journal_reviewer_capabilities`
- Relationship: Belongs to `JournalMembership` (foreign key `journal_membership_id`).
- Fields:
  - `available_for_review` (boolean, default true)
  - `max_reviews_per_month` (integer, default 2)
  - `years_of_experience` (integer, nullable)
  - `previous_experience` (text, nullable)

**Findings:**
- Missing UI/Creation Path: The `JournalMembershipApplicationController` does NOT collect these fields for requested reviewers. When a reviewer application is approved, there is currently no obvious pipeline to populate these fields from the application.

---

## 7. RECRUITMENT SOURCE AUDIT

- Added in Phase 7 via migration `2026_09_17_044444_add_recruitment_and_declarations_to_journal_membership_applications_table`.
- Exact type: `$table->string('recruitment_source')->nullable();`
- Current Usage: **None.** The field is in the database schema but is completely absent from `JournalMembershipApplicationController`, validation requests, and the Blade views. It does not affect authorization.

---

## 8. DECLARATIONS AUDIT

- Added in Phase 7 via the same migration `2026_09_17_044444`.
- Exact type: `$table->json('declarations')->nullable();`
- Model Cast: `'declarations' => 'array'` in `JournalMembershipApplication`.
- Current Usage: **None.** Similar to `recruitment_source`, it is completely missing from the application controller and UI.

---

## 9. LEGACY ReviewerApplication AUDIT

- **Remaining routes:** `journal/reviewer/apply` and `journal/reviewer/application` still exist in `routes/web.php` and route to `ReviewerApplicationController`.
- **Eligibility Dependency:** The backend eligibility (`EditorialDeskController@eligibleReviewers`) correctly falls back to accepted `ReviewerApplication` if a capability is missing.
- **Can the new Journal Registration UI be built without exposing ReviewerApplication?**
  Yes. The new UI should purely target `JournalMembershipApplication` and `JournalReviewerCapability`. `ReviewerApplication` can and should be hidden from the UI to prevent duplicate applications, remaining strictly as a database-level legacy authorization fallback until migration is performed.

---

## 10. VERIFICATION UI/UX AUDIT

- `admin/journal/membership-verifications` (routed to `MembershipVerificationController`) processes applications.
- Statuses: `draft` -> `submitted` -> `under_review` -> `approved` / `rejected` / `needs_revision`.

---

## 11. CURRENT JOURNAL USER UX AUDIT

- Browser presentation relies on standard Blade layouts (`app-main-outer`, `app-main-inner`).
- The current UX mixes academic profile creation and journal application into one step, making the form excessively long and cumbersome.

---

## 12. AUTHENTICATION BOUNDARY AUDIT

**BLOCKER DISCOVERED.**

Normal LMS Registration:
- Vue SPA -> API POST `/api/register` -> Returns JWT token.

Journal Membership Application:
- Blade View -> Requires `auth` middleware (web session guard).

A newly registered normal LMS user **cannot** safely transition into `/journal/membership-applications/create` without being forced to log in again via a web session form. The JWT token does not bridge to the Laravel web session. 

---

## 13. PROPOSED JOURNAL REGISTRATION UX — READINESS AUDIT

```text
/journal/register

Step 1 — Account                     -> BLOCKED (JWT vs Web Session boundary)
Step 2 — Academic Identity           -> PARTIALLY READY (Backend exists, needs decoupled UI)
Step 3 — Institution                 -> PARTIALLY READY (Backend exists, needs decoupled UI)
Step 4 — Researcher Profile          -> PARTIALLY READY (Backend exists, needs decoupled UI)
Step 5 — Journal & Membership Role   -> READY (JournalMembershipApplication handles this)
Step 6 — Reviewer Information        -> BLOCKED (Capability fields missing in application controller)
Step 7 — Declarations                -> BLOCKED (Missing in application controller)
Step 8 — Review & Submit             -> READY
```

---

## 14. ROLE-CONDITIONAL UX AUDIT

The backend architecture theoretically supports conditional logic since `requested_role` is a distinct field. However, the current `JournalMembershipApplicationController` does not dynamically collect reviewer-specific fields (capabilities, recruitment source, declarations) when the role is `reviewer`.

---

## 15. FIELD OWNERSHIP MATRIX

| UI Concept           | Actual Field | Destination                                    | Domain          | Existing? | UI Ready? | Notes |
| -------------------- | ------------ | ---------------------------------------------- | --------------- | --------- | --------- | ----- |
| Name                 | `name`       | User                                           | Identity        | Yes       | No        | JWT Auth gap |
| Email                | `email`      | User                                           | Identity        | Yes       | No        | JWT Auth gap |
| Degree               | `highest_degree` | AcademicProfile                            | Academic        | Yes       | Yes       | |
| Academic Position    | `academic_position` | AcademicProfile                         | Academic        | Yes       | Yes       | |
| Institution          | `institution` | AcademicProfile                               | Academic        | Yes       | Yes       | |
| Department           | `department` | AcademicProfile                                | Academic        | Yes       | Yes       | |
| ORCID                | `orcid`      | AcademicProfile                                | Academic        | Yes       | Yes       | |
| SINTA                | `sinta_id`   | AcademicProfile                                | Academic        | Yes       | Yes       | |
| Scopus               | `scopus_author_id` | AcademicProfile                          | Academic        | Yes       | Yes       | |
| Requested Role       | `requested_role` | JournalMembershipApplication               | Membership      | Yes       | Yes       | |
| Recruitment Source   | `recruitment_source` | JournalMembershipApplication           | Onboarding      | Yes       | No        | Missing in Controller |
| Available for Review | `available_for_review` | JournalReviewerCapability            | Reviewer        | Yes       | No        | Missing in Controller |
| Max Reviews / Month  | `max_reviews_per_month`| JournalReviewerCapability            | Reviewer        | Yes       | No        | Missing in Controller |
| Years of Experience  | `years_of_experience` | JournalReviewerCapability             | Reviewer        | Yes       | No        | Missing in Controller |
| Previous Experience  | `previous_experience` | JournalReviewerCapability             | Reviewer        | Yes       | No        | Missing in Controller |
| Declarations         | `declarations` | JournalMembershipApplication                 | Onboarding      | Yes       | No        | Missing in Controller |

---

## 16. DUPLICATION RISK AUDIT

### SAFE TO REUSE
- `AcademicProfile` (Global, safe to update).
- `JournalMembershipApplication` (Canonical application model).

### LEGACY DUPLICATE
- `ReviewerApplication` (Legacy routes still exposed in `web.php` such as `journal/reviewer/apply`). This will cause high duplication risk if not hidden.

---

## 17. UX DATA LIFECYCLE AUDIT

Missing Lifecycle Step:
When a `JournalMembershipApplication` with `requested_role = reviewer` is verified and approved, it creates a `JournalMembership`. However, there is currently no mechanism to transfer the reviewer capability fields (which should theoretically be collected during the application) into the `JournalReviewerCapability` table upon approval.

---

## 18. PHASE 8 DEPENDENCY AUDIT

1. Does the UI need ReviewerApplication to function? **NO.**
2. Can canonical Journal Membership Application support the new registration flow now? **YES**, but the controller needs to be updated to accept the new fields.
3. Can ReviewerApplication remain as compatibility fallback? **YES.**
4. Would implementing the new UI now create duplicate data? **NO**, provided we hide the legacy routes.
5. Is migration of legacy data required before users can register? **NO.**
6. Is retirement of ReviewerApplication required before registration UI can launch? **NO.**

**Conclusion:** We CAN build the new UX first while keeping `ReviewerApplication` safely as legacy compatibility.

---

## 19. BROWSER / UI VERIFICATION

Skipped (Strict Read-Only code analysis performed).

---

## 20. SECURITY BOUNDARY CHECK

The boundary is conceptually intact. A submitted application goes into `draft` -> `submitted` status and explicitly requires administrative verification before generating a `JournalMembership` and its associated capabilities. The registration UX will not accidentally grant operational authorization.

---

## 21. TEST FOUNDATION AUDIT

- `Phase7CanonicalReviewerEligibilityTest` protects the backend eligibility logic.
- `Phase5GReviewerAssignmentTest` protects assignment bounds.
- UI tests for the new application flow will need to be written.

---

## 22. FINAL READINESS ASSESSMENT

```text
JOURNAL REGISTRATION UI/UX FOUNDATION AUDIT: BLOCKED

CURRENT REGISTRATION: NEEDS WORK
MEMBERSHIP APPLICATION: NEEDS WORK
ACADEMIC PROFILE: PASS
USER IDENTITY: PASS
JOURNAL MEMBERSHIP: PASS
REVIEWER CAPABILITY: PASS
RECRUITMENT SOURCE: NEEDS WORK
DECLARATIONS: NEEDS WORK
VERIFICATION FLOW: PASS
AUTHENTICATION FLOW: NEEDS WORK
CURRENT JOURNAL UX: NEEDS WORK
FIELD OWNERSHIP: PASS
ROLE-CONDITIONAL UX: NEEDS WORK
DATA LIFECYCLE: NEEDS WORK
DUPLICATION RISK: HIGH
PHASE 8 REQUIRED BEFORE REGISTRATION UX: NO

REGISTRATION UI/UX RECOMMENDATION:
FIX FOUNDATION FIRST

APPLICATION CODE CHANGED: NO
DATABASE CHANGED: NO
DATA MODIFIED: NO
MIGRATIONS CREATED: NO
ROUTES CHANGED: NO
AUTHORIZATION CHANGED: NO
AUTHENTICATION CHANGED: NO
UI CHANGED: NO
TESTS MODIFIED: NO
ReviewerApplication MODIFIED: NO
PHASE 8 IMPLEMENTED: NO
```

## BLOCKERS
1. **Authentication Boundary:** Vue SPA JWT vs Blade Web Session prevents seamless registration.
2. **Missing Controller Logic:** `JournalMembershipApplicationController` ignores `recruitment_source`, `declarations`, and Reviewer Capability fields.
3. **Missing Lifecycle Transfer:** Approving a reviewer membership application does not currently seed the `JournalReviewerCapability`.

## RECOMMENDED FOLLOW-UP
1. Bridge the authentication gap (e.g., dedicated web-based registration for Journals, or a secure token handoff).
2. Update `JournalMembershipApplicationController` to accept the missing Phase 7 fields.
3. Update the verification logic to seed `JournalReviewerCapability` upon approval.
4. Hide/Disable the legacy `ReviewerApplicationController` routes from the UI.
