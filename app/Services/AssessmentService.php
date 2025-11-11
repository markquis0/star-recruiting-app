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

        $categoryLabels = [
            'analytical' => 'Analytical / Logical Thinking',
            'creative' => 'Creative / Conceptual Thinking',
            'pragmatic' => 'Pragmatic / Execution-Oriented Thinking',
            'relational' => 'Relational / Collaborative Thinking',
            'general' => 'General Aptitude',
        ];

        $categoryShortLabels = [
            'analytical' => 'Analytical',
            'creative' => 'Creative',
            'pragmatic' => 'Pragmatic',
            'relational' => 'Relational',
            'general' => 'General',
        ];

        $categoryData = [];
        $totalEvaluatedQuestions = 0;
        $totalCorrect = 0;

        foreach ($answers as $answer) {
            $question = $answer->question;
            $categoryKey = $question?->trait ?? 'general';

            if (!isset($categoryData[$categoryKey])) {
                $categoryData[$categoryKey] = [
                    'label' => $categoryLabels[$categoryKey] ?? ucfirst($categoryKey),
                    'total_questions' => 0,
                    'evaluated_questions' => 0,
                    'correct_points' => 0,
                    'open_responses' => 0,
                    'examples' => [],
                ];
            }

            $categoryData[$categoryKey]['total_questions']++;

            if ($question && $question->question_type === 'multiple_choice' && $question->correct_answer) {
                $categoryData[$categoryKey]['evaluated_questions']++;
                $categoryData[$categoryKey]['correct_points'] += ($answer->score ?? 0);

                $totalEvaluatedQuestions++;
                $totalCorrect += ($answer->score ?? 0);
            } else {
                if (!empty(trim((string) $answer->answer))) {
                    $categoryData[$categoryKey]['open_responses']++;
                    if (count($categoryData[$categoryKey]['examples']) < 2) {
                        $categoryData[$categoryKey]['examples'][] = mb_strimwidth($answer->answer, 0, 140, '…');
                    }
                }
            }
        }

        $overallAccuracy = null;
        if ($totalEvaluatedQuestions > 0) {
            $overallAccuracy = round(($totalCorrect / $totalEvaluatedQuestions) * 100);
        }

        $profileSummary = null;
        $rankedCategories = collect($categoryData)->map(function ($data, $key) {
            $evaluated = $data['evaluated_questions'];
            $accuracy = null;

            if ($evaluated > 0) {
                $accuracy = $data['correct_points'] / $evaluated * 100;
            }

            return [
                'key' => $key,
                'label' => $data['label'],
                'short_label' => $data['label'],
                'accuracy' => $accuracy,
                'open_responses' => $data['open_responses'],
            ];
        })->values()->map(function ($item) use ($categoryShortLabels) {
            $item['short_label'] = $categoryShortLabels[$item['key']] ?? $item['label'];
            return $item;
        })->sortByDesc(function ($item) {
            if ($item['accuracy'] !== null) {
                return $item['accuracy'];
            }

            // Treat open responses as moderate strength if no accuracy available
            return $item['open_responses'] > 0 ? 55 : 0;
        })->values();

        $primary = $rankedCategories[0] ?? null;
        $secondary = $rankedCategories[1] ?? null;
        $additional = $rankedCategories->slice(2)->filter(function ($item) {
            return $item['open_responses'] > 0;
        })->pluck('short_label')->unique()->values();

        if ($primary) {
            $pieces = [$primary['short_label']];
            if ($secondary) {
                $pieces[] = $secondary['short_label'];
            }

            $profileSummary = implode(' + ', $pieces) . ' thinker';

            if ($additional->isNotEmpty()) {
                $profileSummary .= ', with ' . $additional->implode(' and ') . ' focus';
            }
        }

        $scoreSummary = [];
        foreach ($categoryData as $key => $data) {
            $evaluated = $data['evaluated_questions'];
            $accuracy = null;
            if ($evaluated > 0) {
                $accuracy = round(($data['correct_points'] / $evaluated) * 100);
            }

            $scoreSummary[$key] = [
                'label' => $data['label'],
                'total_questions' => $data['total_questions'],
                'evaluated_questions' => $evaluated,
                'open_responses' => $data['open_responses'],
                'accuracy' => $accuracy,
                'examples' => $data['examples'],
            ];
        }

        $primaryCategoryLabel = $primary['label'] ?? null;

        $assessment->update([
            'total_score' => $overallAccuracy,
            'category' => $primaryCategoryLabel,
            'score_summary' => [
                'categories' => $scoreSummary,
                'overall_accuracy' => $overallAccuracy,
                'profile_summary' => $profileSummary,
            ],
        ]);
    }
}

