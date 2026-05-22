#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Running seeders..."
php artisan db:seed --force || echo "Seeder skipped (data may already exist)"

echo "Clearing cache..."
php artisan config:clear
php artisan cache:clear

echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
