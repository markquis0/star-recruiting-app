<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\SavedCandidate;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class RecruiterController extends Controller
{
    public function home(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isRecruiter()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $recruiter = $user->recruiter;
        $savedCandidates = $recruiter->savedCandidates()
            ->with(['candidate.user', 'candidate.forms.assessment'])
            ->get();

        return response()->json(['saved_candidates' => $savedCandidates]);
    }

    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isRecruiter()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Candidate::with(['user', 'forms.assessment', 'latestAptitudeAssessment']);

        // Filter by role title (if provided)
        if ($request->filled('role')) {
            $query->where('role_title', 'like', '%' . $request->role . '%');
        }

        // Filter by minimum years of experience (if provided and non-negative)
        if ($request->filled('years_exp')) {
            $yearsExp = (int) $request->years_exp;
            if ($yearsExp >= 0) {
                $query->where('years_exp', '>=', $yearsExp);
            }
        }

        // Filter by behavioral category (if provided)
        if ($request->filled('behavioral_category')) {
            $query->whereHas('forms', function ($q) use ($request) {
                $q->where('form_type', 'behavioral')
                  ->where('status', 'submitted')
                  ->whereHas('assessment', function ($aq) use ($request) {
                      $aq->where('category', $request->behavioral_category);
                  });
            });
        }

        // Filter by aptitude category (if provided)
        if ($request->filled('aptitude_category')) {
            $query->whereHas('forms', function ($q) use ($request) {
                $q->where('form_type', 'aptitude')
                  ->where('status', 'submitted')
                  ->whereHas('assessment', function ($aq) use ($request) {
                      $aq->where('category', $request->aptitude_category);
                  });
            });
        }

        $candidates = $query->get();

        // Transform candidates to include lightweight aptitude summary
        $data = $candidates->map(function ($candidate) {
            $assessment = $candidate->latestAptitudeAssessment;
            $aptitudeProfile = $assessment?->aptitude_profile ?? null;

            $overall = $aptitudeProfile['overall_accuracy'] ?? null;
            $summary = $aptitudeProfile['summary'] ?? null;
            $confidence = $aptitudeProfile['confidence'] ?? null;

            // Get first strength dimension label if available
            $topLabel = null;
            if ($aptitudeProfile && !empty($aptitudeProfile['strengths'])) {
                $firstKey = $aptitudeProfile['strengths'][0];
                $dim = $aptitudeProfile['dimensions'][$firstKey] ?? null;
                $topLabel = $dim['label'] ?? null;
            }

            // Get base candidate data
            $candidateData = [
                'id' => $candidate->id,
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'full_name' => $candidate->full_name,
                'role_title' => $candidate->role_title,
                'years_exp' => $candidate->years_exp,
                'user' => $candidate->user ? [
                    'id' => $candidate->user->id,
                    'username' => $candidate->user->username,
                ] : null,
            ];

            // Add aptitude summary if available
            if ($aptitudeProfile) {
                $candidateData['aptitude'] = [
                    'overall_accuracy' => $overall,
                    'top_dimension_label' => $topLabel,
                    'summary' => $summary,
                    'confidence' => $confidence,
                ];
            } else {
                $candidateData['aptitude'] = null;
            }

            return $candidateData;
        });

        return response()->json([
            'candidates' => $data,
            'count' => $candidates->count()
        ]);
    }

    public function saveCandidate(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isRecruiter()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|exists:candidates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $recruiter = $user->recruiter;

        // Check if already saved
        $existing = SavedCandidate::where('recruiter_id', $recruiter->id)
            ->where('candidate_id', $request->candidate_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Candidate already saved'], 400);
        }

        $savedCandidate = SavedCandidate::create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $request->candidate_id,
            'saved_at' => now(),
        ]);

        return response()->json(['saved_candidate' => $savedCandidate], 201);
    }

    public function unsaveCandidate(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isRecruiter()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $recruiter = $user->recruiter;
        $savedCandidate = SavedCandidate::where('recruiter_id', $recruiter->id)
            ->findOrFail($id);

        $savedCandidate->delete();

        return response()->json(['message' => 'Candidate removed from saved list']);
    }

    public function viewCandidate(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isRecruiter()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $candidate = Candidate::with([
            'user',
            'forms.assessment',
            'forms.assessment.answers.question'
        ])->findOrFail($id);

        // Increment review_count for all forms
        $candidate->forms()->increment('review_count');

        return response()->json(['candidate' => $candidate]);
    }

    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isRecruiter()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $recruiter = $user->recruiter;
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'recruiter' => [
                'first_name' => $recruiter->first_name,
                'last_name' => $recruiter->last_name,
                'email' => $recruiter->email,
                'company_name' => $recruiter->company_name,
            ]
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isRecruiter()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $recruiter = $user->recruiter;

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|unique:users,username,' . $user->id . '|max:255',
            'password' => 'nullable|string|min:6',
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:recruiters,email,' . $recruiter->id . '|max:255',
            'company_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update user info
        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('password') && $request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($user->isDirty()) {
            $user->save();
        }

        // Update recruiter info
        if ($request->has('first_name')) {
            $recruiter->first_name = $request->first_name;
        }

        if ($request->has('last_name')) {
            $recruiter->last_name = $request->last_name;
        }

        if ($request->has('email')) {
            $recruiter->email = $request->email;
        }

        if ($request->has('company_name')) {
            $recruiter->company_name = $request->company_name;
        }

        if ($recruiter->isDirty()) {
            $recruiter->save();
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'recruiter' => $recruiter->fresh()
        ]);
    }
}

