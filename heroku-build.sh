#!/bin/bash
# Heroku build script that prevents database access during build

# Force array cache during build to avoid database connection
export CACHE_DRIVER=array
export SESSION_DRIVER=array

# Run composer install
echo "Running composer install..."
composer install --no-dev --optimize-autoloader --no-interaction

# Generate app key if needed (should be set via config vars)
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY not set!"
fi

echo "Build completed successfully"
