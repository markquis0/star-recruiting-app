<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\ShortLink;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function show(string $identifier)
    {
        $candidate = null;
        $token = null;

        // Decide if this looks like a short code or a full token.
        // Short code: 6–8 chars alphanumeric
        if (strlen($identifier) <= 8 && preg_match('/^[A-Za-z0-9]+$/', $identifier)) {
            $shortLink = ShortLink::where('short_code', $identifier)->first();

            if (!$shortLink) {
                abort(404);
            }

            $candidate = $shortLink->candidate;
            $token = $shortLink->full_token;
        } else {
            // Fall back to the existing long-token behavior
            $token = $identifier;
            $candidate = Candidate::where('public_profile_token', $token)->first();
        }

        if (!$candidate || !$candidate->public_profile_active) {
            abort(404);
        }

        if ($candidate->public_profile_expires_at && now()->gt($candidate->public_profile_expires_at)) {
            abort(404);
        }

        // Load related data (same as you had before)
        $candidate->load([
            'user',
            'forms.assessment',
            'forms.assessment.answers.question',
            'latestAptitudeAssessment',
        ]);

        $aptitudeAssessment = $candidate->latestAptitudeAssessment;
        $aptitudeProfile = $aptitudeAssessment?->aptitude_profile ?? null;

        $behavioralForm = $candidate->forms
            ->where('form_type', 'behavioral')
            ->where('status', 'submitted')
            ->sortByDesc('created_at')
            ->first();

        $behavioralAssessment = $behavioralForm?->assessment;

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
