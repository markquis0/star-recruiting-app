<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Assessment;
use App\Models\AssessmentType;
use App\Models\AssessmentQuestion;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class CandidateController extends Controller
{
    public function home(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $candidate = $user->candidate;
        $forms = $candidate->forms()->with('assessment')->get();

        return response()->json(['forms' => $forms]);
    }

    public function createForm(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'form_type' => 'required|in:project,behavioral,aptitude',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate = $user->candidate;
        
        // Check if candidate already has a behavioral or aptitude assessment
        if ($request->form_type === 'behavioral' || $request->form_type === 'aptitude') {
            $existingForm = Form::where('candidate_id', $candidate->id)
                ->where('form_type', $request->form_type)
                ->first();
            
            if ($existingForm) {
                $formTypeName = ucfirst($request->form_type);
                return response()->json([
                    'message' => "You already have a {$formTypeName} assessment. You can only create one {$formTypeName} assessment.",
                    'existing_form_id' => $existingForm->id
                ], 409); // 409 Conflict
            }
        }
        
        $form = Form::create([
            'candidate_id' => $candidate->id,
            'form_type' => $request->form_type,
            'status' => 'incomplete',
            'data' => $request->data ?? [],
        ]);

        return response()->json(['form' => $form], 201);
    }

    public function getForm(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $candidate = $user->candidate;
        $form = Form::where('candidate_id', $candidate->id)
            ->with(['assessment', 'assessment.answers.question'])
            ->findOrFail($id);

        return response()->json(['form' => $form]);
    }

    public function updateForm(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'data' => 'nullable|array',
            'status' => 'nullable|in:incomplete,submitted,reviewed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate = $user->candidate;
        $form = Form::where('candidate_id', $candidate->id)->findOrFail($id);

        if ($request->has('data')) {
            $form->data = $request->data;
        }

        if ($request->has('status')) {
            $form->status = $request->status;
        }

        $form->save();

        return response()->json(['form' => $form->fresh()]);
    }

    public function submitAssessment(Request $request, int $formId): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:assessment_questions,id',
            'answers.*.answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate = $user->candidate;
        $form = Form::where('candidate_id', $candidate->id)->findOrFail($formId);

        if ($form->form_type !== 'behavioral' && $form->form_type !== 'aptitude') {
            return response()->json(['message' => 'Invalid form type for assessment'], 400);
        }

        // Determine assessment type
        $assessmentTypeName = $form->form_type === 'behavioral' ? 'Behavioral' : 'Aptitude';
        $assessmentType = AssessmentType::where('name', $assessmentTypeName)->firstOrFail();

        // Create assessment
        $assessment = Assessment::create([
            'form_id' => $form->id,
        ]);

        // Store answers
        foreach ($request->answers as $answerData) {
            $question = AssessmentQuestion::findOrFail($answerData['question_id']);
            
            $score = null;
            if ($form->form_type === 'aptitude' && $question->correct_answer) {
                $score = ($answerData['answer'] === $question->correct_answer) ? 1 : 0;
            }

            $assessment->answers()->create([
                'question_id' => $answerData['question_id'],
                'answer' => $answerData['answer'],
                'score' => $score,
            ]);
        }

        // Evaluate assessment
        $assessmentService = app(AssessmentService::class);
        
        if ($form->form_type === 'behavioral') {
            $assessmentService->evaluateBehavioral($assessment);
        } else {
            // For aptitude, get the profile and save it
            $profile = $assessmentService->evaluateAptitude($assessment);
            $assessment->aptitude_profile = $profile;
            $assessment->total_score = $profile['overall_accuracy'];
            $assessment->category = $profile['dimensions'][$profile['strengths'][0] ?? 'general']['label'] ?? null;
            $assessment->save();
        }

        $form->update(['status' => 'submitted']);

        return response()->json([
            'message' => 'Assessment submitted successfully',
            'assessment' => $assessment->fresh(),
        ], 201);
    }

    public function getResults(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $candidate = $user->candidate;
        $assessments = Assessment::whereHas('form', function ($query) use ($candidate) {
            $query->where('candidate_id', $candidate->id);
        })->with(['form', 'answers.question'])->get();

        return response()->json(['assessments' => $assessments]);
    }

    public function getQuestions(Request $request, string $type): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assessmentTypeName = $type === 'behavioral' ? 'Behavioral' : 'Aptitude';
        $assessmentType = AssessmentType::where('name', $assessmentTypeName)->first();
        
        if (!$assessmentType) {
            return response()->json(['message' => 'Assessment type not found', 'questions' => []], 404);
        }
        
        $questions = AssessmentQuestion::where('assessment_type_id', $assessmentType->id)
            ->orderBy('id')
            ->get();

        return response()->json(['questions' => $questions]);
    }

    public function deleteForm(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $candidate = $user->candidate;
        $form = Form::where('candidate_id', $candidate->id)->findOrFail($id);

        // Delete associated assessment and answers if they exist
        if ($form->assessment) {
            $form->assessment->answers()->delete();
            $form->assessment->delete();
        }

        $form->delete();

        return response()->json(['message' => 'Form deleted successfully']);
    }

    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $candidate = $user->candidate;
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'candidate' => [
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'role_title' => $candidate->role_title,
                'years_exp' => $candidate->years_exp,
            ]
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isCandidate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|unique:users,username,' . $user->id . '|max:255',
            'password' => 'nullable|string|min:6',
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'role_title' => 'nullable|string|max:255',
            'years_exp' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate = $user->candidate;

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

        // Update candidate info
        if ($request->has('first_name')) {
            $candidate->first_name = $request->first_name;
        }

        if ($request->has('last_name')) {
            $candidate->last_name = $request->last_name;
        }

        if ($request->has('role_title')) {
            $candidate->role_title = $request->role_title;
        }

        if ($request->has('years_exp')) {
            $candidate->years_exp = $request->years_exp;
        }

        if ($candidate->isDirty()) {
            $candidate->save();
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'candidate' => $candidate->fresh()
        ]);
    }
}

