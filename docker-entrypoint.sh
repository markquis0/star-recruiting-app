#!/bin/bash
set -e

echo "Starting Laravel application..."

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

