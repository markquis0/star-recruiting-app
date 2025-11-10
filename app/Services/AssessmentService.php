<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentQuestion;

class AssessmentService
{
    public function evaluateBehavioral(Assessment $assessment): void
    {
        $answers = $assessment->answers()->with('question')->get();
        
        $traits = [
            'analytical' => 0,
            'collaborative' => 0,
            'persistent' => 0,
            'social' => 0,
        ];

        $traitCounts = [
            'analytical' => 0,
            'collaborative' => 0,
            'persistent' => 0,
            'social' => 0,
        ];

        // Map questions to traits based on question trait field
        foreach ($answers as $answer) {
            $rating = (int) $answer->answer;
            $trait = $answer->question->trait;
            
            if ($trait && isset($traits[$trait])) {
                $traits[$trait] += $rating;
                $traitCounts[$trait]++;
            }
        }

        // Calculate percentages (scale 1-5 to 0-100)
        $scoreSummary = [];
        foreach ($traits as $trait => $total) {
            if ($traitCounts[$trait] > 0) {
                $average = ($total / $traitCounts[$trait]) / 5 * 100;
                $scoreSummary[$trait] = round($average);
            } else {
                $scoreSummary[$trait] = 0;
            }
        }

        // Determine category (highest scoring trait)
        $category = array_search(max($scoreSummary), $scoreSummary);
        $category = ucfirst($category);

        $assessment->update([
            'score_summary' => $scoreSummary,
            'category' => $category,
        ]);
    }

    public function evaluateAptitude(Assessment $assessment): void
    {
        $answers = $assessment->answers()->with('question')->get();
        
        $totalQuestions = $answers->count();
        $correctAnswers = $answers->sum('score');

        if ($totalQuestions > 0) {
            $percentage = ($correctAnswers / $totalQuestions) * 100;
        } else {
            $percentage = 0;
        }

        // Determine category
        if ($percentage >= 80) {
            $category = 'High Aptitude';
        } elseif ($percentage >= 60) {
            $category = 'Moderate Aptitude';
        } else {
            $category = 'Low Aptitude';
        }

        $assessment->update([
            'total_score' => (int) $percentage,
            'category' => $category,
        ]);
    }
}

