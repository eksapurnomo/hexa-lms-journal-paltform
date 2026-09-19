# JOURNAL REGISTRATION API READINESS AUDIT

## HexaLMS Journal Platform
**MODE: STRICT READ-ONLY**

---

## 1. EXECUTIVE SUMMARY

The backend's REST API foundation is **BLOCKED** from immediately supporting a modern Journal Registration flow (Web SPA, Flutter, or Mobile). While a basic JWT authentication layer exists, the Journal domain is overwhelmingly tethered to stateful Blade/web-session controllers. 

Specifically, there are zero APIs for submitting journal membership applications, activating accounts, fetching reviewer capabilities, or even letting standard users view their own memberships. However, the existing JWT infrastructure and isolation boundaries are solid, meaning the backend *can* be upgraded to support APIs without breaking legacy Web Admin functionalities.

---

## 2. EXISTING AUTHENTICATION ARCHITECTURE

- **A1. JWT Issuance:** Issued via `JWTAuth::fromUser($newUser)` during registration (`UserController@register`) and `JWTAuth::attempt()` during login (`UserController@login`).
- **A2. JWT Validation:** Validated via the `auth:api` middleware.
- **A3. User Resolution:** Uses `JWTAuth::user()` or `auth()->user()` within `auth:api` protected routes.
- **A4. Middleware:** `auth:api` protects all core API routes.
- **A5. JWT States:**
  - Missing/Invalid/Expired: Throws `JWTException` (Returns HTTP 401/403).
  - Valid but User Inactive: **Security Gap**. `UserController@login` does *not* check if the user is active before issuing the JWT. The user can authenticate even if inactive.
- **A6. Cross-Platform Readiness:** Yes, the stateless JWT mechanism perfectly supports Web SPA and Flutter without requiring Laravel web sessions or CSRF tokens.

---

## 3. CURRENT API ROUTE INVENTORY

| Domain                 | API Exists | Auth        | Controller | Ready |
| ---------------------- | ---------- | ----------- | ---------- | ----- |
| Registration           | Yes        | None        | `UserController` | Yes |
| Activation             | No         | None        | N/A | No |
| Login                  | Yes        | None        | `UserController` | Partial (No active check) |
| Academic Profile       | Yes        | `auth:api`  | `Api\AcademicProfileController` | Partial (Missing fields) |
| Journal List           | Yes        | None        | `Api\JournalController` | Yes |
| Membership Application | **No**     | N/A         | N/A | No |
| Reviewer               | **No**     | N/A         | N/A | No |
| Submission             | Yes        | `auth:api`  | `Api\SubmissionController` | Yes |

---

## 4. JOURNAL MEMBERSHIP APPLICATION API AUDIT

- **B1. Create Application:** No API exists.
- **B2. Retrieve Application:** No API exists.
- **B3. Update Draft:** No API exists.
- **B4. Submit Application:** No API exists.
- **B5. Application Status:** No API exists.
- **B6. Specific Journal:** Handled correctly at the database level, but no API to access it.
- **B7. Journal Isolation Enforced:** Yes, at the database layer (compound keys / queries).

---

## 5. ACADEMIC PROFILE API AUDIT

- **Endpoints:** `GET /profile/academic` and `PATCH /profile/academic`.
- **Readiness:** **PARTIAL**. 
- **Reason:** The API exists and correctly utilizes `auth:api`, but it does *not* support the newly added canonical fields required for Journal Registration (`academic_type`, `institution_type`, `institution_id`, `institutional_email`, `sinta_id`). These are only validated in the Blade web controller.

---

## 6. REVIEWER ONBOARDING API READINESS

- fields: `recruitment_source`, `declarations`, `available_for_review`, `max_reviews_per_month`, `years_of_experience`, `previous_experience`.
- **Readiness:** **MISSING**. 
- **Reason:** There is absolutely no API support for injecting these fields into an application, nor exposing them to the user.

---

## 7. APPROVAL API AUDIT

- **Admin Approval:** Currently handled via Blade + Web Session (`MembershipVerificationController`).
- **Architectural Acceptability:** **YES**. It is perfectly acceptable to keep Admin Verification in the Blade/web-session environment. User-facing APIs and Admin web portals can seamlessly coexist over the same database domain. We do not need to convert Admin routes to APIs just because the frontend is an SPA.

---

## 8. MEMBERSHIP API AUDIT

- **Existing API:** `GET admin/journals/{journal}/memberships` (`Api\JournalMembershipController`).
- **Authorization:** `Gate::authorize('viewAny', [JournalMembership::class, $journal])` restricts this entirely to Admins and Journal Owners.
- **User Facing APIs:** **MISSING**. Standard users have no API endpoint to query "my journal memberships", "my roles", or "my statuses".

---

## 9. JOURNAL AUTHORIZATION (JWT TO DOMAIN)

- **Isolation Check:** PASS. The API correctly scopes capabilities per journal. For example, `EditorialDeskController` routes are strictly gated by `Gate::authorize('editorial-process', [$submission])`, which verifies the specific journal context against the user's membership.
- A user holding a reviewer role in Journal A cannot accidentally utilize those privileges in Journal B.

---

## 10. FLUTTER READINESS

| Feature | Readiness |
| :--- | :--- |
| `POST` Registration | **READY** |
| `POST` Activation | **MISSING** |
| `POST` Login | **PARTIAL** |
| `GET` Journal discovery | **READY** |
| `GET / POST / PATCH` Membership application | **MISSING** |
| `POST` Submit application | **MISSING** |
| `GET` Membership status | **MISSING** |
| `GET` Reviewer state | **MISSING** |

---

## 11. API SECURITY AUDIT

- **JWT Handling:** Standard `tymon/jwt-auth` implementation. Safe.
- **Journal Isolation:** Safe. Database enforces uniqueness.
- **Role Escalation:** Safe. Standard registration defaults to LMS Student. API membership controllers explicitly block self-escalation.
- **Inactive Account Handling:** **VULNERABILITY**. The JWT `login` endpoint fails to verify `is_active`, allowing unactivated accounts to obtain tokens.
- **Application Ownership:** Protected by DB constraints, but API endpoints are completely missing so exposure is nil.

---

## 12. BLADE/API COEXISTENCE

- The domain models (`User`, `JournalMembership`, `JournalMembershipApplication`, `AcademicProfile`) are cleanly separated from the presentation layer.
- The Blade controllers (e.g. `JournalMembershipApplicationController`) contain too much embedded business logic (such as profile syncing). This logic must be extracted to Services or FormRequests before the API can be safely built without duplicating code.

---

## 13. LEGACY REVIEWERAPPLICATION COMPATIBILITY

- The API implementation can proceed safely while `ReviewerApplication` remains untouched. The frontend will hit the new `JournalMembershipApplication` endpoints, while the backend eligibility checks continue to support legacy records.

---

## 14. API READINESS MATRIX

| Capability             | Current API | Auth | Journal Scoped | Flutter Ready | Action |
| ---------------------- | ----------- | ---- | -------------- | ------------- | ------ |
| Register               | Yes         | None | N/A            | Yes           | Keep |
| Activate               | No          | None | N/A            | No            | Build API |
| Login                  | Yes         | None | N/A            | Partial       | Add active check |
| Academic Profile       | Yes         | JWT  | No (Global)    | Partial       | Add missing fields |
| Journal List           | Yes         | None | Yes            | Yes           | Keep |
| Membership Application | No          | JWT  | Yes            | No            | Build API |
| Application Draft      | No          | JWT  | Yes            | No            | Build API |
| Application Submit     | No          | JWT  | Yes            | No            | Build API |
| Application Status     | No          | JWT  | Yes            | No            | Build API |
| Membership Status      | No          | JWT  | Yes            | No            | Build API |
| Reviewer Capability    | No          | JWT  | Yes            | No            | Build API |
| Admin Verification     | Web Only    | Web  | Yes            | N/A           | Keep as Web |

---

## 15. CURRENT VS TARGET ARCHITECTURE

**CURRENT:**
```text
User -> JWT -> /api/register
User -> Web Session -> /journal/membership-applications (Broken Handoff)
```

**TARGET:**
```text
User -> JWT -> Web SPA / Flutter
User -> JWT -> /api/journals/{journal}/applications (New API)
```

---

## 16. MINIMAL IMPLEMENTATION RECOMMENDATION

We can proceed to the **JOURNAL REGISTRATION API FOUNDATION**. 
The minimal implementation phases are:

1. **JRAF-1: Authentication Safety**
   - Create an API endpoint for Activation.
   - Enforce `is_active` check on `UserController@login`.
2. **JRAF-2: Profile API Upgrade**
   - Update `Api\AcademicProfileController` to support all canonical fields.
3. **JRAF-3: Application API Foundation**
   - Create `Api\JournalMembershipApplicationController` to handle drafts, submissions, and status retrieval. Include capability mapping.
4. **JRAF-4: Membership API Expansion**
   - Add a "My Memberships" endpoint for standard users.

---

## 17. PHASE 8 DEPENDENCY

Phase 8 (Data Migration) is **NOT REQUIRED** before implementing the API Foundation. The APIs will serve new applicants and interface perfectly with the existing canonical database schema.

---

## 18. FINAL STATUS

JOURNAL REGISTRATION API READINESS AUDIT: BLOCKED

JWT AUTHENTICATION: PASS

USER REGISTRATION API: PASS

ACCOUNT ACTIVATION API: BLOCKED

JOURNAL API FOUNDATION: PASS

MEMBERSHIP APPLICATION API: BLOCKED

ACADEMIC PROFILE API: PARTIAL

REVIEWER CAPABILITY API: BLOCKED

MEMBERSHIP STATUS API: BLOCKED

JOURNAL AUTHORIZATION: PASS

JOURNAL ISOLATION: PASS

FLUTTER READINESS: BLOCKED

BLADE/API COEXISTENCE: PASS

SECURITY: PARTIAL

PHASE 8 REQUIRED BEFORE API FOUNDATION: NO

APPLICATION CODE CHANGED: NO
DATABASE SCHEMA CHANGED: NO
DATABASE DATA CHANGED: NO
ROUTES CHANGED: NO
AUTHENTICATION CHANGED: NO
AUTHORIZATION CHANGED: NO
UI CHANGED: NO
TESTS MODIFIED: NO
REVIEWERAPPLICATION MODIFIED: NO
