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
            AssessmentQuestion::create([
                'assessment_type_id' => $behavioral->id,
                'question_text' => $question[0],
                'question_type' => $question[1],
                'options' => json_encode(['1', '2', '3', '4', '5']),
                'correct_answer' => $question[3],
                'weight' => 1.0,
                'trait' => $question[4],
            ]);
        }

        // Aptitude Questions (Multiple Choice)
        $aptitudeQuestions = [
            ['What is 15% of 200?', 'multiple_choice', ['A: 15', 'B: 30', 'C: 25', 'D: 20'], 'B'],
            ['If a train travels 60 miles in 1 hour, how far will it travel in 3 hours?', 'multiple_choice', ['A: 120 miles', 'B: 180 miles', 'C: 200 miles', 'D: 240 miles'], 'B'],
            ['What is the next number in the sequence: 2, 4, 8, 16, ?', 'multiple_choice', ['A: 24', 'B: 32', 'C: 28', 'D: 30'], 'B'],
            ['If 3x + 5 = 20, what is x?', 'multiple_choice', ['A: 3', 'B: 5', 'C: 7', 'D: 10'], 'B'],
            ['A rectangle has length 8 and width 5. What is its area?', 'multiple_choice', ['A: 13', 'B: 26', 'C: 40', 'D: 45'], 'C'],
            ['What is 2^4?', 'multiple_choice', ['A: 8', 'B: 16', 'C: 32', 'D: 64'], 'B'],
            ['If 20% of a number is 40, what is the number?', 'multiple_choice', ['A: 100', 'B: 200', 'C: 300', 'D: 400'], 'B'],
            ['What is the average of 10, 20, 30, 40?', 'multiple_choice', ['A: 20', 'B: 25', 'C: 30', 'D: 35'], 'B'],
            ['If a = 5 and b = 3, what is a² + b²?', 'multiple_choice', ['A: 34', 'B: 28', 'C: 16', 'D: 15'], 'A'],
            ['What is 50% of 120?', 'multiple_choice', ['A: 50', 'B: 60', 'C: 70', 'D: 80'], 'B'],
        ];

        foreach ($aptitudeQuestions as $question) {
            AssessmentQuestion::create([
                'assessment_type_id' => $aptitude->id,
                'question_text' => $question[0],
                'question_type' => $question[1],
                'options' => json_encode($question[2]),
                'correct_answer' => $question[3],
                'weight' => 1.0,
            ]);
        }
    }
}

