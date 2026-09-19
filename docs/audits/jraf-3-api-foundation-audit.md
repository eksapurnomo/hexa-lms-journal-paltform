# JRAF-3 API FOUNDATION AUDIT

## 1. Domain Inventory
- **JournalMembershipApplication**: Manages journal onboarding (Draft -> Approved). Currently Web-only. API gap exists.
- **JournalMembership**: Represents the granted journal role. API-ready, used actively.
- **JournalReviewerCapability**: Defines reviewer metadata (capacity, availability). Missing from application lifecycle.
- **AcademicProfile**: Global canonical academic identity. Integrated into Web controllers.
- **ReviewerApplication**: Legacy compatibility model.

## 2. Database Schema Audit
- `journal_membership_applications`: Contains `user_id`, `journal_id`, `requested_role`, `status`, `submitted_at`, `reviewed_at`, `reviewed_by`, `reviewer_note`, `recruitment_source`, `declarations`. (Note: Academic fields reside globally in `academic_profiles`, which is architecturally correct).
- Schema correctly supports transitions: `draft`, `submitted`, `under_review`, `needs_revision`, `approved`, `rejected`.

## 3. Current Application Lifecycle
Existing workflow transitions (Web-only):
- User creates application (Status: `draft`)
- User updates academic profile & app fields
- User submits (Status: `submitted`)
- Admin reviews (Status: `under_review` / `needs_revision` / `approved` / `rejected`)

## 4. Current Admin Approval Flow (CRITICAL)
- **Implemented:** `MembershipVerificationController@updateStatus` successfully transitions application to `approved` and creates a `JournalMembership`.
- **Missing Dependency:** It NEVER creates a `JournalReviewerCapability`. The capability model is currently only instantiated in test fixtures.
- **Conclusion:** JRAF-3 IMPLEMENTATION DEPENDENCY.

## 5. Role Lifecycle
- Roles (`owner`, `editor`, `reviewer`) are requested by the user but strictly granted by Admin.
- The `requested_role` safely does not equal granted authorization.

## 6. Reviewer Capability Lifecycle
- `JournalReviewerCapability` exists in schema and models, but is completely absent from the actual application controllers and admin approval flows.
- `max_reviews_per_month` exists in schema but enforcement is currently DEFERRED.

## 7. Journal Membership Application API Gap
- API endpoints do NOT exist. Existing implementation relies entirely on `routes/web.php` (`JournalMembershipApplicationController`).

## 8. API Contract Design Readiness
Minimal recommended contract for JRAF-3 (respecting existing API conventions):
- `GET /api/journals/{journal}/membership-application`
- `POST /api/journals/{journal}/membership-application/draft`
- `POST /api/journals/{journal}/membership-application/submit`

## 9. Academic Profile Integration
- **Current Behavior:** The Web controller explicitly updates the global `AcademicProfile` using user inputs before creating/updating the application.
- **Recommendation:** The API must follow this pattern, ensuring the canonical `AcademicProfile` is updated, avoiding duplicated identity fields in the application payload.

## 10. Recruitment Source & Declarations
- `recruitment_source` (string) and `declarations` (json) correctly exist in the schema. They serve as metadata/onboarding info and properly do not grant authorization.

## 11. ReviewerApplication Legacy
- Heavily relied upon in `EditorialDeskController` as a fallback when `JournalReviewerCapability` is missing.
- Serves legacy web routes.
- **Must be preserved** strictly for legacy compatibility.

## 12. Authorization & Isolation
- User scoped to `Auth::id()`.
- Isolated by `journal_id`. 
- Users are correctly blocked from arbitrary status/role manipulation.

## 13. Business Logic Duplication
- The Web controller (`JournalMembershipApplicationController@store` / `@update`) contains significant domain logic covering Academic Profile syncing, `independent` institution handling, and idempotency checks.
- Extracting this into a `JournalMembershipApplicationService` is highly recommended before or during API implementation to avoid duplicated logic between Web and API.

## 14. Flutter Readiness
- The schema and authentication foundation are fully capable of serving stateless JSON payloads required by mobile clients.

## 15. Final Recommendation
1. **Is the backend ready for JRAF-3 implementation?** Mostly, but blocked by a core dependency.
2. **What MUST be implemented before the API controller?** `MembershipVerificationController` must be updated to instantiate a `JournalReviewerCapability` when a `reviewer` application is approved.
3. **What can remain unchanged?** Global identity architecture (`AcademicProfile`) and JWT boundaries.
4. **Is a minimal service extraction required?** Yes, domain logic inside the Blade controller should be extracted to prevent duplication.
5. **What API endpoints are actually required?** Draft creation/update, submission, and status retrieval.
6. **What legacy dependencies must remain temporarily?** `ReviewerApplication` must remain for eligibility fallback.

---

JRAF-3 API FOUNDATION AUDIT: READY WITH DEPENDENCIES

APPLICATION API FOUNDATION: PARTIAL

APPLICATION LIFECYCLE: READY

ADMIN APPROVAL FLOW: PARTIAL

REVIEWER CAPABILITY LIFECYCLE: BLOCKED

ACADEMIC PROFILE INTEGRATION: READY

AUTHORIZATION: PASS

JOURNAL ISOLATION: PASS

IDEMPOTENCY: PASS

FLUTTER API READINESS: READY

REVIEWERAPPLICATION LEGACY: COMPATIBILITY

DATABASE CHANGED: NO

CODE CHANGED: NO

JRAF-3 IMPLEMENTED: NO

READY FOR JRAF-3 IMPLEMENTATION: NO
