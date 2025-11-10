<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssessmentController extends Controller
{
    public function evaluateBehavioral(Request $request): JsonResponse
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
        ]);

        $assessment = Assessment::findOrFail($request->assessment_id);
        app(AssessmentService::class)->evaluateBehavioral($assessment);

        return response()->json([
            'message' => 'Behavioral assessment evaluated',
            'assessment' => $assessment->fresh(),
        ]);
    }

    public function evaluateAptitude(Request $request): JsonResponse
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
        ]);

        $assessment = Assessment::findOrFail($request->assessment_id);
        app(AssessmentService::class)->evaluateAptitude($assessment);

        return response()->json([
            'message' => 'Aptitude assessment evaluated',
            'assessment' => $assessment->fresh(),
        ]);
    }
}

