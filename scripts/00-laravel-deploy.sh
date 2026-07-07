#!/usr/bin/env bash
echo "Running composer install..."
composer install --no-dev --working-dir=/var/www/html --optimize-autoloader

echo "Caching config..."
php artisan config:cache
php artisan route:cache

echo "Running migrations..."
php artisan migrate --force
