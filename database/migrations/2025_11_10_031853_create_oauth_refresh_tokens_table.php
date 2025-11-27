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
        // Always use the migration's connection, not the default
        $schema = Schema::connection($this->getConnection());
        
        if (!$schema->hasTable('oauth_refresh_tokens')) {
            $schema->create('oauth_refresh_tokens', function (Blueprint $table) {
                $table->char('id', 80)->primary();
                $table->char('access_token_id', 80)->index();
                $table->boolean('revoked');
                $table->dateTime('expires_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnection())
            ->dropIfExists('oauth_refresh_tokens');
    }

    /**
     * Get the migration connection name.
     */
    public function getConnection(): ?string
    {
        return $this->connection ?? config('passport.connection');
    }
};
