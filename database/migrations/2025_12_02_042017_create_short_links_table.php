<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_links', function (Blueprint $table) {
            $table->id();
            $table->string('short_code', 8)->unique();
            $table->string('full_token', 64);
            $table->foreignId('candidate_id')
                ->constrained()
                ->onDelete('cascade');
            $table->timestamps();

            $table->index('short_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_links');
    }
};
