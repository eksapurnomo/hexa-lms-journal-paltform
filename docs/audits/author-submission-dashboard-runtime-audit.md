AUTHOR SUBMISSION + DASHBOARD RUNTIME AUDIT
============================================

Submission #14 Visible:
PASS

Submission List:
PASS

Submission Detail:
PASS

Manuscript Version:
PASS

Submission File:
PASS

File Download:
PASS

Historical Version:
PASS

Revision Workflow:
NOT AVAILABLE

Owner Authorization:
PASS

Unauthorized Access:
PASS

IDOR Protection:
PASS

Cross-Journal Isolation:
PASS

Dashboard:
PASS

Conditional Journal UI:
PASS

Author Workspace:
PASS

Journal Management Separation:
PASS

Course/User Separation:
PASS

Compact UX:
PASS

Responsive UX:
PASS

Horizontal Overflow:
PASS

Submission Tests:
PASS

Frontend Build:
PASS

Backend Changes:
NONE

Unexpected Changes:
LIST: The frontend `SubmissionDetail.vue` revision action attempts to POST to `/api/submissions/{submissionId}/submit-revision`, but the backend route is actually defined as `/api/submissions/{submission}/revision`. This causes the revision workflow button to fail (404), rendering the workflow currently NOT AVAILABLE from the UI. No backend changes were made.

Database Reset:
NO

Submission #14 Deleted:
NO

Commit:
NO

FINAL VERDICT:
NEEDS FOLLOW-UP
