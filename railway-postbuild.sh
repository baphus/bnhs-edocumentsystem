#!/bin/bash

echo "========================================"
echo "Railway Post-Build Script"
echo "========================================"

# Set permissions for Laravel directories
echo "Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Create necessary directories
echo "Creating storage directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Build frontend assets (should already be built, but verify)
if [ ! -d "public/build" ]; then
    echo "Building frontend assets..."
    npm run build
fi

# Cache Laravel configuration
echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
echo "Optimizing Composer autoloader..."
composer dump-autoload --optimize

echo "========================================"
echo "Post-build complete!"
echo "========================================"
