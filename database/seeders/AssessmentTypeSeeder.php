<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssessmentType;

class AssessmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        AssessmentType::create([
            'name' => 'Behavioral',
            'description' => 'Behavioral assessment to evaluate candidate traits',
        ]);

        AssessmentType::create([
            'name' => 'Aptitude',
            'description' => 'Aptitude test to measure candidate skills',
        ]);
    }
}

