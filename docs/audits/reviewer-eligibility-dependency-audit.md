# Audit 2 — Reviewer Eligibility Dependency Audit

## 1. Executive Summary
This audit confirms that the legacy `ReviewerApplication` model is deeply entrenched in the reviewer assignment logic as a required eligibility filter. The canonical `JournalMembership` alone is currently insufficient for a user to appear in the editorial desk's reviewer selection list. Furthermore, while the legacy application captures capacity (`max_reviews_per_month`) and availability (`available_for_review`), these fields are merely stored as capability data and are completely ignored by the system's assignment logic. There is currently no automated conflict-of-interest check.

## 2. Exact `eligibleReviewers()` implementation
Location: `app/Http/Controllers/Api/EditorialDeskController.php`

```php
public function eligibleReviewers(Submission $submission)
{
    Gate::authorize('editorial-process', [$submission]);

    $reviewers = User::whereHas('journalMemberships', function ($query) use ($submission) {
        $query->where('journal_id', $submission->journal_id)
              ->where('role', 'reviewer')
              ->where('status', 'active');
    })
    ->whereHas('reviewerApplications', function ($query) use ($submission) {
        $query->where('journal_id', $submission->journal_id)
              ->where('status', \App\Models\ReviewerApplication::STATUS_ACCEPTED);
    })
    ->whereHas('academicProfile')
    ->select('id', 'name', 'email')->get();

    return response()->json([
        'message' => 'Eligible reviewers retrieved successfully.',
        'data' => $reviewers
    ]);
}
```

## 3. Dependency Trace
```text
Eligible Reviewer
    ↓
User
    ↓
Active JournalMembership (role = reviewer, journal match)?
    ↓ YES
Accepted ReviewerApplication (journal match)?
    ↓ YES
Has AcademicProfile?
    ↓ YES
Eligible for UI display
```

## 4. Condition-by-Condition Eligibility Matrix

| Condition | Required for eligibility? | Source | Type | Legacy dependency? | Evidence |
| --------- | ------------------------- | ------ | ---- | ------------------ | -------- |
| User exists | Yes | `User` | Authorization | No | `EditorialDeskController.php:307` |
| JournalMembership exists | Yes | `JournalMembership` | Authorization | No | `EditorialDeskController.php:307` |
| Membership active | Yes | `JournalMembership` | Authorization | No | `EditorialDeskController.php:310` |
| Role = reviewer | Yes | `JournalMembership` | Authorization | No | `EditorialDeskController.php:309` |
| Journal ID match | Yes | `JournalMembership` / `ReviewerApplication` | Filter | No / Yes | `EditorialDeskController.php:308, 313` |
| ReviewerApplication accepted | Yes | `ReviewerApplication` | Legacy Gate | Yes | `EditorialDeskController.php:314` |
| AcademicProfile exists | Yes | `AcademicProfile` | Authorization | No | `EditorialDeskController.php:316` |
| `available_for_review` | No | `ReviewerApplication` | Capability | Yes | Never queried |
| `max_reviews_per_month`| No | `ReviewerApplication` | Capability | Yes | Never queried |
| `expertise` / `research_area` | No | `ReviewerApplication` | Capability | Yes | Never queried |
| Existing assignment | No (for UI) / Yes (for action) | `ReviewAssignment` | Capacity | No | `EditorialDeskController.php:365` |

## 5. ReviewerApplication Dependencies
- **`status` ('accepted')**: Yes. Enforced by `eligibleReviewers()` query as a strict INNER JOIN requirement.
- **`available_for_review`**: No. Stored but never checked during selection or assignment.
- **`max_reviews_per_month`**: No. Stored but never checked during selection or assignment.
- **`expertise`**: No. Stored but not used to filter eligible reviewers.

## 6. JournalMembership Dependencies
- `status = active` is strictly required.
- `role = reviewer` is strictly required.
- Membership is strictly scoped to the `journal_id` of the submission being processed.
- The membership is checked indirectly via the `whereHas` Eloquent relationship scope.

## 7. JournalMembershipApplication Dependencies
`JournalMembershipApplication` has **NO DIRECT EFFECT** on reviewer eligibility. A user who successfully passes the UAV.5 application pipeline receives an active `JournalMembership`. However, because they lack an accepted `ReviewerApplication`, the `eligibleReviewers()` query filters them out. 

## 8. Capacity / Assignment Logic
Reviewer assignment logic (`EditorialDeskController@assignReviewer`) checks:
1. **Duplicate Assignment**: Rejects assignment if the reviewer already has an `assigned`, `accepted`, or `in_progress` assignment for the *current review round*.
2. **Round Limits**: Rejects assignment if the round has reached its `maximum_reviewers` limit.
**CRITICAL**: Individual reviewer capacity (e.g., `max_reviews_per_month`) is entirely unenforced.

## 9. Availability Logic
`available_for_review` exists in the legacy form and database, but it is **NEVER** queried or displayed. It currently has no operational effect.

## 10. Expertise / Profile Logic
Expertise, keywords, and research areas (whether from `ReviewerApplication` or `AcademicProfile`) are completely ignored in the `eligibleReviewers()` query. They serve only as informational profile data (if exposed in UI) and are not eligibility filters.

## 11. Conflict-of-Interest Logic
**NO AUTOMATED CONFLICT CHECK FOUND**. The system relies entirely on manual editorial judgment and reviewer self-declarations (`agreed_conflict_of_interest`) to manage conflicts.

## 12. Security Test Evidence
`tests/Feature/PeerReviewSecurityTest.php` proves the legacy dependency. The test manually seeds an active `JournalMembership` AND an accepted `ReviewerApplication` (lines 46-57). If `ReviewerApplication` was not a hard dependency, the test would only need to seed the membership. This test asserts that assignments succeed only when these rigid conditions are met.

## 13. Route Trace
- User visits `/journal/reviewer/apply` (creates `ReviewerApplication`).
- Admin visits `/admin/reviewer-applications` (accepts `ReviewerApplication` -> creates pending membership).
- Admin assigns reviewer at Editorial Desk UI -> Calls `GET /api/editorial/submissions/{id}/eligible-reviewers` -> Hits `EditorialDeskController@eligibleReviewers()`.

## 14. Schema/Data Source Trace
- Eligibility pulls from: `users`, `journal_memberships`, `reviewer_applications`, `academic_profiles`.
- Assignment checks pull from: `review_assignments`, `review_rounds`.

## 15. Current Eligibility Boolean Model
```text
CURRENT_ELIGIBILITY = A AND B AND C AND D

A = Active JournalMembership (role=reviewer, matched journal)
B = Accepted ReviewerApplication (matched journal)
C = Has AcademicProfile
D = Not actively assigned to current review round (Filter applied at mutation, not query)
```

## 16. Critical Question Results

**Question**: If a user has a complete AcademicProfile, an approved JournalMembershipApplication, and an active JournalMembership as reviewer, but NO ReviewerApplication, will they appear in `eligibleReviewers()`?

**Answer**: NO.
**Evidence**: `EditorialDeskController.php:312-315` applies a strict `whereHas('reviewerApplications')` clause requiring an accepted application status.

## 17. Legacy Dependency Classification
**CASE E: A combination of the above.**
`ReviewerApplication` acts as a required eligibility filter (the query mandates it) and serves as the sole source of reviewer capability data (capacity/availability), even though the system currently ignores that capability data at runtime.

## 18. Future Canonical Source Mapping
- `ReviewerApplication.accepted` -> **`JournalMembership` / Canonical Authorization**
- `available_for_review` -> **Reviewer Capability Domain** (Requires domain decision on where capability lives, e.g., JSON on membership or a dedicated `ReviewerCapability` table).
- `max_reviews_per_month` -> **Reviewer Capability Domain** (Requires domain decision).
- Declarations (`agreed_confidentiality`, etc.) -> **JournalMembershipApplication** (As workflow metadata during onboarding).

## 19. Risks / Open Decisions
Decoupling `ReviewerApplication` from `EditorialDeskController` will instantly break legacy tests like `PeerReviewSecurityTest` that explicitly assert against the presence of this model. A clear domain decision must be made on where reviewer-specific capacity and availability settings will permanently reside.

## 20. Recommended Next Audit
**AUDIT 3 — REVIEWER CAPABILITY DESTINATION AUDIT**.
Purpose: Determine the proper canonical home for reviewer-specific data such as `available_for_review`, `max_reviews_per_month`, and onboarding declarations, paving the way to finally retire the legacy model.
