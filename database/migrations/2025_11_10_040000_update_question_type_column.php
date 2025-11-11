<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_questions')) {
            DB::statement("ALTER TABLE assessment_questions MODIFY question_type VARCHAR(50) NOT NULL DEFAULT 'multiple_choice'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_questions')) {
            DB::statement("ALTER TABLE assessment_questions MODIFY question_type ENUM('multiple_choice','rating_scale') NOT NULL");
        }
    }
};
