<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_questions')) {
            // PostgreSQL syntax: ALTER COLUMN instead of MODIFY
            DB::statement("ALTER TABLE assessment_questions 
                ALTER COLUMN question_type TYPE VARCHAR(50),
                ALTER COLUMN question_type SET NOT NULL,
                ALTER COLUMN question_type SET DEFAULT 'multiple_choice'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_questions')) {
            // PostgreSQL doesn't have ENUM like MySQL, so revert to VARCHAR
            DB::statement("ALTER TABLE assessment_questions 
                ALTER COLUMN question_type DROP DEFAULT,
                ALTER COLUMN question_type TYPE VARCHAR(50),
                ALTER COLUMN question_type SET NOT NULL");
        }
    }
};
