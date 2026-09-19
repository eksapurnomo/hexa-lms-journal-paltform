<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationEvidence extends Model
{
    use HasFactory;

    protected $table = 'verification_evidences';

    protected $guarded = ['id'];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_NEEDS_REVISION = 'needs_revision';

    const CATEGORY_IDENTITY = 'identity';
    const CATEGORY_ACADEMIC_CREDENTIAL = 'academic_credential';
    const CATEGORY_INSTITUTION_AFFILIATION = 'institution_affiliation';
    const CATEGORY_RESEARCHER_IDENTITY = 'researcher_identity';
    const CATEGORY_SUPPORTING_DOCUMENT = 'supporting_document';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JournalMembershipApplication::class, 'journal_membership_application_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}