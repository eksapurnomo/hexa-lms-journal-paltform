<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicProfileController extends Controller
{
    /**
     * Display the authenticated user's academic profile.
     */
    public function show()
    {
        $profile = AcademicProfile::where('user_id', Auth::id())->first();

        if (!$profile) {
            return response()->json([
                'message' => 'Academic profile not found.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Academic profile retrieved successfully.',
            'data' => $profile
        ]);
    }

    /**
     * Create or update the authenticated user's academic profile.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'academic_type' => 'nullable|string|max:255',
            'highest_degree' => 'nullable|string|max:255',
            'academic_position' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'institution_id' => 'nullable|integer|exists:institutions,id',
            'institution_type' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'biography' => 'nullable|string|max:2000',
            'research_interests' => 'nullable|string|max:1000',
            'expertise' => 'nullable|array',
            'expertise.*' => 'string|max:255',
            'orcid' => 'nullable|string|max:255',
            'sinta_id' => 'nullable|string|max:255',
            'scopus_author_id' => 'nullable|string|max:255',
            'google_scholar_url' => 'nullable|url|max:255',
            'institutional_email' => 'nullable|email|max:255',
        ]);

        $profile = AcademicProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json([
            'message' => 'Academic profile updated successfully.',
            'data' => $profile
        ]);
    }
}
