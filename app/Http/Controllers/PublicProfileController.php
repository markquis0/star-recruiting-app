<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function show(string $token)
    {
        $candidate = Candidate::where('public_profile_token', $token)->first();

        if (!$candidate || !$candidate->public_profile_active) {
            abort(404);
        }

        if ($candidate->public_profile_expires_at && now()->gt($candidate->public_profile_expires_at)) {
            abort(404);
        }

        // Eager load what we need
        $candidate->load([
            'user',
            'forms.assessment',
            'forms.assessment.answers.question',
            'latestAptitudeAssessment',
        ]);

        // Extract aptitude
        $aptitudeAssessment = $candidate->latestAptitudeAssessment;
        $aptitudeProfile = $aptitudeAssessment?->aptitude_profile ?? null;

        // Extract latest behavioral assessment
        $behavioralForm = $candidate->forms
            ->where('form_type', 'behavioral')
            ->where('status', 'submitted')
            ->sortByDesc('created_at')
            ->first();

        $behavioralAssessment = $behavioralForm?->assessment;

        // Project forms
        $projects = $candidate->forms
            ->where('form_type', 'project')
            ->sortByDesc('created_at')
            ->values();

        return view('public.profile', [
            'candidate' => $candidate,
            'aptitudeProfile' => $aptitudeProfile,
            'behavioralAssessment' => $behavioralAssessment,
            'projects' => $projects,
        ]);
    }
}
