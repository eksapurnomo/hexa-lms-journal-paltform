# Single User Identity & Cross-Domain Capability Architecture

## 1. Single User Identity
The canonical identity model for the entire LMS and Journal ecosystem is exactly **ONE User identity**. A `User` record represents a single human being. All other roles—whether they are students, instructors, authors, reviewers, or journal editors—are modeled as capabilities, associations, or profiles attached to this single identity, not as separate user models.

## 2. AcademicProfile
The `AcademicProfile` is a 1:1 extension of the `User` (`User hasOne AcademicProfile`). It is strictly bound to the `User`, not to any specific `Journal`. It serves as the canonical repository for researcher identifiers (ORCID, Scopus, SINTA, Google Scholar), academic rank, and institutional affiliation. Because it belongs to the User, it is inherently reusable across all future capabilities (Journal member, Reviewer, Instructor, etc.) without duplication.

## 3. Journal Membership
`JournalMembership` represents a relationship/capability between a `User` and a `Journal` (`User hasMany JournalMemberships`). A single user can hold memberships in multiple journals concurrently, and can hold different roles (Member, Reviewer, Editor) within those journals. This is an authorization and grouping mechanism, NOT an identity mechanism.

## 4. Course Membership
LMS course participation is modeled via enrollments (student capabilities) and assignments (instructor capabilities). Specifically, a `User` becomes a student via `Enrollment` records, and acts as an instructor via an `Instructor` capability profile. These do not fracture the underlying `User` identity.

## 5. Reviewer/Editor Capabilities
Reviewer and Editor capabilities are governed by `JournalMembership` (and specifically applied via `JournalMembershipApplication`). They are contextual to a specific journal. 

## 6. Student/Instructor Capabilities
A `User` functions as a student by default via LMS enrollments. Instructor capabilities are managed via an explicit `Instructor` profile model associated with the `User`. 

## 7. Capability Composition
The system natively supports complex capability composition. A single `User` can simultaneously be a Student in Course A, an Instructor in Course B, an Editor for Journal X, and a Reviewer for Journal Y. Authorization logic MUST NOT treat these as mutually exclusive roles unless a strict domain rule applies to a specific action.

## 8. Instructor Authorization Principle
**Journal Membership ≠ Instructor Authorization.** 
Being a member, editor, or reviewer of a journal does not automatically grant Instructor privileges in the LMS. Instructor capabilities require explicit authorization (via the `Instructor` model / admin assignment). Future implementations must respect this boundary.

## 9. Registration Principles
All registration flows (LMS or Journal) MUST resolve to the canonical `User` creation process (e.g., `UserRepository::storeByStudentRequest()`). Creating a dedicated "Journal Registration" flow must only orchestrate the creation of a standard `User` (and an associated `AcademicProfile`), followed by a `JournalMembershipApplication`. It must never create a separate `JournalUser` or similar fragmented identity.

## 10. Authentication Boundary
The current ecosystem operates across a fractured authentication boundary:
- **LMS Frontend (Vue SPA)** relies on stateless **JWT** via API routes.
- **Journal/Admin Backend (Blade)** relies on stateful **Web Sessions** via `web` routes.
Future cross-domain workflows (such as a researcher registering to apply for a journal) must explicitly handle this boundary, ensuring that the necessary web session is established so that the User can seamlessly access Blade-based journal applications.

## 11. Anti-Duplication Rules
Future implementations are strictly forbidden from introducing domain-specific user models such as:
- `JournalUser`
- `InstructorUser`
- `ReviewerUser`
- `EditorUser`
- `StudentUser`

Furthermore, profile information must not be duplicated into isolated tables like `JournalMemberProfile` or `ReviewerProfile` when the fields (biography, institution, researcher IDs) logically belong to the canonical `AcademicProfile`.

## 12. Future Extensibility
The architecture is designed to scale horizontally across domains. New modules (e.g., conferences, grants, or alumni networks) should follow the same pattern: leverage the single `User` identity and attach domain-specific capabilities (Pivot tables or capability models) to it.
