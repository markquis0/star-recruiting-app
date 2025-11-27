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
        
        if (!$schema->hasTable('oauth_auth_codes')) {
            $schema->create('oauth_auth_codes', function (Blueprint $table) {
                $table->char('id', 80)->primary();
                $table->foreignId('user_id')->index();
                $table->foreignUuid('client_id');
                $table->text('scopes')->nullable();
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
