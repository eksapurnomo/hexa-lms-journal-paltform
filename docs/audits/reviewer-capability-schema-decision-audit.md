# Audit 4 — Reviewer Capability Schema Decision Audit

## Objective
This architecture audit evaluates the optimal persistence schema for reviewer-specific capability data, comparing a JSON column approach against a dedicated relational table approach. The goal is to establish the canonical home for capacity, availability, and journal-specific reviewing preferences.

---

## PART 1 — CURRENT DOMAIN INSPECTION

The current implementation of the reviewer/membership ecosystem heavily relies on strongly typed, relational tables.
- `User` has a 1:1 relationship with `AcademicProfile`.
- `User` has a 1:M relationship with `JournalMembership`.
- `JournalMembership` uses an enum-like `role` field (`owner`, `editor`, `reviewer`).
- The legacy `ReviewerApplication` table stores everything (identity, capability, workflow) in one flat structure.
- Assignment logic (`EditorialDeskController`) currently depends on `JournalMembership` + `ReviewerApplication`, querying via Eloquent's `whereHas` relational scopes.
- Existing peer-review tables (`ReviewAssignment`, `ReviewRound`, `PeerReview`) are highly normalized relational tables.

---

## PART 2 — REVIEWER CAPABILITY FIELD AUDIT

| Field | Current Source | Current Runtime Use | AcademicProfile? | Reviewer-Specific? | Proposed Destination |
| ----- | -------------- | ------------------- | ---------------- | ------------------ | -------------------- |
| `available_for_review` | `ReviewerApplication` | Ignored | No | Yes | Reviewer Capability |
| `max_reviews_per_month` | `ReviewerApplication` | Ignored | No | Yes | Reviewer Capability |
| `years_of_experience` | `ReviewerApplication` | Ignored | No | Yes (Peer-Review Exp) | Reviewer Capability |
| `previous_experience` | `ReviewerApplication` | Ignored | No | Yes (Peer-Review Exp) | Reviewer Capability |
| `expertise` / `research_keywords` | `ReviewerApplication` | Ignored | Yes | No | Academic Profile |
| `primary_research_area` | `ReviewerApplication` | Ignored | Yes | No | Academic Profile |
| `agreed_confidentiality` | `ReviewerApplication` | Ignored | No | No (Onboarding) | Application / Membership |
| `agreed_conflict_of_interest` | `ReviewerApplication` | Ignored | No | No (Onboarding) | Application / Membership |
| `agreed_guidelines` | `ReviewerApplication` | Ignored | No | No (Onboarding) | Application / Membership |

### Global academic identity
Fields like `expertise`, `primary_research_area`, `institution`, and `ORCID` belong strictly to `AcademicProfile`. They represent the individual regardless of which journal they review for.

### Journal-specific reviewer capability
Fields like `available_for_review` and `max_reviews_per_month` govern workload and availability for a *specific* journal. They belong to the Reviewer Capability domain.

### Submission-specific eligibility
Concepts like conflict of interest with an author, or existing assignment workload for a specific review round, are dynamic constraints calculated against `ReviewAssignment` and manuscript metadata. They are not stored as static fields.

---

## PART 3 — OPTION A

**Concept**: `journal_memberships.capabilities JSON`

1. **Journal scoping**: Inherited naturally because it lives on the membership record.
2. **Queryability**: Poor. Requires JSON path queries (e.g., `whereJsonContains` or `where('capabilities->available_for_review', true)`), which are slower and less portable across database engines.
3. **Laravel/Eloquent usability**: Acceptable via `$casts = ['capabilities' => 'array']`, but lacks native strongly-typed accessors without custom boilerplate.
4. **Validation**: Must be enforced strictly in application logic; the database provides no schema validation for the JSON structure.
5. **Indexing**: Extremely limited in MySQL. Cannot easily create compound indexes involving availability and role.
6. **Future schema evolution**: Easy to add fields ad-hoc, but high risk of data fragmentation (e.g., missing keys, type mismatches).
7. **Auditability**: Harder to track atomic changes to specific capability fields.
8. **Historical changes**: Requires full JSON object replacement or complex JSON patch operations.
9. **Reviewer workload management**: Checking numeric limits (like `max_reviews`) inside JSON is inefficient at scale.
10. **Availability/capacity logic**: Slower to compute during batch assignment queries.
11. **Testing complexity**: Requires mocking JSON structures and testing for missing keys.
12. **Migration complexity**: Simple schema update, but complex data migration logic.
13. **Compatibility with current architecture**: Low. HexaLMS relies on normalized relational schemas. Additionally, this pollutes `journal_memberships` because roles like `editor` and `owner` would have a `null` or irrelevant `capabilities` field.

**Query Example**:
```php
// Find active reviewers available for review
JournalMembership::where('role', 'reviewer')
    ->where('status', 'active')
    ->where('capabilities->available_for_review', true)
    ->get();
```

---

## PART 4 — OPTION B

**Concept**: Dedicated table `journal_reviewer_capabilities`

**Relationship**: `JournalMembership (1) : (1) JournalReviewerCapability`

1. **Journal scoping**: Excellent. Inherited securely via the parent `JournalMembership` relationship.
2. **Queryability**: Excellent. Standard SQL `WHERE` clauses.
3. **Laravel/Eloquent relationships**: Native and clean (`$membership->reviewerCapability`).
4. **Validation**: Strict database schema types (boolean, integer) enforce data integrity.
5. **Indexing**: Full support for standard, compound, and optimized indexing.
6. **Extensibility**: Very structured. New capability fields require standard migrations, maintaining history and intent.
7. **Auditability**: Atomic updates are naturally auditable.
8. **Historical changes**: Easy to track via standard Eloquent events/observers.
9. **Reviewer workload management**: Native integer comparisons (`WHERE max_reviews_per_month > ?`).
10. **Availability/capacity logic**: Extremely fast and index-friendly.
11. **Testing**: Native Eloquent factories and assertions.
12. **Migration complexity**: Requires a new table and model, standard Laravel procedure.
13. **Compatibility with current architecture**: High. Matches the existing normalized architecture (e.g., separating `User` from `AcademicProfile`).

---

## PART 5 — FUTURE REQUIREMENTS

Option B (`journal_reviewer_capabilities`) seamlessly supports future expansions:
- `maximum_active_reviews` (Integer)
- `monthly_review_capacity` (Integer)
- `preferred_subject_areas` (JSON or Pivot table)
- `review_language` (JSON or Pivot table)
- `review_type_preferences` (JSON)

**Boundary Enforcements**:
- `onboarding_status`, `invitation_status`, and declarations should reside on `JournalMembershipApplication` (or `JournalMembership`), not capabilities.
- `last_reviewed_at` is a computed metric from `ReviewAssignment`, not a static capability field.
- `verified_at` belongs to `JournalMembership` (as a status transition timestamp).

---

## PART 6 — RECOMMENDED DOMAIN MODEL

```text
User
 │
 ├── AcademicProfile (Global academic identity, degrees, expertise)
 │
 └── JournalMembership (Journal-scoped authorization, role, status)
       │
       ├── JournalMembershipApplication (Onboarding workflow, recruitment source, declarations)
       │
       └── JournalReviewerCapability (Journal-specific availability, capacity, preferences)
```

**Domain Principles**:
`Academic Qualification` (Global, immutable per journal)
≠
`Journal Membership` (Authorization boundary)
≠
`Reviewer Capability` (Availability/workload boundary)
≠
`Recruitment Source` (Onboarding context)
≠
`Manuscript Eligibility` (Dynamic conflict/suitability checks)

---

## PART 7 — LEGACY ReviewerApplication MAPPING

| ReviewerApplication Field | Future Domain | Future Field | Notes |
| ------------------------- | ------------- | ------------ | ----- |
| `status` | `JournalMembership` | `status` | Replaces the accepted gate. |
| `available_for_review` | `JournalReviewerCapability` | `is_available` | Boolean setting. |
| `max_reviews_per_month` | `JournalReviewerCapability` | `max_reviews_per_month` | Integer setting. |
| `years_of_experience` | `JournalReviewerCapability` | `years_of_experience` | Peer-review specific. |
| `previous_experience` | `JournalReviewerCapability` | `previous_experience` | Peer-review specific. |
| `agreed_*` | `JournalMembershipApplication` | `declarations` | JSON onboarding data. |
| `affiliation`/`expertise` | `AcademicProfile` | Various | Already canonically stored. |

---

## PART 8 — ELIGIBILITY IMPACT

In `EditorialDeskController::eligibleReviewers()`:

**Current Legacy Dependency**:
```php
->whereHas('reviewerApplications', function ($query) use ($submission) {
    $query->where('journal_id', $submission->journal_id)
          ->where('status', \App\Models\ReviewerApplication::STATUS_ACCEPTED);
})
```

**Future state (with Option B)**:
The `reviewerApplications` gate is entirely removed. The query relies on `journalMemberships` for authorization. If filtering for active availability is desired (which it currently isn't), it becomes a highly optimized query:
```php
->whereHas('journalMemberships', function ($query) use ($submission) {
    $query->where('journal_id', $submission->journal_id)
          ->where('role', 'reviewer')
          ->where('status', 'active')
          ->whereHas('reviewerCapability', function ($cap) {
              $cap->where('is_available', true);
          });
})
```

---

## PART 9 — DECISION

**RECOMMENDATION: Option B**

**Rationale**:
1. **Fits HexaLMS**: The platform uses strict, normalized relational models (`AcademicProfile`, `JournalMembership`). Option B continues this architectural standard.
2. **Role Separation**: `journal_memberships` houses Owners, Editors, and Reviewers. Injecting a JSON column for *reviewer-specific* workload pollutes the table for Owners and Editors. Option B ensures that only active reviewers receive a capability record.
3. **Scalability & Query Performance**: Workload management relies heavily on numeric comparisons (`count < max_reviews_per_month`) and boolean flags (`is_available`). Relational columns provide massive performance benefits over JSON path queries for these operations across multiple journals and thousands of reviewers.
4. **Maintainability**: Strongly typed database schemas drastically reduce bugs and validation complexity in Laravel compared to unstructured JSON blobs.

---

## PART 10 — IMPLEMENTATION ROADMAP

**Phase 1: Reviewer Capability Foundation**
Create `JournalReviewerCapability` model and migration. Define the 1:1 relationship on `JournalMembership`.

**Phase 2: Application / Recruitment Source**
Add `recruitment_source` and `declarations` to `JournalMembershipApplication`.

**Phase 3: Dual-read compatibility**
Update the UI/API to read capabilities from `JournalReviewerCapability` with a fallback to `ReviewerApplication` if missing.

**Phase 4: Data migration**
Run a one-time command to seed `JournalReviewerCapability` records for all existing accepted `ReviewerApplication` records.

**Phase 5: Eligibility migration**
Refactor `EditorialDeskController@eligibleReviewers` to remove the `ReviewerApplication` gate and replace it with canonical authorization and capability checks.

**Phase 6: ReviewerApplication retirement**
Drop the `ReviewerApplication` model, routes, controllers, and database table.

---

AUDIT STATUS: COMPLETE

SCHEMA CHANGED: NO
MIGRATIONS CREATED: NO
APPLICATION CODE CHANGED: NO
DATABASE DATA CHANGED: NO
ROUTES CHANGED: NO
AUTHORIZATION CHANGED: NO
ELIGIBILITY LOGIC CHANGED: NO
LEGACY ReviewerApplication REMOVED: NO
