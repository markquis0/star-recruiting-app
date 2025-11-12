#!/bin/bash
set +e  # Don't exit on errors, we handle them explicitly

echo "Starting Laravel application..."

# Clear any existing caches first (may contain references to dev dependencies)
php artisan config:clear || true
php artisan route:clear || true
php artisan cache:clear || true

# Generate app key if not set
php artisan key:generate --force || true

# Run migrations
php artisan migrate --force || echo "Migration failed, continuing..."

# Generate Passport keys if they don't exist
if [ ! -f storage/oauth-private.key ]; then
    php artisan passport:keys --force || echo "Passport keys generation failed, continuing..."
fi

# Cache configuration
php artisan config:cache || echo "Config cache failed, continuing..."
php artisan route:cache || echo "Route cache failed, continuing..."

# Create storage link
php artisan storage:link || true

# Start the server
echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

