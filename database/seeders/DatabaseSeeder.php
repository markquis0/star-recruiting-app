<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AssessmentTypeSeeder::class,
            AssessmentQuestionSeeder::class,
            CandidateDataSeeder::class,
        ]);
    }
}

