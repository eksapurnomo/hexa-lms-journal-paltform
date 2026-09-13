<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled by JournalPolicy in the controller
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:journals,slug',
            'description' => 'nullable|string',
            'issn' => 'nullable|string|max:255',
            'eissn' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:draft,active,archived',

            // Peer Review Policy
            'review_model' => 'nullable|string|in:open,single_blind,double_blind',
            'minimum_reviewers' => 'nullable|integer|min:1',
            'target_reviewers' => 'nullable|integer|gte:minimum_reviewers',
            'maximum_reviewers' => 'nullable|integer|gte:target_reviewers',
            'reviewer_agreement_required' => 'nullable|boolean',
            'conflict_of_interest_required' => 'nullable|boolean',
            'confidentiality_required' => 'nullable|boolean',
        ];
    }
}
