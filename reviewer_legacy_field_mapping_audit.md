# Reviewer Legacy Field Mapping Audit

## 1. Executive Summary
The legacy `ReviewerApplication` model represents an older, monolithic onboarding flow that mixes academic identity, reviewer capability, and journal membership requests into a single database table. With the introduction of the UAV.5 `JournalMembershipApplication` and `AcademicProfile` architecture, much of this data is now structurally duplicated or obsolete. Critically, the legacy flow is still functionally intertwined with the peer-review assignment logic (`EditorialDeskController`), meaning it cannot be safely deleted yet without breaking reviewer assignments.

## 2. Actual ReviewerApplication Schema
The `reviewer_applications` table (from migration `2026_09_11_000000_create_reviewer_applications_table.php`) contains the following structure:
- **Foreign Keys**: `user_id`, `journal_id`, `reviewed_by`
- **Identity/Profile**: `affiliation`, `department`, `academic_position`, `orcid`, `academic_url`, `primary_research_area`, `research_keywords`, `expertise`
- **Capability**: `years_of_experience`, `previous_experience`, `available_for_review`, `max_reviews_per_month`
- **Agreements**: `agreed_confidentiality`, `agreed_conflict_of_interest`, `agreed_guidelines`
- **System**: `status` (default: 'pending'), `denial_reason`, `reviewed_at`, `created_at`, `updated_at`
- **Constraints**: Unique composite index on `['user_id', 'journal_id', 'status']`.

## 3. Complete Field Inventory
| Field | DB Type | Nullable | Default | Form Input | Validation | Controller Usage | Model Usage | Test Coverage |
| ----- | ------- | -------- | ------- | ---------- | ---------- | ---------------- | ----------- | ------------- |
| `id` | bigint | NO | - | No | - | - | PK | Yes |
| `user_id` | bigint | NO | - | No | Auth | Store/Filter | Rel | Yes |
| `journal_id` | bigint | NO | - | Yes | exists | Store | Rel | Yes |
| `status` | string | NO | 'pending' | No | - | Accept/Deny | Const | Yes |
| `affiliation` | string | NO | - | Yes | string | Store | - | Yes |
| `department` | string | NO | - | Yes | string | Store | - | Yes |
| `academic_position` | string | NO | - | Yes | string | Store | - | Yes |
| `orcid` | string | YES | - | Yes | string | Store | - | Yes |
| `academic_url` | string | YES | - | Yes | url | Store | - | Yes |
| `primary_research_area` | string | NO | - | Yes | string | Store | - | Yes |
| `research_keywords` | text | YES | - | Yes | string | Store | - | Yes |
| `expertise` | text | YES | - | Yes | string | Store | - | Yes |
| `years_of_experience` | smallint | NO | 0 | Yes | integer | Store | - | Yes |
| `previous_experience` | text | YES | - | Yes | string | Store | - | Yes |
| `available_for_review` | boolean | NO | true | Yes | boolean | Store | Cast | Yes |
| `max_reviews_per_month` | smallint | NO | 1 | Yes | integer | Store | Cast | Yes |
| `agreed_confidentiality` | boolean | NO | false | Yes | accepted | Store | Cast | Yes |
| `agreed_conflict_of_interest`| boolean | NO | false | Yes | accepted | Store | Cast | Yes |
| `agreed_guidelines` | boolean | NO | false | Yes | accepted | Store | Cast | Yes |
| `denial_reason` | text | YES | - | No | string | Deny | - | Yes |
| `reviewed_at` | timestamp| YES | - | No | - | Accept/Deny | Cast | Yes |
| `reviewed_by` | bigint | YES | - | No | Auth | Accept/Deny | Rel | Yes |

## 4. Canonical Field Mapping
| Legacy Field | Canonical Destination | Exact Existing Field/Model | Reason | Data Loss Risk | Security Impact | Migration Needed? |
| ------------ | --------------------- | -------------------------- | ------ | -------------- | --------------- | ----------------- |
| `affiliation` | `AcademicProfile` | `institution_id` / `institution` | Core identity data. | Low | None | Yes |
| `department` | `AcademicProfile` | `department` | Core identity data. | Low | None | Yes |
| `academic_position`| `AcademicProfile` | `academic_position` | Core identity data. | Low | None | Yes |
| `orcid` | `AcademicProfile` | `orcid` | Core identity data. | Low | None | Yes |
| `academic_url` | `AcademicProfile` | `google_scholar_url` | Core identity data. | Low | None | Yes |
| `primary_research_area`| `AcademicProfile` | `research_interests` | Core identity data. | Low | None | Yes |
| `research_keywords` | `AcademicProfile` | `expertise` / `AcademicProfileExpertise`| Core identity data. | Low | None | Yes |
| `expertise` | `AcademicProfile` | `expertise` / `AcademicProfileExpertise`| Core identity data. | Low | None | Yes |
| `years_of_experience`| `Reviewer Domain/Capability`| *DESTINATION MISSING — DO NOT IMPLEMENT* | Specific to reviewer capacity. | Med | None | Yes |
| `previous_experience`| `Reviewer Domain/Capability`| *DESTINATION MISSING — DO NOT IMPLEMENT* | Specific to reviewer capacity. | Med | None | Yes |
| `available_for_review`| `Reviewer Domain/Capability`| *DESTINATION MISSING — DO NOT IMPLEMENT* | Specific to reviewer capacity. | High | Affects assign | Yes |
| `max_reviews_per_month`| `Reviewer Domain/Capability`| *DESTINATION MISSING — DO NOT IMPLEMENT* | Specific to reviewer capacity. | High | Affects assign | Yes |
| `agreed_confidentiality`| `JournalMembershipApplication`| *JSON Metadata or New Field needed* | App-specific declaration. | Med | None | Yes |
| `agreed_conflict_of_interest`| `JournalMembershipApplication`| *JSON Metadata or New Field needed* | App-specific declaration. | Med | None | Yes |
| `agreed_guidelines` | `JournalMembershipApplication`| *JSON Metadata or New Field needed* | App-specific declaration. | Med | None | Yes |
| `denial_reason` | `System/Workflow Metadata`| `JournalMembershipApplication.reviewer_note`| System metadata. | None | None | No |
| `reviewed_at` | `System/Workflow Metadata`| `JournalMembershipApplication.reviewed_at`| System metadata. | None | None | No |
| `reviewed_by` | `System/Workflow Metadata`| `JournalMembershipApplication.reviewed_by`| System metadata. | None | None | No |

## 5. Reviewer-Specific Data Analysis
The legacy fields `available_for_review`, `max_reviews_per_month`, `years_of_experience`, and `previous_experience` represent **Reviewer Capability** data. Currently, the system lacks a dedicated `ReviewerProfile` or capability table attached to `JournalMembership`. The system *does* have a `PeerReviewPolicy` table defining journal-level rules, but the applicant's response/capacity currently has no canonical home outside the legacy application.

## 6. Security / Authorization Findings
**CRITICAL FINDING**: The legacy `ReviewerApplication` is deeply embedded in the authorization logic for peer-review assignment. 
In `EditorialDeskController@eligibleReviewers`, a user CANNOT be assigned as a reviewer unless they meet **BOTH** conditions:
1. `User has active JournalMembership (role=reviewer)`
2. `User has accepted ReviewerApplication`

If a user applies through the canonical `JournalMembershipApplication` (UAV.5) and is approved, they get an active membership but they WILL NOT appear in the eligible reviewer list because they lack a `ReviewerApplication`. Thus, `ReviewerApplication` currently acts as a mandatory authorization gate.

## 7. Workflow Comparison
| Concern | ReviewerApplication (Legacy) | JournalMembershipApplication (Canonical) |
| ------- | ---------------------------- | ---------------------------------------- |
| Purpose | Reviewer onboarding & profile collection | Unified role verification |
| Role support | Reviewer ONLY | Owner, Editor, Reviewer |
| Status lifecycle | pending -> accepted/denied | draft -> submitted -> under_review -> approved |
| Profile data | Hardcoded strings | Relies on canonical `AcademicProfile` |
| Approval mechanism| `WebAdmin\ReviewerApplicationController@accept` | `MembershipVerificationController@updateStatus` |
| Membership creation| Creates `pending` JournalMembership | Creates/Updates `active` JournalMembership |

## 8. Data Duplication Findings
The legacy model structurally duplicates the following data now managed canonically in `AcademicProfile`:
- `orcid`, `department`, `affiliation`, `academic_position`, `academic_url`, `primary_research_area`, `research_keywords`, `expertise`.

## 9. Route / UX Overlap
The UI exposes competing paths:
1. `/journal/reviewer/apply` (Legacy, 20-field form, required for assignments).
2. `/journal/membership-applications/create` (Canonical UAV.5, 3-field form, dead-end for reviewers due to `EditorialDeskController` gate).
They are competing, confusing, and currently mathematically impossible to successfully navigate as a new user wanting to review (unless they fill out both, but the UI does not link them).

## 10. Existing Data Counts
- `reviewer_applications`: 0
- `journal_membership_applications`: 1
- `journal_memberships`: (Variable, seeded in tests/factories)

## 11. Final Legacy Classification
**MIXED — LEGACY APPLICATION + STILL-REQUIRED BUSINESS DATA**.
The application flow is legacy and superseded by UAV.5, but the resulting `ReviewerApplication` record is mathematically required by the `EditorialDeskController` to assign reviewers.

## 12. Recommended Future Sequence
1. **Phase A**: Update `EditorialDeskController@eligibleReviewers` to remove the hard dependency on `ReviewerApplication`, relying instead purely on active `JournalMembership` (role=reviewer).
2. **Phase B**: Define the missing `Reviewer Domain/Capability` structure (e.g., a JSON column on `JournalMembership` or a new `ReviewerCapability` table) to house `max_reviews_per_month` and availability.
3. **Phase C**: Extend `JournalMembershipApplication` to capture the required declarations (`agreed_confidentiality`, etc.) dynamically based on `PeerReviewPolicy`.
4. **Phase D**: Redirect `/journal/reviewer/apply` to `/journal/membership-applications/create`.
5. **Phase E**: Safely drop `ReviewerApplication` model, routes, and migrations.

## 13. Risks / Open Decisions
- Removing the `ReviewerApplication` requirement in `EditorialDeskController` will instantly break existing test assertions in `PeerReviewSecurityTest` that rely on seeding it.
- Moving capability fields requires a firm architectural decision on where reviewer capacity is stored (e.g., Pivot table vs Dedicated Model).

## 14. Files Inspected
- `database/migrations/*create_reviewer_applications_table.php`
- `database/migrations/*create_academic_profiles_table.php`
- `database/migrations/*create_peer_review_policies_table.php`
- `app/Http/Controllers/Api/EditorialDeskController.php`
- `app/Http/Controllers/WebAdmin/ReviewerApplicationController.php`
- `tests/Feature/PeerReviewSecurityTest.php`

## 15. APPLICATION CHANGES: NONE

---
AUDIT STATUS: COMPLETE
APPLICATION CODE CHANGED: NO
DATABASE CHANGED: NO
MIGRATIONS CREATED: NO
DATA MODIFIED: NO
ROUTES MODIFIED: NO
AUTHORIZATION MODIFIED: NO
