<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('assessment_questions')) {
            // Drop the old check constraint
            DB::statement("
                ALTER TABLE assessment_questions
                DROP CONSTRAINT IF EXISTS assessment_questions_question_type_check;
            ");

            // Add a new, broader constraint that includes 'open_ended'
            DB::statement("
                ALTER TABLE assessment_questions
                ADD CONSTRAINT assessment_questions_question_type_check
                CHECK (question_type IN ('multiple_choice', 'rating_scale', 'open_ended'));
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assessment_questions')) {
            // Drop the constraint (restore to no constraint or you can recreate the old one)
            DB::statement("
                ALTER TABLE assessment_questions
                DROP CONSTRAINT IF EXISTS assessment_questions_question_type_check;
            ");
        }
    }
};
