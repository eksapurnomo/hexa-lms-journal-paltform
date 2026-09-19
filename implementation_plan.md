# JRAF Step 1 — Authentication Safety + Academic Profile API

## 1. Objective
Implement the first two foundation subphases (JRAF-1 and JRAF-2) required for the future Journal Registration API and Flutter/mobile clients. This ensures the existing JWT foundation is safe and the canonical AcademicProfile API exposes the correct database fields.

## 2. Architecture Decision
HexaLMS uses:
- **JWT/API**: Primary user-facing authentication foundation for Vue/React Web, Flutter Android, Flutter iOS, and Laravel API.
- **Web Session**: Compatibility layer for existing Blade/Admin.

**Critical Boundaries:**
- DO NOT create a JWT → Web Session bridge.
- Admin Membership Verification remains Blade + Web Session.

## 3. Current-State Findings
- **Login Vulnerability:** `UserController@login` currently issues a JWT regardless of whether the user account is active.
- **Activation Dependency:** `AccountActivationController` currently relies on `auth()->user()`. If an inactive user loses their initial JWT, they cannot log in to activate, leading to a dead end.
- **Academic Profile API:** `Api\AcademicProfileController` is missing several canonical fields that exist in the database and blade views.

## 4. JRAF-1 Implementation Plan

### Activation Flow
The activation endpoint MUST NOT require `auth:api`, `auth()->user()`, or an existing JWT. 
- **Endpoint:** Expose a public API endpoint (e.g. `POST /api/account/activate`).
- **Identifier:** The API must accept the activation credential (e.g. email and code) instead of resolving from `auth()->user()`.
- **Refactor:** Perform a MINIMAL refactor on the existing `AccountActivationController` / `AccountActivationRepository` so the target user is resolved from the credential.
- **Semantics:** Do NOT invent a new activation mechanism. Reuse existing token generation, validation, expiration, and idempotency semantics. Preserve existing response behavior.

### Login Security Flow
- **Inactive User:** If a user is inactive, login is rejected and NO JWT is issued.
- **Active User:** Login succeeds and a JWT is issued.
- Do NOT redesign JWT, introduce OAuth, social login, refresh-token redesign, or a new authentication system.

## 5. JRAF-2 Implementation Plan

### Academic Profile Verification
- **INSPECT FIRST:** Inspect the actual `academic_profiles` migration/schema/model before implementing. Verify the presence of canonical fields (`academic_type`, `highest_degree`, `academic_position`, `institution_type`, `institution_id`, `institution`, `department`, `country`, `biography`, `research_interests`, `institutional_email`, `orcid`, `sinta_id`, `scopus_author_id`, `google_scholar_url`, `expertise`).
- **STOP Condition:** If the schema/model does not match, STOP and report the mismatch. DO NOT invent a migration.

### Upgrade API
- Upgrade `GET /profile/academic` and `PATCH /profile/academic`.
- Append canonical fields in a backward-compatible manner without creating a new global response format.
- Preserve existing frontend compatibility.

## 6. Ownership/Security Rules
- **Profile Ownership:** Authenticated JWT user accesses/updates their OWN AcademicProfile only. Do NOT allow an arbitrary `user_id` to update another user's profile.
- **Global Identity:** AcademicProfile remains GLOBAL academic identity. It must NOT contain `JournalMembership`, `ReviewerCapability`, `recruitment_source`, `declarations`, reviewer authorization, or journal role.

## 7. Backward Compatibility
- Retain existing API routes and frontend compatibility.
- Ensure the original registration flow still works correctly.

## 8. Test Plan

### JRAF-1 Tests
1. Active user login succeeds and receives JWT.
2. Inactive user login is rejected.
3. Inactive login does NOT issue JWT.
4. Valid activation works WITHOUT JWT.
5. Invalid activation fails.
6. Activation credential cannot activate another user.
7. Already-active behavior is safe.
8. Registration remains functional.

### JRAF-2 Tests
1. Authenticated user can GET own AcademicProfile.
2. Authenticated user can PATCH own AcademicProfile.
3. Canonical fields are supported.
4. Unauthenticated access is rejected.
5. User A cannot update User B's profile.
6. Existing profile behavior remains compatible.

## 9. Regression Test Plan
Run appropriate regression tests after implementation.

## 10. Explicit Out-of-Scope
- Journal Membership Application API
- Journal Registration UI
- Flutter UI
- Reviewer Capability API
- Journal Membership API
- Membership Status API
- ReviewerApplication migration
- ReviewerApplication deletion
- Phase 8
- OAuth
- Social Login
- Refresh-token redesign
- JWT → Web Session bridge
- Admin API conversion
- Database reset
- Destructive migration

## 11. Expected Files to Inspect/Change
- `app/Http/Controllers/UserController.php`
- `app/Http/Controllers/AccountActivationController.php`
- `app/Http/Controllers/Api/AcademicProfileController.php`
- `routes/api.php`
- `app/Http/Requests/StudentRegisterRequest.php`
- `app/Repositories/UserRepository.php`
- `app/Repositories/AccountActivationRepository.php`
- `app/Models/User.php`
- `app/Models/AcademicProfile.php`
- Test files

## 12. Stop Conditions
- Schema mismatch in `academic_profiles`.

## 13. Final Acceptance Criteria

JRAF STEP 1 STATUS: PLANNED

JRAF-1 AUTHENTICATION SAFETY: PLANNED
INACTIVE LOGIN PROTECTION: REQUIRED
ACCOUNT ACTIVATION API: REQUIRED
JRAF-2 ACADEMIC PROFILE API: PLANNED
JWT AUTHENTICATION: PRESERVED
USER OWNERSHIP SECURITY: REQUIRED

DATABASE SCHEMA CHANGED: NO
DATABASE DATA RESET: NO
REVIEWERAPPLICATION MODIFIED: NO
JOURNAL MEMBERSHIP APPLICATION API IMPLEMENTED: NO
PHASE 8 IMPLEMENTED: NO

READY FOR IMPLEMENTATION: YES
READY FOR JRAF-3: NO
