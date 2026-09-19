# FINAL FOUNDATION AUDIT

## HexaLMS Journal Platform
**Mode:** STRICT READ-ONLY ARCHITECTURE AND LIFECYCLE AUDIT
**Objective:** Determine the safety and readiness of Lifecycle A (Registration -> Web Session) and Lifecycle B (Application -> Approval -> Membership -> Capability).

---

## 1. EXECUTIVE SUMMARY

The final architecture audit reveals severe foundational disconnects in both Lifecycle A and Lifecycle B that **BLOCK** the immediate implementation of the Journal Registration UI/UX.

1. **Lifecycle A is broken by an authentication boundary mismatch.** The LMS registration issues a stateless JWT, but the Journal Application pages are protected by stateful web session middleware. A standard user currently has no mechanism to establish a web session.
2. **Lifecycle B is broken by a missing data transfer pipeline.** When an admin approves a reviewer application, the `JournalMembership` is created, but the `JournalReviewerCapability` is completely ignored. Reviewer capability metadata is currently stranded.

---

## 2. LIFECYCLE A — USER REGISTRATION → WEB SESSION

### A1. After `/api/register`, what exactly exists?
**Actual Result:**
- User created in the database.
- An activation code is generated (`AccountActivationRepository`).
- A JWT token is issued immediately and returned in the JSON response.
- **Web session is absent.** 

### A2. Is the account immediately usable?
The account is created, but the `is_active` flag remains false until activation. The user can technically log in via the Vue SPA using the returned JWT to hit API endpoints, but some API logic checks `is_active`. 

### A3. After successful activation, is the user automatically authenticated?
**Yes, but only via JWT.**
The `AccountActivationController@activateAccount` endpoint verifies the code, updates `is_active = true`, and returns a new JWT token. It **does not** create a Laravel web session.

### A4. Can a newly registered normal LMS user access `/journal/membership-applications/create` without manually logging in through a separate web login?
**NO.**
The user has a JWT, but the Journal Membership Application routes require a stateful web session cookie. They will be treated as unauthenticated by the web guard.

### A5. Which guard protects Journal Membership Application?
- **Middleware:** `auth` (defaults to the `web` guard).
- **Authentication Source:** Laravel Session.
- **Expected session/token:** A valid session cookie (`laravel_session`), NOT a Bearer JWT.

### A6. Can we safely introduce `/journal/register` without changing existing behavior?
Yes. The existing `UserController@register` and Vue SPA flows can be left untouched. The Journal Registration flow can either use a new web controller that issues a web session, or a secure bridge endpoint that exchanges a valid JWT for a web session.

### A7. Can the Journal registration flow safely establish a web session after successful account activation?
Yes, using `Auth::guard('web')->login($user)`. Currently, standard users have no web login mechanism (only admins/instructors use `LoginController`), so establishing a session specifically for Journal applicants is safe and isolated from the SPA.

### A8. Does the current architecture permit this?
**BLOCKED.**
The current architecture does not provide any web login mechanism for standard users. If a user registers via the SPA, they can never access the Blade-based Journal forms without a bridging mechanism being built first.

### Security Findings for Lifecycle A
- **JWT/web-session confusion:** High risk. Mixing Vue SPA (JWT) and Blade (Web Session) without a clear token exchange will lead to UX dead-ends.
- **Privilege Escalation:** Safe. The LMS registration sets the default role (student). `JournalMembership` dictates Journal privileges, keeping boundaries intact.

---

## 3. LIFECYCLE B — MEMBERSHIP APPLICATION → APPROVAL

### B1. When an Application is submitted, what exact records are created/updated?
- `AcademicProfile` (Created/Updated via `firstOrCreate` and `update`).
- `JournalMembershipApplication` (Created with `status = draft`).
*(Note: `JournalReviewerCapability` is NOT created. `JournalMembership` is NOT created.)*

### B2. What happens when an application is approved?
In `MembershipVerificationController@updateStatus`:
- Application `status` becomes `approved`.
- `reviewed_by` and `reviewed_at` are set.
- **`JournalMembership` IS created automatically.**

### B3. What values are assigned?
- `journal_id` = `$application->journal_id`
- `user_id` = `$application->user_id`
- `role` = `$application->requested_role`
- `status` = `'active'`

### B4. What happens for `member` vs `reviewer` vs `editor`?
They are treated **identically**. The code simply assigns `$application->requested_role` to the `JournalMembership` role. No role-specific logic exists in the approval method.

### B5. Does approval currently create `JournalReviewerCapability` for an approved reviewer?
**NO.**
The approval logic entirely ignores reviewer capabilities. An approved reviewer will have an active `JournalMembership` but no `JournalReviewerCapability`.

### B6. If capability is not created, where could reviewer capability onboarding data safely live before approval?
The `JournalMembershipApplication` is the correct canonical container for onboarding metadata before approval. It should hold the pending capability fields (e.g., `available_for_review`, `max_reviews_per_month`, `years_of_experience`, `previous_experience`) as JSON or dedicated columns, so they can be transferred to `JournalReviewerCapability` upon approval.

---

## 4. REVIEWER CAPABILITY DATA LIFECYCLE

1. **Where they currently exist:** `journal_reviewer_capabilities` table.
2. **Can Membership Application store them?** Currently, no dedicated columns exist in `JournalMembershipApplication` for capability fields.
3. **Can approval transfer them?** Currently, no. The logic is entirely missing from `MembershipVerificationController`.
4. **Does ReviewerApplication store equivalent values?** Yes, the legacy `ReviewerApplication` table stores years of experience, but it is isolated from the new canonical model.
5. **Canonical source of truth before membership:** None exists in the new architecture yet.

---

## 5. RECRUITMENT SOURCE & DECLARATIONS

- **Current Write/Read Path:** They exist in the `journal_membership_applications` database schema (added via migration `2026_09_17_044444`).
- **Validation/UI:** Completely missing from `JournalMembershipApplicationController` and the Blade views.
- **Approval Usage:** Ignored by `MembershipVerificationController`.
- **Safety:** They safely remain as onboarding metadata and do not affect authorization.

---

## 6. REVIEWERAPPLICATION COMPATIBILITY

**Verified:** The legacy compatibility fallback works perfectly at the authorization layer. `EditorialDeskController::eligibleReviewers()` successfully queries for an active canonical capability OR an accepted legacy `ReviewerApplication`.
**Warning:** `ReviewerApplicationController` routes are still exposed in `web.php` (`journal/reviewer/apply`). If not hidden, users can submit duplicate applications under the old system.

---

## 7. APPROVAL STATE MACHINE

**Actual Flow:**
```text
draft -> submitted -> under_review -> approved / rejected / needs_revision
```
- **Who can transition:** Admin users authenticated via the `web` guard and authorized by `adminauth` middleware.
- **Transactional:** YES. The approval logic is wrapped in `DB::beginTransaction()`.
- **Membership Creation:** Occurs exclusively at approval.

---

## 8. CRITICAL DATA OWNERSHIP CHECK

| Data                | Canonical Owner                | Created/Updated When | Current Status |
| ------------------- | ------------------------------ | -------------------- | -------------- |
| Name                | User                           | Registration         | READY          |
| Email               | User                           | Registration         | READY          |
| Password            | User                           | Registration         | READY          |
| Activation          | User/AccountActivationRepo     | Activation           | READY          |
| Academic degree     | AcademicProfile                | Application          | READY          |
| Academic position   | AcademicProfile                | Application          | READY          |
| Institution         | AcademicProfile                | Application          | READY          |
| ORCID               | AcademicProfile                | Application          | READY          |
| Requested role      | JournalMembershipApplication   | Application          | READY          |
| Recruitment source  | JournalMembershipApplication   | Application          | MISSING IN UI  |
| Declarations        | JournalMembershipApplication   | Application          | MISSING IN UI  |
| Reviewer capability | JournalReviewerCapability      | Approval             | MISSING PIPELINE |
| Journal role        | JournalMembership              | Approval             | READY          |
| Membership status   | JournalMembership              | Approval             | READY          |

---

## 9. TRANSACTION / ATOMICITY & IDEMPOTENCY AUDIT

- **Atomicity:** The approval process uses `DB::beginTransaction()`. If creating the `JournalMembership` fails, the application status update rolls back safely.
- **Idempotency:** Protected. If an admin attempts to approve an already-approved application, the code checks `$activeExists`. If an active membership for that role already exists, it throws a safe `Exception` and rolls back.
- **Journal Isolation:** Protected. The queries strictly filter by `$application->journal_id`.

---

## 10. MINIMAL IMPLEMENTATION RECOMMENDATION

### Step A — Registration Authentication Bridge
**Recommendation:** Implement a secure JWT-to-Web-Session bridge. When the Vue SPA completes registration and activation, it can POST the valid JWT to a new endpoint (e.g., `/api/auth/web-session-bridge`). This endpoint verifies the JWT and calls `Auth::guard('web')->login($user)`. This is the smallest safe mechanism that preserves the existing decoupled LMS architecture.

### Step B — Membership Approval Lifecycle
**Recommendation:** 
1. Add reviewer capability fields (e.g., as a JSON `reviewer_metadata` column or distinct columns) to `JournalMembershipApplication`.
2. Update `JournalMembershipApplicationController` to collect capabilities, `recruitment_source`, and `declarations`.
3. Update `MembershipVerificationController@updateStatus` so that when `requested_role === 'reviewer'`, it extracts the capabilities from the application and creates the `JournalReviewerCapability` record inside the existing transaction.

---

## 11. PHASE 8 DEPENDENCY

**Is Phase 8 required before fixing these lifecycle foundations?**
**NO.** Phase 8 (Data Migration) deals with legacy data. The lifecycle foundations deal with the intake of *new* applications. The foundations must be fixed first so that new users enter the system cleanly before migrating old ones.

---

## 12. REQUIRED FINAL STATUS

FINAL REGISTRATION + MEMBERSHIP LIFECYCLE AUDIT: BLOCKED

USER REGISTRATION → ACTIVATION: PASS

ACTIVATION → WEB SESSION: BLOCKED

WEB SESSION → JOURNAL APPLICATION: PASS

APPLICATION → VERIFICATION: PARTIAL

VERIFICATION → JOURNAL MEMBERSHIP: PASS

JOURNAL MEMBERSHIP → REVIEWER CAPABILITY: BLOCKED

REVIEWERAPPLICATION COMPATIBILITY: PASS

JOURNAL ISOLATION: PASS

TRANSACTION SAFETY: PASS

IDEMPOTENCY: PASS

PHASE 8 REQUIRED BEFORE FOUNDATION FIX: NO

APPLICATION CODE CHANGED: NO
DATABASE SCHEMA CHANGED: NO
DATABASE DATA CHANGED: NO
ROUTES CHANGED: NO
AUTHENTICATION CHANGED: NO
AUTHORIZATION CHANGED: NO
UI CHANGED: NO
TESTS MODIFIED: NO
REVIEWERAPPLICATION MODIFIED: NO
