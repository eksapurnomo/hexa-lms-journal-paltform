<?php

namespace App\Services;

use App\Models\AcademicProfile;
use App\Models\JournalMembershipApplication;
use App\Models\User;

class JournalMembershipApplicationService
{
    /**
     * @throws \Exception
     */
    public function createDraft(User $user, array $data): JournalMembershipApplication
    {
        // Prevent duplicate draft/pending applications for same journal + role
        $existing = JournalMembershipApplication::where('user_id', $user->id)
            ->where('journal_id', $data['journal_id'])
            ->where('requested_role', $data['requested_role'])
            ->whereNotIn('status', [JournalMembershipApplication::STATUS_REJECTED, JournalMembershipApplication::STATUS_APPROVED])
            ->first();

        if ($existing) {
            throw new \Exception('You already have an active application for this role in this journal.');
        }

        $this->syncAcademicProfile($user, $data);

        $appData = [
            'user_id' => $user->id,
            'journal_id' => $data['journal_id'],
            'requested_role' => $data['requested_role'],
            'status' => JournalMembershipApplication::STATUS_DRAFT,
        ];

        if (array_key_exists('recruitment_source', $data)) {
            $appData['recruitment_source'] = $data['recruitment_source'];
        }
        if (array_key_exists('declarations', $data)) {
            $appData['declarations'] = $data['declarations'];
        }

        return JournalMembershipApplication::create($appData);
    }

    /**
     * @throws \Exception
     */
    public function updateDraft(User $user, JournalMembershipApplication $application, array $data): JournalMembershipApplication
    {
        if ($application->user_id !== $user->id) {
            throw new \Exception('Unauthorized access to application.');
        }

        if (!in_array($application->status, [JournalMembershipApplication::STATUS_DRAFT, JournalMembershipApplication::STATUS_NEEDS_REVISION])) {
            throw new \Exception('Application cannot be edited in its current state.');
        }

        $this->syncAcademicProfile($user, $data);

        if (array_key_exists('recruitment_source', $data)) {
            $application->recruitment_source = $data['recruitment_source'];
        }
        if (array_key_exists('declarations', $data)) {
            $application->declarations = $data['declarations'];
        }
        
        if ($application->isDirty()) {
            $application->save();
        }

        return $application;
    }

    /**
     * @throws \Exception
     */
    public function submitApplication(User $user, JournalMembershipApplication $application): JournalMembershipApplication
    {
        if ($application->user_id !== $user->id) {
            throw new \Exception('Unauthorized access to application.');
        }

        if (!in_array($application->status, [JournalMembershipApplication::STATUS_DRAFT, JournalMembershipApplication::STATUS_NEEDS_REVISION])) {
            throw new \Exception('Application cannot be submitted in its current state.');
        }

        $application->update([
            'status' => JournalMembershipApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        return $application;
    }

    protected function syncAcademicProfile(User $user, array $data): void
    {
        // Handle 'independent' institution type
        if (($data['institution_type'] ?? '') === 'independent') {
            $data['institution_id'] = null;
            $data['institution'] = null;
            $data['department'] = null;
        }

        $profile = AcademicProfile::firstOrCreate(['user_id' => $user->id]);
        
        $updateData = [];
        $fields = [
            'academic_type', 'highest_degree', 'academic_position', 'institution_type',
            'institution_id', 'institution', 'department', 'country', 'biography',
            'research_interests', 'institutional_email', 'orcid', 'sinta_id', 
            'scopus_author_id', 'google_scholar_url'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field] ?? $profile->{$field};
            }
        }

        if (!empty($updateData)) {
            $profile->update($updateData);
        }
    }
}
