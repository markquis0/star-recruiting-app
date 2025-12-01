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

    /**
     * Evaluate an aptitude assessment using weighted scoring.
     *
     * @param  \App\Models\Assessment  $assessment
     * @return array
     */
    public function evaluateAptitude(Assessment $assessment): array
    {
        // Aptitude dimensions (non-overlapping with behavioral assessment)
        $dimensionLabels = [
            'logic_reasoning'        => 'Logic & Reasoning',
            'conceptual_strategic'   => 'Conceptual & Strategic Thinking',
            'decision_prioritization'=> 'Decision-Making & Prioritization',
            'people_insight'         => 'People Insight & Communication',
        ];

        // Initialize category data
        $categoryData = [];
        foreach ($dimensionLabels as $key => $label) {
            $categoryData[$key] = [
                'key'                 => $key,
                'label'               => $label,
                'evaluated_questions' => 0,
                'correct_points'      => 0.0,  // weighted earned points
                'max_points'          => 0.0,  // weighted max points
                'open_responses'      => [],   // up to 2 examples (truncated)
            ];
        }

        // Optional catch-all for legacy / misc traits
        $categoryData['general'] = [
            'key'                 => 'general',
            'label'               => 'General',
            'evaluated_questions' => 0,
            'correct_points'      => 0.0,
            'max_points'          => 0.0,
            'open_responses'      => [],
        ];

        $totalCorrectWeighted = 0.0;
        $totalMaxWeighted     = 0.0;

        // Ensure questions are eager loaded: answers.question
        $assessment->loadMissing('answers.question');

        foreach ($assessment->answers as $answer) {
            $question = $answer->question;

            if (!$question) {
                continue;
            }

            // Determine which dimension this question belongs to
            $categoryKey = $question->trait ?? 'general';
            if (!isset($categoryData[$categoryKey])) {
                // If we encounter an unexpected trait, group it under "general"
                $categoryKey = 'general';
            }

            // Handle multiple-choice questions (weighted scoring)
            if (
                $question->question_type === 'multiple_choice'
                && !empty($question->correct_answer)
            ) {
                $weight = $question->weight ?? 1.0;
                if ($weight <= 0) {
                    $weight = 1.0; // sane default
                }

                // score is already 1 or 0 from submitAssessment
                $rawScore = (float) ($answer->score ?? 0);
                $earned   = $rawScore * $weight;

                $categoryData[$categoryKey]['evaluated_questions']++;
                $categoryData[$categoryKey]['correct_points'] += $earned;
                $categoryData[$categoryKey]['max_points']     += $weight;

                $totalCorrectWeighted += $earned;
                $totalMaxWeighted     += $weight;
            }

            // Handle open-ended questions (tracked but not scored)
            if ($question->question_type === 'open_ended') {
                if (count($categoryData[$categoryKey]['open_responses']) < 2) {
                    $text = (string) ($answer->answer ?? '');

                    // Truncate to 140 chars with ellipsis
                    if (mb_strlen($text) > 140) {
                        $text = mb_substr($text, 0, 140) . '...';
                    }

                    if ($text !== '') {
                        $categoryData[$categoryKey]['open_responses'][] = $text;
                    }
                }
            }
        }

        // Build normalized dimension results
        $dimensions = [];

        foreach ($categoryData as $key => $data) {
            $maxPoints  = $data['max_points'];
            $accuracy   = null;

            if ($maxPoints > 0) {
                $accuracy = round(($data['correct_points'] / $maxPoints) * 100);
            }

            // Use explicit label if defined, fall back to generated
            $label = $data['label']
                ?? ucfirst(str_replace('_', ' ', $key));

            $dimensions[$key] = [
                'label'               => $label,
                'accuracy'            => $accuracy,                 // weighted %
                'correct_points'      => $data['correct_points'],   // weighted earned
                'max_points'          => $data['max_points'],       // weighted max
                'evaluated_questions' => $data['evaluated_questions'],
                'open_responses'      => $data['open_responses'],
            ];
        }

        // Overall weighted accuracy
        $overallAccuracy = null;
        if ($totalMaxWeighted > 0) {
            $overallAccuracy = round(($totalCorrectWeighted / $totalMaxWeighted) * 100);
        }

        // Derive top 2 strengths from the four main aptitude dimensions only
        $strengths = collect($dimensions)
            ->filter(function ($dim, $key) use ($dimensionLabels) {
                return array_key_exists($key, $dimensionLabels) && !is_null($dim['accuracy']);
            })
            ->sortByDesc('accuracy')
            ->take(2)
            ->keys()
            ->values()
            ->all();

        return [
            'overall_accuracy' => $overallAccuracy,
            'strengths'        => $strengths,   // e.g. ['decision_prioritization','logic_reasoning']
            'dimensions'       => $dimensions,  // keyed by trait
        ];
    }
}

