<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssessmentType;
use App\Models\AssessmentQuestion;

class AssessmentQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $behavioral = AssessmentType::where('name', 'Behavioral')->first();
        $aptitude = AssessmentType::where('name', 'Aptitude')->first();

        // Clear existing question sets to avoid duplicates when reseeding
        AssessmentQuestion::where('assessment_type_id', $behavioral->id)->delete();
        AssessmentQuestion::where('assessment_type_id', $aptitude->id)->delete();

        // Behavioral Questions (Rating Scale 1-5)
        $behavioralQuestions = [
            // Analytical (1-5)
            ['I enjoy solving complex problems', 'rating_scale', null, null, 'analytical'],
            ['I prefer data-driven decisions', 'rating_scale', null, null, 'analytical'],
            ['I analyze situations before acting', 'rating_scale', null, null, 'analytical'],
            ['I enjoy working with numbers and statistics', 'rating_scale', null, null, 'analytical'],
            ['I break down problems into smaller parts', 'rating_scale', null, null, 'analytical'],
            
            // Collaborative (6-10)
            ['I work well in team settings', 'rating_scale', null, null, 'collaborative'],
            ['I value input from others', 'rating_scale', null, null, 'collaborative'],
            ['I enjoy group brainstorming sessions', 'rating_scale', null, null, 'collaborative'],
            ['I share credit for team successes', 'rating_scale', null, null, 'collaborative'],
            ['I communicate effectively with team members', 'rating_scale', null, null, 'collaborative'],
            
            // Persistent (11-15)
            ['I don\'t give up easily', 'rating_scale', null, null, 'persistent'],
            ['I complete tasks despite obstacles', 'rating_scale', null, null, 'persistent'],
            ['I maintain focus on long-term goals', 'rating_scale', null, null, 'persistent'],
            ['I push through difficult challenges', 'rating_scale', null, null, 'persistent'],
            ['I stay committed to projects', 'rating_scale', null, null, 'persistent'],
            
            // Social (16-20)
            ['I enjoy networking events', 'rating_scale', null, null, 'social'],
            ['I build relationships easily', 'rating_scale', null, null, 'social'],
            ['I enjoy public speaking', 'rating_scale', null, null, 'social'],
            ['I am comfortable in social settings', 'rating_scale', null, null, 'social'],
            ['I connect with people from diverse backgrounds', 'rating_scale', null, null, 'social'],
        ];

        foreach ($behavioralQuestions as $index => $question) {
            AssessmentQuestion::updateOrCreate(
                [
                    'assessment_type_id' => $behavioral->id,
                    'question_text' => $question[0],
                ],
                [
                    'question_type' => $question[1],
                    'options' => json_encode(['1', '2', '3', '4', '5']),
                    'correct_answer' => $question[3],
                    'weight' => 1.0,
                    'trait' => $question[4],
                ]
            );
        }

        // Aptitude Questions (Multiple Choice + Open-Ended)
        $aptitudeQuestions = [
            // Category 1: Analytical & Logical Thinking (7 questions: 5 MC + 2 open-ended)
            [
                'category' => 'analytical',
                'text' => 'Pattern Logic: Which number best completes the sequence: 5, 9, 17, 33, ?',
                'type' => 'multiple_choice',
                'options' => ['A' => '49', 'B' => '57', 'C' => '65', 'D' => '41'],
                'answer' => 'C',
            ],
            [
                'category' => 'analytical',
                'text' => 'Basic Deduction: All projects requiring budget approval must be reviewed on Tuesdays. The Apollo project is reviewed on Tuesday. What can you conclude?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Apollo required budget approval', 'B' => 'Apollo did not require approval', 'C' => 'Maybe Apollo required approval', 'D' => 'Review day does not indicate approval'],
                'answer' => 'C',
            ],
            [
                'category' => 'analytical',
                'text' => 'Information Synthesis: A dashboard shows a sharp upward spike in customer messages at 9 PM daily. What is the most reasonable first step?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Increase staffing at 9 PM', 'B' => 'Investigate why users contact support at that time', 'C' => 'Disable chat at 9 PM', 'D' => 'Assume it\'s a random pattern'],
                'answer' => 'B',
            ],
            [
                'category' => 'analytical',
                'text' => 'Logical Puzzle: If A → B and B → C, which must be true?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'A → C', 'B' => 'C → A', 'C' => 'A and C are unrelated', 'D' => 'B causes A'],
                'answer' => 'A',
            ],
            [
                'category' => 'analytical',
                'text' => 'Data Reasoning: Which visual would best detect an outlier in daily sales?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Line graph', 'B' => 'Bar chart', 'C' => 'Box plot', 'D' => 'Pie chart'],
                'answer' => 'C',
            ],
            [
                'category' => 'analytical',
                'text' => 'Describe how you would evaluate whether two seemingly unrelated trends are connected.',
                'type' => 'open_ended',
            ],
            [
                'category' => 'analytical',
                'text' => 'Explain how you would break down a complex problem you\'ve never encountered before.',
                'type' => 'open_ended',
            ],

            // Category 2: Creative & Conceptual Thinking (6 questions: 3 MC + 3 open-ended)
            [
                'category' => 'creative',
                'text' => 'Reframing: Your team wants to increase customer loyalty without spending more on marketing. Which approach represents reframing the problem?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Increase email frequency', 'B' => 'Improve product experience', 'C' => 'Run a discount campaign', 'D' => 'Hire a loyalty specialist'],
                'answer' => 'B',
            ],
            [
                'category' => 'creative',
                'text' => 'Analogy Matching: Improving a workflow is most similar to:',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Editing a document', 'B' => 'Painting a picture', 'C' => 'Solving a mystery', 'D' => 'Traveling to a new country'],
                'answer' => 'C',
            ],
            [
                'category' => 'creative',
                'text' => 'Concept Link: Which pair is most conceptually unrelated?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Strategy & Planning', 'B' => 'Feedback & Improvement', 'C' => 'Deadlines & Time', 'D' => 'Creativity & Regulations'],
                'answer' => 'D',
            ],
            [
                'category' => 'creative',
                'text' => 'Provide one unconventional idea to make a routine internal tool genuinely enjoyable to use.',
                'type' => 'open_ended',
            ],
            [
                'category' => 'creative',
                'text' => 'Describe how you would redesign a broken process without knowing who caused the issues.',
                'type' => 'open_ended',
            ],
            [
                'category' => 'creative',
                'text' => 'Imagine you could eliminate one frustrating user experience from any product you\'ve used. What would it be and why?',
                'type' => 'open_ended',
            ],

            // Category 3: Execution, Prioritization & Decision-Making (6 questions: 3 MC + 3 open-ended)
            [
                'category' => 'pragmatic',
                'text' => 'Prioritization Under Pressure: You receive an urgent request, a high-effort task, and a small but important fix. What do you do first?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Urgent request', 'B' => 'High-effort task', 'C' => 'Small important fix', 'D' => 'Ask for clarification on priorities'],
                'answer' => 'D',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Scenario Evaluation: A deliverable is at risk due to unclear requirements. What is your first action?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Continue with assumptions', 'B' => 'Escalate immediately', 'C' => 'Clarify requirements with stakeholders', 'D' => 'Freeze work until told otherwise'],
                'answer' => 'C',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Efficiency Judgment: A recurring operational issue causes 10–15 minutes of daily slowdown. Best approach?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Ignore it unless it becomes critical', 'B' => 'Investigate to eliminate root cause', 'C' => 'Document a workaround', 'D' => 'Train people to manage it faster'],
                'answer' => 'B',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'How do you decide when a task needs a perfect solution vs. a good-enough solution?',
                'type' => 'open_ended',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Describe a time you had to adjust your plan quickly. What was your decision process?',
                'type' => 'open_ended',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Explain how you would plan a 30-day cycle of work with multiple moving parts.',
                'type' => 'open_ended',
            ],

            // Category 4: Relational & Collaborative Thinking (6 questions: 3 MC + 3 open-ended)
            [
                'category' => 'relational',
                'text' => 'Conflict Navigation: Two colleagues disagree strongly about an approach. What\'s the most productive action?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Choose a side', 'B' => 'Facilitate a structured discussion', 'C' => 'Let them resolve it privately', 'D' => 'Escalate immediately'],
                'answer' => 'B',
            ],
            [
                'category' => 'relational',
                'text' => 'Communication Style: You must explain a new process to someone struggling to understand it. What do you do?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Repeat the explanation more slowly', 'B' => 'Use examples relevant to their role', 'C' => 'Send them documentation', 'D' => 'Involve their manager'],
                'answer' => 'B',
            ],
            [
                'category' => 'relational',
                'text' => 'Team Decision-Making: In a meeting, you realize the group is moving toward a poor decision. Best response?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Immediately interrupt', 'B' => 'Ask questions that challenge assumptions', 'C' => 'Let the team learn from mistakes', 'D' => 'Stop the meeting'],
                'answer' => 'B',
            ],
            [
                'category' => 'relational',
                'text' => 'How do you ensure everyone feels heard in group discussions?',
                'type' => 'open_ended',
            ],
            [
                'category' => 'relational',
                'text' => 'Describe your approach when someone strongly disagrees with your idea.',
                'type' => 'open_ended',
            ],
            [
                'category' => 'relational',
                'text' => 'Explain how you build trust with new teammates or cross-functional partners.',
                'type' => 'open_ended',
            ],
        ];

        foreach ($aptitudeQuestions as $question) {
            AssessmentQuestion::updateOrCreate(
                [
                    'assessment_type_id' => $aptitude->id,
                    'question_text' => $question['text'],
                ],
                [
                    'question_type' => $question['type'],
                    'options' => (array_key_exists('options', $question) && $question['options']) ? json_encode($question['options']) : null,
                    'correct_answer' => $question['answer'] ?? null,
                    'weight' => 1.0,
                    'trait' => $question['category'],
                ]
            );
        }
    }
}

