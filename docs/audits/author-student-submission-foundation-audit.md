# Audit 5 — Author & Student Submission Foundation Audit

## Objective
This strict read-only audit evaluates the current HexaLMS Journal Platform architecture and UI/UX to determine whether students (S1, S2, S3) and academics possess the necessary foundation to become Journal Authors and submit manuscripts. The audit strictly evaluates the current state without introducing new policies.

---

## PART 1 — USER IDENTITY FOUNDATION
**USER IDENTITY STATUS: COMPLETE**
The system uses a single, canonical `User` model for all identities. Students (via `Enrollment`), Instructors, Journal Members, and Authors all share the same identity. Submissions reference this identity via `submissions.created_by` (the submission owner) and `submission_authors.user_id` (the registered author). There are no duplicate identity models.

---

## PART 2 — ACADEMIC PROFILE FOUNDATION
The `AcademicProfile` correctly houses academic credentials without acting as an authorization gate. S1/S2/S3 levels are technically supported by `highest_degree`, although there is no hard restriction preventing a user from submitting based on their degree.

| Academic Data | Exists? | Actual Field/Model | Used by Submission? | UI Exists? | Notes |
| ------------- | ------- | ------------------ | ------------------- | ---------- | ----- |
| Degree (S1/S2/S3) | Yes | `highest_degree` | No | Yes | Informational only. |
| Institution | Yes | `institution` / `institution_id` | No | Yes | - |
| Department | Yes | `department` | No | Yes | - |
| Study Program | No | N/A | No | No | Typically folded into `department`. |
| Academic Position | Yes | `academic_position` | No | Yes | - |
| Research Interests| Yes | `research_interests` | No | Yes | - |
| Expertise | Yes | `expertise` | No | Yes | Also uses `AcademicProfileExpertise`. |
| ORCID | Yes | `orcid` | No | Yes | - |
| Google Scholar | Yes | `google_scholar_url` | No | Yes | - |
| Scopus / SINTA | Yes | `scopus_author_id` / `sinta_id`| No | Yes | - |

**Critical Observation**: Academic level is NOT used as an eligibility restriction, which correctly aligns with the domain requirement that anyone can submit.

---

## PART 3 — JOURNAL MEMBERSHIP AND AUTHOR RELATIONSHIP
Submission creation is completely decoupled from Journal Membership.

| User State | Can Access Journal? | Can Create Submission? | Can Submit? | Evidence |
| ---------- | ------------------- | ---------------------- | ----------- | -------- |
| Normal User | Yes | Yes | Yes | `SubmissionPolicy::create` returns `true`. |
| Student S1/S2/S3 | Yes | Yes | Yes | `SubmissionPolicy::create` |
| Journal Member | Yes | Yes | Yes | `SubmissionPolicy::create` |
| Reviewer/Editor | Yes | Yes | Yes | `SubmissionPolicy::create` |

---

## PART 4 — AUTHOR DOMAIN AUDIT
The Author domain is correctly implemented via a dedicated `SubmissionAuthor` model (`submission_authors` table). It maintains a many-to-one relationship with `Submission`. Authorship is explicitly decoupled from the LMS User identity, though a nullable `user_id` foreign key can link a `SubmissionAuthor` to a registered `User`.

---

## PART 5 — CO-AUTHOR SUPPORT
**CO-AUTHOR SUPPORT: COMPLETE**
One submission can have multiple authors. The `SubmissionForm.vue` UI allows dynamic addition/removal of authors. `SubmissionRepository::syncAuthors` enforces deterministic ordering via a `sequence` field. Authors do not need to be registered users (only strings for name, email, and affiliation are required).

---

## PART 6 — CORRESPONDING AUTHOR
**CORRESPONDING AUTHOR SUPPORT: PARTIAL**
The system explicitly stores `is_corresponding` (boolean) on the `submission_authors` table. The UI enforces that at least one author is the corresponding author. 
**However**, the concept of `Submission Owner` (`submissions.created_by`) is distinct from `Corresponding Author`. Only the `Submission Owner` has system authorization to view, edit, or submit the manuscript (enforced by `SubmissionPolicy::view` and `SubmissionRepository::getScopedQuery`). If the corresponding author is not the user who created the submission, the corresponding author cannot access the submission in their dashboard.

---

## PART 7 — MANUSCRIPT DOMAIN
The manuscript domain is highly structured and supports versioning:
```text
User (Submission Owner)
 ↓
Submission
 │
 ├── SubmissionAuthor (Authorship Metadata)
 │
 └── SubmissionRevision (Versioning)
       │
       ├── SubmissionFile (Uploaded Manuscript)
       │
       └── ReviewRound (Editorial Workflow)
```

---

## PART 8 — SUBMISSION WORKFLOW
The actual lifecycle is fully implemented via API and Vue components:

1. **Create Submission**: IMPLEMENTED (`SubmissionController@store`, `SubmissionForm.vue`)
2. **Enter Metadata**: IMPLEMENTED
3. **Add Authors**: IMPLEMENTED
4. **Upload Manuscript**: IMPLEMENTED (`SubmissionController@uploadFile`, `SubmissionDetail.vue`)
5. **Submit**: IMPLEMENTED (`SubmissionController@submit`)
6. **Editorial Screening**: IMPLEMENTED
7. **Review**: IMPLEMENTED
8. **Decision**: IMPLEMENTED
9. **Revision**: IMPLEMENTED (`SubmissionController@submitRevision`)

---

## PART 9 — AUTHOR UI/UX AUDIT
The UI provides a comprehensive Author dashboard in the SPA (`resources/js/pages/author/`):
1. **How to start a submission**: `/author/submissions/create`
2. **How to enter metadata & authors**: `SubmissionForm.vue`
3. **How to select corresponding author**: Handled via radio button in `SubmissionForm.vue`.
4. **How to upload manuscript**: `SubmissionDetail.vue` file input.
5. **How to submit**: "Submit Manuscript" button in `SubmissionDetail.vue`.
6. **Submission status / Revision requests**: Displayed dynamically in `SubmissionDetail.vue`.

---

## PART 10 — BROWSER / UI FLOW VERIFICATION
| UI Area | Route / Vue Component | Exists | Accessible | Functional | UX Status |
| ------- | --------------------- | ------ | ---------- | ---------- | --------- |
| Author Dashboard | `/author/submissions` | Yes | Yes | Yes | `SubmissionList.vue` |
| Create Submission | `/author/submissions/create` | Yes | Yes | Yes | `SubmissionForm.vue` |
| Author Management | Component in Form | Yes | Yes | Yes | Dynamic array input |
| Manuscript Upload | Component in Detail | Yes | Yes | Yes | `SubmissionDetail.vue` |
| Submission Detail | `/author/submissions/:id` | Yes | Yes | Yes | `SubmissionDetail.vue` |
| Revision | Action in Detail | Yes | Yes | Yes | Calls `/submit-revision` API |

---

## PART 11 — S1 / S2 / S3 REALISTIC AUTHOR SCENARIOS
- **Scenario A (S1 Student Submits)**: Supported. They create the submission and own it.
- **Scenario B (S2 Student Corresponding, Co-author Lecturer)**: Supported. They create it, mark themselves as corresponding, and add the Lecturer's details.
- **Scenario C (S3 Student, Multiple Authors)**: Supported.
- **Scenario D (S1 Student creates, Lecturer is corresponding)**: Supported in data, but **practically broken for the Lecturer**. Because the student created the submission, the Lecturer (even if marked as `is_corresponding` and linked via `user_id`) cannot view or access the submission in their dashboard due to the `created_by` access restriction.

---

## PART 12 — AUTHORIZATION AND SECURITY
- **Creation**: Any registered user (`SubmissionPolicy::create`).
- **Editing/Viewing**: Restricted strictly to `submissions.created_by` (or Editors/Owners of the journal).
- **Security Finding**: `SubmissionAuthor` records containing a valid `user_id` do NOT grant that user access to the submission. This prevents co-authors from viewing or collaborating on the manuscript in the system.

---

## PART 13 — LEGACY / DUPLICATION AUDIT
There is no duplication of identity for Authors. The system correctly relies on the canonical `User` and `AcademicProfile` tables. `SubmissionAuthor` acts as a pivot metadata layer, which is the correct pattern.

---

## PART 14 — CURRENT ARCHITECTURE MAP
```text
User
 │
 ├── AcademicProfile
 │
 ├── JournalMembership (Orthogonal to authorship)
 │
 └── Submission (created_by)
       │
       ├── SubmissionAuthor (is_corresponding, user_id nullable)
       │
       └── SubmissionRevision
             │
             ├── SubmissionFile
             └── ReviewRound
```

---

## PART 15 — DOMAIN BOUNDARY ANALYSIS
- **Academic Identity**: Owned by `AcademicProfile`. Correctly separated.
- **Journal Membership**: Owned by `JournalMembership`. Correctly separated (not required for authorship).
- **Author Role**: Owned by `SubmissionAuthor`. Correctly separated (manuscript-specific).
- **Submission Ownership**: Owned by `submissions.created_by`. Currently acting as the sole authorization gate for authors, which causes friction with the "Corresponding Author" concept.
- **Corresponding Author**: Owned by `SubmissionAuthor.is_corresponding`. Metadata only, no system privileges.

---

## PART 16 — GAPS AND READINESS
| Domain | Status | Severity | Evidence |
| ------ | ------ | -------- | -------- |
| Canonical User | COMPLETE | - | Uses global `users` |
| AcademicProfile | COMPLETE | - | Dedicated table and UI |
| S1/S2/S3 | COMPLETE | - | Degree fields exist, not blocking |
| Journal Membership | COMPLETE | - | Does not block submissions |
| Author | COMPLETE | - | `SubmissionAuthor` model |
| Co-Author | COMPLETE | - | Supports array of authors |
| Corresponding Author | PARTIAL | High | No system privileges attached |
| Submission | COMPLETE | - | Full lifecycle |
| Manuscript | COMPLETE | - | Versioned via `SubmissionRevision` |
| Author Dashboard | COMPLETE | - | Vue components active |
| Authorization | PARTIAL | High | `created_by` prevents co-author access |

---

## PART 17 — CRITICAL QUESTION
**Question**: Does HexaLMS currently have a sufficient foundation for an S1/S2/S3 student to become a Journal Author and submit a manuscript through a proper journal workflow?

**Answer: PARTIALLY**
**Why**: The data foundation, identity architecture, UI, and submission workflow are completely in place. A student can physically create a submission, upload files, and successfully push it to the editorial desk. However, because system authorization relies entirely on the `created_by` field (the "Submission Owner") rather than empowering the designated "Corresponding Author" or linked "Co-authors", realistic academic workflows (e.g., a student creating the draft but the Lecturer acting as the corresponding author and handling revisions) will result in the Lecturer being locked out of the submission.

---

## PART 18 — NEXT PHASE RECOMMENDATION
**Author foundation is sufficient; continue Reviewer Capability**

*(Note: The author authorization gap regarding co-authors/corresponding authors is a workflow enhancement, but the architectural foundation for identity and persistence is fully solid. Proceeding to finalize the Reviewer Capability destination is the logical next architectural step before addressing specific author-level policy tweaks.)*

---

AUDIT STATUS: COMPLETE

SCHEMA CHANGED: NO
MIGRATIONS CREATED: NO
APPLICATION CODE CHANGED: NO
DATABASE DATA CHANGED: NO
ROUTES CHANGED: NO
AUTHORIZATION CHANGED: NO
UI/UX CHANGED: NO
SUBMISSION WORKFLOW CHANGED: NO
REVIEWER WORKFLOW CHANGED: NO
LEGACY COMPONENTS REMOVED: NO
