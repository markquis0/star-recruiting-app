#!/usr/bin/env bash
set -o errexit
set -o nounset
set -o pipefail

apt-get update
apt-get install -y php-cli php-fpm php-mbstring php-xml php-curl php-zip php-mysql mysql-client unzip

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
npm ci
npm run build
php artisan passport:keys --force
php artisan config:cache
php artisan route:cache
php artisan storage:link
