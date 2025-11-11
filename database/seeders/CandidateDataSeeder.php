<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Form;
use App\Models\Assessment;
use App\Models\AssessmentType;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentAnswer;
use App\Services\AssessmentService;
use Illuminate\Support\Facades\Hash;

class CandidateDataSeeder extends Seeder
{
    public function run(): void
    {
        $assessmentService = app(AssessmentService::class);
        $behavioralType = AssessmentType::where('name', 'Behavioral')->first();
        $aptitudeType = AssessmentType::where('name', 'Aptitude')->first();
        
        // Get all questions
        $behavioralQuestions = AssessmentQuestion::where('assessment_type_id', $behavioralType->id)
            ->orderBy('id')
            ->get();
        $aptitudeQuestions = AssessmentQuestion::where('assessment_type_id', $aptitudeType->id)
            ->orderBy('id')
            ->get();

        // Candidate data with varied profiles
        $candidates = [
            [
                'username' => 'john_developer',
                'password' => 'password123',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'role_title' => 'Senior Software Engineer',
                'years_exp' => 8,
                'behavioral_category' => 'Analytical', // High analytical scores
                'aptitude_category' => 'High Aptitude', // 90% score
            ],
            [
                'username' => 'sarah_manager',
                'password' => 'password123',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'role_title' => 'Project Manager',
                'years_exp' => 5,
                'behavioral_category' => 'Collaborative', // High collaborative scores
                'aptitude_category' => 'Moderate Aptitude', // 70% score
            ],
            [
                'username' => 'mike_engineer',
                'password' => 'password123',
                'first_name' => 'Michael',
                'last_name' => 'Williams',
                'role_title' => 'Software Engineer',
                'years_exp' => 3,
                'behavioral_category' => 'Persistent', // High persistent scores
                'aptitude_category' => 'High Aptitude', // 85% score
            ],
            [
                'username' => 'emily_designer',
                'password' => 'password123',
                'first_name' => 'Emily',
                'last_name' => 'Brown',
                'role_title' => 'UX Designer',
                'years_exp' => 4,
                'behavioral_category' => 'Social', // High social scores
                'aptitude_category' => 'Moderate Aptitude', // 65% score
            ],
            [
                'username' => 'david_lead',
                'password' => 'password123',
                'first_name' => 'David',
                'last_name' => 'Davis',
                'role_title' => 'Tech Lead',
                'years_exp' => 10,
                'behavioral_category' => 'Analytical', // High analytical scores
                'aptitude_category' => 'High Aptitude', // 95% score
            ],
            [
                'username' => 'lisa_analyst',
                'password' => 'password123',
                'first_name' => 'Lisa',
                'last_name' => 'Miller',
                'role_title' => 'Data Analyst',
                'years_exp' => 2,
                'behavioral_category' => 'Analytical', // High analytical scores
                'aptitude_category' => 'Low Aptitude', // 50% score
            ],
            [
                'username' => 'james_devops',
                'password' => 'password123',
                'first_name' => 'James',
                'last_name' => 'Wilson',
                'role_title' => 'DevOps Engineer',
                'years_exp' => 6,
                'behavioral_category' => 'Collaborative', // High collaborative scores
                'aptitude_category' => 'High Aptitude', // 88% score
            ],
            [
                'username' => 'jennifer_marketing',
                'password' => 'password123',
                'first_name' => 'Jennifer',
                'last_name' => 'Moore',
                'role_title' => 'Marketing Manager',
                'years_exp' => 7,
                'behavioral_category' => 'Social', // High social scores
                'aptitude_category' => 'Moderate Aptitude', // 72% score
            ],
            [
                'username' => 'robert_junior',
                'password' => 'password123',
                'first_name' => 'Robert',
                'last_name' => 'Taylor',
                'role_title' => 'Junior Developer',
                'years_exp' => 1,
                'behavioral_category' => 'Persistent', // High persistent scores
                'aptitude_category' => 'Low Aptitude', // 45% score
            ],
            [
                'username' => 'amanda_senior',
                'password' => 'password123',
                'first_name' => 'Amanda',
                'last_name' => 'Anderson',
                'role_title' => 'Senior Developer',
                'years_exp' => 9,
                'behavioral_category' => 'Collaborative', // High collaborative scores
                'aptitude_category' => 'High Aptitude', // 92% score
            ],
        ];

        foreach ($candidates as $candidateData) {
            // Check if user already exists
            $existingUser = User::where('username', $candidateData['username'])->first();
            if ($existingUser) {
                continue; // Skip if user already exists
            }
            
            // Create user
            $user = User::create([
                'username' => $candidateData['username'],
                'password' => Hash::make($candidateData['password']),
                'role' => 'candidate',
            ]);

            // Create candidate profile
            $candidate = Candidate::create([
                'user_id' => $user->id,
                'first_name' => $candidateData['first_name'],
                'last_name' => $candidateData['last_name'],
                'role_title' => $candidateData['role_title'],
                'years_exp' => $candidateData['years_exp'],
            ]);

            // Create behavioral assessment form
            $behavioralForm = Form::create([
                'candidate_id' => $candidate->id,
                'form_type' => 'behavioral',
                'status' => 'submitted',
                'data' => [],
                'review_count' => 0,
            ]);

            // Create behavioral assessment
            $behavioralAssessment = Assessment::create([
                'form_id' => $behavioralForm->id,
            ]);

            // Generate behavioral answers based on desired category
            $behavioralAnswers = $this->generateBehavioralAnswers(
                $behavioralQuestions,
                $candidateData['behavioral_category']
            );

            foreach ($behavioralAnswers as $answerData) {
                $behavioralAssessment->answers()->create([
                    'question_id' => $answerData['question_id'],
                    'answer' => $answerData['answer'],
                    'score' => null,
                ]);
            }

            // Evaluate behavioral assessment
            $assessmentService->evaluateBehavioral($behavioralAssessment);

            // Create aptitude assessment form
            $aptitudeForm = Form::create([
                'candidate_id' => $candidate->id,
                'form_type' => 'aptitude',
                'status' => 'submitted',
                'data' => [],
                'review_count' => 0,
            ]);

            // Create aptitude assessment
            $aptitudeAssessment = Assessment::create([
                'form_id' => $aptitudeForm->id,
            ]);

            // Generate aptitude answers based on desired category
            $aptitudeAnswers = $this->generateAptitudeAnswers(
                $aptitudeQuestions,
                $candidateData['aptitude_category']
            );

            foreach ($aptitudeAnswers as $answerData) {
                $aptitudeAssessment->answers()->create([
                    'question_id' => $answerData['question_id'],
                    'answer' => $answerData['answer'],
                    'score' => $answerData['score'],
                ]);
            }

            // Evaluate aptitude assessment
            $assessmentService->evaluateAptitude($aptitudeAssessment);

            // Create a project form for some candidates
            if (rand(0, 1)) {
                Form::create([
                    'candidate_id' => $candidate->id,
                    'form_type' => 'project',
                    'status' => 'submitted',
                    'data' => [
                        'project_name' => 'Sample Project ' . $candidate->first_name,
                        'description' => 'A sample project description',
                        'technologies' => ['PHP', 'Laravel', 'MySQL'],
                        'role' => 'Developer',
                    ],
                    'review_count' => 0,
                ]);
            }
        }
    }

    private function generateBehavioralAnswers($questions, $targetCategory): array
    {
        $answers = [];
        $targetTrait = strtolower($targetCategory);

        foreach ($questions as $question) {
            $trait = $question->trait;
            
            // Give higher ratings (4-5) for target trait, moderate (3) for others
            if ($trait === $targetTrait) {
                $rating = rand(4, 5); // High rating for target trait
            } else {
                $rating = rand(2, 3); // Lower rating for other traits
            }
            
            $answers[] = [
                'question_id' => $question->id,
                'answer' => (string) $rating,
            ];
        }

        return $answers;
    }

    private function generateAptitudeAnswers($questions, $targetCategory): array
    {
        $answers = [];
        
        // Determine target score percentage
        $targetScore = match($targetCategory) {
            'High Aptitude' => 0.85, // 85-100% correct
            'Moderate Aptitude' => 0.65, // 60-75% correct
            'Low Aptitude' => 0.45, // 40-50% correct
            default => 0.65,
        };

        $openResponseSamples = [
            'analytical' => [
                'I would outline assumptions, test each quickly, and validate the data before acting.',
                'Start by mapping the logical flow, confirm dependencies, and quantify impact before deciding.'
            ],
            'creative' => [
                'I would design an interactive onboarding that adapts to the user’s study style and schedule.',
                'Introduce collaborative challenges with surprise rewards to keep learners curious and engaged.'
            ],
            'pragmatic' => [
                'Stabilize production first, communicate the plan, and schedule knowledge sharing once risk is reduced.',
                'I would capture the manual steps, automate the repetitive pieces, and document the new flow.'
            ],
            'relational' => [
                'Facilitate a quick sync, surface the shared objective, and guide the team toward a combined solution.',
                'Begin with appreciation for the feedback, ask clarifying questions, and iterate on the proposal together.'
            ],
        ];
 
        foreach ($questions as $question) {
            if ($question->question_type === 'open_text') {
                $categoryKey = $question->trait ?? 'general';
                $samples = $openResponseSamples[$categoryKey] ?? ['I would outline a plan and execute while keeping stakeholders informed.'];
                $answerText = $samples[array_rand($samples)];

                $answers[] = [
                    'question_id' => $question->id,
                    'answer' => $answerText,
                    'score' => null,
                ];
                continue;
            }

            $options = $question->options ?? [];
            if (!is_array($options) || empty($options)) {
                $options = [];
            }

            // Normalize options to associative [letter => text]
            $normalizedOptions = [];
            foreach ($options as $key => $value) {
                if (is_string($key) && strlen($key) === 1) {
                    $normalizedOptions[$key] = $value;
                } else {
                    $letter = chr(ord('A') + count($normalizedOptions));
                    $normalizedOptions[$letter] = $value;
                }
            }

            $correctAnswer = $question->correct_answer;

            // Randomly answer correctly based on target score
            $shouldAnswerCorrectly = (rand(1, 100) / 100) < $targetScore;

            if ($shouldAnswerCorrectly && $correctAnswer && isset($normalizedOptions[$correctAnswer])) {
                $answer = $correctAnswer;
                $score = 1;
            } else {
                $wrongOptions = array_filter(array_keys($normalizedOptions), function ($letter) use ($correctAnswer) {
                    return $letter !== $correctAnswer;
                });

                if (!empty($wrongOptions)) {
                    $answer = $wrongOptions[array_rand($wrongOptions)];
                } else {
                    $answer = array_key_first($normalizedOptions) ?? 'A';
                }

                $score = ($answer === $correctAnswer) ? 1 : 0;
            }

            $answers[] = [
                'question_id' => $question->id,
                'answer' => $answer,
                'score' => $score,
            ];
        }
 
        return $answers;
    }
}

