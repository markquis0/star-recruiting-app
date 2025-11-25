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

        // Aptitude Questions (Multiple Choice + Open Text)
        $aptitudeQuestions = [
            // Category 1: Analytical / Logical Thinking (6)
            [
                'category' => 'analytical',
                'text' => 'Pattern Recognition: Sequence 3, 6, 12, 24, ?',
                'type' => 'multiple_choice',
                'options' => ['A' => '36', 'B' => '48', 'C' => '42', 'D' => '54'],
                'answer' => 'B',
            ],
            [
                'category' => 'analytical',
                'text' => 'Logic Puzzle: Three servers are connected. Server A can only send messages to Server C via Server B. What is the network topology?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Star', 'B' => 'Ring', 'C' => 'Bus', 'D' => 'Mesh'],
                'answer' => 'B',
            ],
            [
                'category' => 'analytical',
                'text' => 'Data Interpretation: Daily active users: 150, 180, 210, 240, 300. Approximate average daily growth rate?',
                'type' => 'multiple_choice',
                'options' => ['A' => '5%', 'B' => '10%', 'C' => '15%', 'D' => '20%'],
                'answer' => 'D',
            ],
            [
                'category' => 'analytical',
                'text' => 'Deductive Reasoning: If all engineers attend a tech talk, and Jane attends all tech talks, what can we conclude?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Jane is an engineer', 'B' => 'Jane might be an engineer', 'C' => 'Jane is not an engineer', 'D' => 'Cannot determine'],
                'answer' => 'B',
            ],
            [
                'category' => 'analytical',
                'text' => 'Abstract Reasoning: Which figure completes the series? (Refer to Figure Set A in the assessment.)',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Figure A', 'B' => 'Figure B', 'C' => 'Figure C', 'D' => 'Figure D'],
                'answer' => 'C',
            ],
            [
                'category' => 'analytical',
                'text' => 'Algorithmic Thinking: You need to sort a list of 10,000 numbers efficiently. Which approach is best?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Bubble sort', 'B' => 'Quick sort', 'C' => 'Selection sort', 'D' => 'Insertion sort'],
                'answer' => 'B',
            ],

            // Category 2: Creative / Conceptual Thinking (6)
            [
                'category' => 'creative',
                'text' => 'Problem Reframing: Your company wants to reduce support tickets by 50% without hiring staff. What approach would you take?',
                'type' => 'open_text',
            ],
            [
                'category' => 'creative',
                'text' => 'Analogical Thinking: Which is most analogous to debugging software?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Gardening', 'B' => 'Cooking', 'C' => 'Solving a mystery', 'D' => 'Driving a car'],
                'answer' => 'C',
            ],
            [
                'category' => 'creative',
                'text' => 'Idea Generation: You have a study app. Name three unconventional ways to increase engagement.',
                'type' => 'open_text',
            ],
            [
                'category' => 'creative',
                'text' => 'Conceptual Connection: Which two items are least related?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Cloud storage & Dropbox', 'B' => 'AI & Machine Learning', 'C' => 'Keyboard & Mouse', 'D' => 'Database & Solar Panel'],
                'answer' => 'D',
            ],
            [
                'category' => 'creative',
                'text' => 'Innovation Challenge: Imagine a meeting scheduling tool. How could you make it delight users in an unexpected way?',
                'type' => 'open_text',
            ],
            [
                'category' => 'creative',
                'text' => 'Visual Pattern Recognition: Which option completes the abstract shape sequence? (Refer to Figure Set B in the assessment.)',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Option A', 'B' => 'Option B', 'C' => 'Option C', 'D' => 'Option D'],
                'answer' => 'B',
            ],

            // Category 3: Pragmatic / Execution-Oriented Thinking (7)
            [
                'category' => 'pragmatic',
                'text' => 'Prioritization: You have three tasks—deploy a critical bug fix, update documentation, conduct team training. Which do you tackle first and why?',
                'type' => 'open_text',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Scenario Analysis: A project is behind schedule. Options: cut a feature, extend the deadline, add resources, or negotiate scope. Your choice?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Cut feature', 'B' => 'Extend deadline', 'C' => 'Add resources', 'D' => 'Negotiate scope'],
                'answer' => 'D',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Efficiency Assessment: A support issue keeps recurring. What do you address first?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Root cause', 'B' => 'Workaround', 'C' => 'User training', 'D' => 'Combination'],
                'answer' => 'A',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Risk Assessment: You notice a minor bug shortly before release. Do you ship now or delay? Explain your decision.',
                'type' => 'open_text',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Process Optimization: You notice repetitive manual work in your team. How do you address it?',
                'type' => 'open_text',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Decision under Constraint: Your budget is reduced by 20%. Which project adjustments do you prioritize?',
                'type' => 'open_text',
            ],
            [
                'category' => 'pragmatic',
                'text' => 'Goal Setting: How would you plan your team’s work for a month-long sprint?',
                'type' => 'open_text',
            ],

            // Category 4: Relational / Collaborative Thinking (6)
            [
                'category' => 'relational',
                'text' => 'Conflict Resolution: Two engineers disagree on implementation details. How do you handle it?',
                'type' => 'open_text',
            ],
            [
                'category' => 'relational',
                'text' => 'Communication Style: Explain a complex technical concept to a non-technical stakeholder. What approach do you take?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Step-by-step technical explanation', 'B' => 'Use analogies', 'C' => 'Provide a summary with visuals', 'D' => 'Delegate explanation'],
                'answer' => 'C',
            ],
            [
                'category' => 'relational',
                'text' => 'Team Problem Solving: Someone proposes a flawed solution in a meeting. What do you do?',
                'type' => 'multiple_choice',
                'options' => ['A' => 'Critique openly', 'B' => 'Suggest alternatives', 'C' => 'Wait for others to respond', 'D' => 'Discuss privately later'],
                'answer' => 'B',
            ],
            [
                'category' => 'relational',
                'text' => 'Leadership Style: How would you encourage collaboration in a remote team?',
                'type' => 'open_text',
            ],
            [
                'category' => 'relational',
                'text' => 'Feedback Reception: You receive feedback that your solution is inefficient. How do you respond?',
                'type' => 'open_text',
            ],
            [
                'category' => 'relational',
                'text' => 'Interpersonal Influence: You need buy-in from multiple teams for a project. How do you approach it?',
                'type' => 'open_text',
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

