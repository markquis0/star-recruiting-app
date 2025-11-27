<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The oauth_auth_codes table already exists in production.
        // We intentionally do nothing here to avoid duplicate-table errors.
        //
        // If you ever need to change the structure of oauth_auth_codes,
        // create a separate migration that uses Schema::table(...) instead
        // of trying to recreate the table.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnection())
            ->dropIfExists('oauth_auth_codes');
    }

    /**
     * Get the migration connection name.
     */
    public function getConnection(): ?string
    {
        return $this->connection ?? config('passport.connection');
    }
};
