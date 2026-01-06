#!/bin/bash

echo "-----> Running post-build tasks"

# Build frontend assets
echo "-----> Building frontend assets"
npm run build

# Clear and cache config
echo "-----> Caching configuration"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "-----> Running database migrations"
php artisan migrate --force

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    echo "-----> Creating storage symlink"
    php artisan storage:link
fi

echo "-----> Post-build complete"
