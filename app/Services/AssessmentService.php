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

        // Initialize category data for the four main aptitude dimensions
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
            $categoryKey = $question->trait;
            
            // Skip questions without a trait or with invalid traits
            if (empty($categoryKey) || !isset($categoryData[$categoryKey])) {
                // Log warning for questions without proper trait assignment
                \Log::warning("Aptitude question missing or invalid trait", [
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'trait' => $categoryKey,
                ]);
                continue; // Skip this question
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

        // Build normalized dimension results (only include dimensions with data)
        $dimensions = [];

        foreach ($categoryData as $key => $data) {
            $maxPoints  = $data['max_points'];
            $accuracy   = null;

            if ($maxPoints > 0) {
                $accuracy = round(($data['correct_points'] / $maxPoints) * 100);
            }

            // Only include dimensions that have evaluated questions
            // This filters out empty dimensions and prevents "General" from appearing
            if ($data['evaluated_questions'] > 0 || count($data['open_responses']) > 0) {
                $dimensions[$key] = [
                    'label'               => $data['label'],
                    'accuracy'            => $accuracy,                 // weighted %
                    'correct_points'      => $data['correct_points'],   // weighted earned
                    'max_points'          => $data['max_points'],       // weighted max
                    'evaluated_questions' => $data['evaluated_questions'],
                    'open_responses'      => $data['open_responses'],
                ];
            }
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

        $profile = [
            'overall_accuracy' => $overallAccuracy,
            'strengths'        => $strengths,   // e.g. ['decision_prioritization','logic_reasoning']
            'dimensions'       => $dimensions,  // keyed by trait
        ];

        // Add human-readable summary
        $profile['summary'] = $this->describeAptitudeStrengths($profile);

        // Add confidence indicator
        $profile['confidence'] = $this->calculateAptitudeConfidence(
            $profile['overall_accuracy'],
            $profile['dimensions']
        );

        return $profile;
    }

    /**
     * Generate a human-readable summary of aptitude strengths.
     *
     * @param  array  $profile
     * @return string|null
     */
    public function describeAptitudeStrengths(array $profile): ?string
    {
        $dimensions  = $profile['dimensions'] ?? [];
        $strengthKeys = $profile['strengths'] ?? [];

        if (empty($dimensions) || empty($strengthKeys)) {
            return null;
        }

        $labels = [];
        foreach ($strengthKeys as $key) {
            if (isset($dimensions[$key]['label'])) {
                $labels[] = $dimensions[$key]['label'];
            }
        }

        if (count($labels) === 0) {
            return null;
        }

        if (count($labels) === 1) {
            return "Strong in {$labels[0]}.";
        }

        if (count($labels) === 2) {
            return "Strong in {$labels[0]} and {$labels[1]}.";
        }

        // Fallback if somehow more than 2 keys
        $last = array_pop($labels);
        return "Strong in " . implode(', ', $labels) . " and {$last}.";
    }

    /**
     * Calculate confidence level based on overall accuracy and dimension scores.
     *
     * @param  int|null  $overallAccuracy
     * @param  array  $dimensions
     * @return string|null
     */
    public function calculateAptitudeConfidence(?int $overallAccuracy, array $dimensions): ?string
    {
        if ($overallAccuracy === null) {
            return null;
        }

        // Simple confidence definition:
        // High: 80%+ overall and at least 2 dimensions >= 75%
        // Medium: 60–79% overall
        // Low: < 60% or not enough data
        $scores = collect($dimensions)
            ->pluck('accuracy')
            ->filter(fn ($v) => !is_null($v))
            ->values();

        $strongDims = $scores->filter(fn ($v) => $v >= 75)->count();

        if ($overallAccuracy >= 80 && $strongDims >= 2) {
            return 'high';
        }

        if ($overallAccuracy >= 60) {
            return 'medium';
        }

        return 'low';
    }
}

