#!/bin/bash
set +e  # Don't exit on errors, we handle them explicitly

echo "Starting Laravel application..."

# Clear any existing caches first (may contain references to dev dependencies)
php artisan config:clear || true
php artisan route:clear || true
php artisan cache:clear || true

# Generate app key if not set (only if APP_KEY is empty)
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not set, generating new key..."
    php artisan key:generate --force || echo "Failed to generate APP_KEY"
else
    echo "APP_KEY is set from environment"
fi

# Ensure storage directories exist and have proper permissions
echo "Setting up storage directories..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache || true

# Test database connection
echo "Testing database connection..."
php artisan db:show || echo "Database connection test failed, but continuing..."

# For Option A: we use ONLY vendor Passport migrations.
# Remove app-level Passport migrations (except oauth_personal_access_clients which we need).
echo "Removing duplicate app-level Passport migrations..."
find database/migrations -name "*_create_oauth_*_table.php" \
    ! -name "*_create_oauth_personal_access_clients_table.php" \
    -delete || true

# Run migrations (includes Passport vendor migrations automatically)
echo "Running migrations..."
php artisan migrate --force || echo "Migration failed, continuing..."

# Install Passport (creates oauth_personal_access_clients table and initial clients)
# This is safe to run multiple times - it won't recreate existing tables/clients
echo "Installing Passport (creates missing tables and clients)..."
php artisan passport:install --force || echo "Passport install failed, continuing..."

# Generate Passport keys if they don't exist (passport:install may have created them)
if [ ! -f storage/oauth-private.key ]; then
    echo "Generating Passport keys..."
    php artisan passport:keys --force || echo "Passport keys generation failed, continuing..."
fi

# Cache configuration (only if no critical errors)
echo "Caching configuration..."
php artisan config:cache 2>&1 || {
    echo "Config cache failed, clearing and retrying..."
    php artisan config:clear
    php artisan config:cache 2>&1 || echo "Config cache still failed, running without cache"
}

php artisan route:cache 2>&1 || {
    echo "Route cache failed, clearing and retrying..."
    php artisan route:clear
    php artisan route:cache 2>&1 || echo "Route cache still failed, running without cache"
}

# Create storage link
php artisan storage:link || true

# Start the server
echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

