#!/bin/bash

echo "-----> Starting Railway deployment"

# Set proper permissions
chmod -R 755 bootstrap/cache
chmod -R 755 storage

# Create storage directories if they don't exist
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    echo "-----> Creating storage symlink"
    php artisan storage:link
fi

# Run migrations first to ensure database is ready
echo "-----> Running database migrations"
php artisan migrate --force --no-interaction

# Now clear and cache config (after database is available)
echo "-----> Clearing and caching configuration"
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Seed default settings if needed (only on first deploy)
php artisan db:seed --class=SettingsSeeder --force

# Start the queue worker in background
echo "-----> Starting queue worker"
php artisan queue:work --daemon --tries=3 --timeout=90 &

# Start the web server
echo "-----> Starting web server on port ${PORT:-8080}"
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
