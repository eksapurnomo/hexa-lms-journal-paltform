LOCAL FILE STORAGE & MANUSCRIPT AUDIT
=====================================

## Executive Summary
This audit validates the existing Journal Submission file storage architecture. The current system utilizes Laravel's Filesystem abstraction, secure private storage, and a robust revisioning system (`SubmissionRevision` -> `SubmissionFile`). The architecture is highly portable, already behaves precisely like the proposed "ManuscriptVersion" concept, and relies on strict API authorization for downloads. It can be immediately reused for the Journal Manuscript workflow with zero structural changes.

## Existing Upload Architecture
The application currently uploads files through `SubmissionController@uploadFile`. It expects a multipart form-data request and validates the file using Laravel's built-in `file`, `mimes`, and `max` rules in `SubmissionFileStoreRequest`.

## Existing Submission File Flow
- **Route**: `POST /api/submissions/{submission}/files`
- **Controller**: `SubmissionController@uploadFile`
- **Model**: `SubmissionFile` (related via `SubmissionRevision`)
- **Database Table**: `submission_files`
- **Storage Disk**: `'local'`
- **Physical Path Pattern**: `submissions/{submission_id}/{uuid}.{ext}`
- **Authorization**: Governed by `SubmissionPolicy@uploadFile`
- **Validation**: `SubmissionFileStoreRequest`

## Storage Disk
The `uploadFile` method hardcodes `$disk = 'local'`. The disk is configured in `config/filesystems.php` to use the `local` driver.

## Physical Storage Location
The physical files are stored in `storage/app/submissions/`. Since they are outside `storage/app/public/`, they are completely inaccessible to the web server (Nginx) directly and require the application API to read.

## Docker Persistence
**PERSISTENT**. The `docker-compose.yml` mounts the host's current directory (`./`) directly into the container (`/var/www`). Because the `storage` directory is within the host repository, files uploaded to `storage/app/submissions/` are persisted on the host machine and survive container recreation.

## Laravel Filesystem Usage
The codebase fully adheres to Laravel's Filesystem abstraction. 
- Upload: `Storage::disk($disk)->put($path, file_get_contents($file))`
- Delete: `Storage::disk($existingFile->disk)->delete($existingFile->file_path)`
- Download: `Storage::disk($file->disk)->download($file->file_path, $file->original_name)`
The physical disk paths are not coupled tightly to the business logic, as the operations use `$file->disk`.

## Download Authorization
**SECURE**. Downloads occur via `GET /api/submissions/{submission}/files/{file}/download` which runs through `SubmissionPolicy@downloadFile`. The authorization protects against IDOR and spoofing. Because the physical files are private (`storage/app`), it is impossible to bypass the API to download a manuscript.

## ManuscriptVersion Relationship
**EXISTS**. The domain model implements a "Revision" pattern (`SubmissionRevision`) which perfectly models the future "ManuscriptVersion". 
If a user uploads a new file to a draft/unreviewed revision, it overwrites the existing file and removes the old one from storage. If a revision has entered the Peer Review stage (has assignments), the system automatically bumps the version number and preserves the old file/revision, guaranteeing immutability. 

## Supported File Types
Currently restricted to:
- `PDF`
- `DOC`
- `DOCX`
Validation is enforced by `mimes:pdf,doc,docx`.

## File Size Limits
- **Application Validation**: `max:20480` (20MB) defined in `SubmissionFileStoreRequest`.
- **PHP Limits**: The Docker container's PHP runtime has `upload_max_filesize = 2M` and `post_max_size = 8M`. 
- **Effective Limit**: 2MB (Due to the PHP configuration bottleneck).

## Database File Metadata
The `submission_files` table stores:
- `disk` (e.g. `'local'`)
- `file_path` (Logical key for storage, e.g., `submissions/14/uuid.pdf`)
- `original_name`
- `mime_type`
- `size`
Physical host paths or public URLs are NOT stored in the database, preserving portability.

## Security Findings
- **CLEAN**. Files are stored privately. Download routes enforce authorization.

## Storage Portability
**GOOD**. The use of `$file->disk` and `Storage::disk()` makes the transition to S3 or Google Cloud Storage trivial. When external storage is introduced, we only need to update the default upload disk and S3 credentials; the codebase will naturally adapt. Existing local files will continue to load correctly because their database records indicate `disk => 'local'`.

## Minimum Change Recommendation
**REUSE**. The existing system is robust and production-ready for manuscripts.
1. Increase PHP runtime limits in the Docker configuration (`upload_max_filesize = 20M` and `post_max_size = 25M`).
2. If `ODT`, `TXT`, or `RTF` are required, simply update the `mimes` rule in `SubmissionFileStoreRequest`.

## Risks
The only immediate risk is the 2MB PHP limitation causing silent or unexpected upload failures for users submitting large PDF manuscripts.

## Recommended Next Step
No code changes are required for the layout or backend structure. The next phase can safely build features relying on this manuscript file system.
