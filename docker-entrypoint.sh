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

# Run migrations
echo "Running migrations..."
php artisan migrate --force || echo "Migration failed, continuing..."

# Generate Passport keys if they don't exist
if [ ! -f storage/oauth-private.key ]; then
    echo "Generating Passport keys..."
    php artisan passport:keys --force || echo "Passport keys generation failed, continuing..."
fi

# Ensure Passport OAuth clients exist (required for createToken)
echo "Checking Passport OAuth clients..."
php -r "
require __DIR__ . '/vendor/autoload.php';
\$app = require_once __DIR__ . '/bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

try {
    if (!DB::table('oauth_clients')->where('name', 'Laravel Personal Access Client')->exists()) {
        echo 'Creating Personal Access Client...' . PHP_EOL;
        \$clientId = (string) Str::uuid();
        \$clientSecret = Str::random(40);
        DB::table('oauth_clients')->insert([
            'id' => \$clientId,
            'name' => 'Laravel Personal Access Client',
            'secret' => \$clientSecret,
            'redirect' => 'http://localhost',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => \$clientId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo 'Personal Access Client created successfully.' . PHP_EOL;
    } else {
        echo 'Personal Access Client already exists.' . PHP_EOL;
    }
} catch (Exception \$e) {
    echo 'Error checking/creating Passport clients: ' . \$e->getMessage() . PHP_EOL;
}
" || echo "Failed to check/create Passport clients, continuing..."

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

