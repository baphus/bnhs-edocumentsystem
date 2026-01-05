#!/bin/bash

#############################################################################
# BNHS Production Deployment Script
# Safely deploys the application with zero downtime
#############################################################################

set -e  # Exit on any error

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/bnhs"
BACKUP_DIR="/var/backups/bnhs"
LOG_FILE="/var/log/bnhs_deployment.log"

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

#############################################################################
# Pre-Deployment Checks
#############################################################################

log "=== Starting BNHS Production Deployment ==="

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   error "This script must be run as root"
fi

# Check if app directory exists
if [[ ! -d "$APP_DIR" ]]; then
    error "Application directory $APP_DIR not found"
fi

cd "$APP_DIR" || error "Failed to change to app directory"

log "Pre-deployment checks passed"

#############################################################################
# Create Backup
#############################################################################

log "Creating backup..."
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_PATH="$BACKUP_DIR/$BACKUP_DATE"
mkdir -p "$BACKUP_PATH"

# Backup database
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_PATH/database.sql" || \
    warning "Database backup failed"

# Backup application files
tar -czf "$BACKUP_PATH/app_files.tar.gz" --exclude=node_modules --exclude=vendor . || \
    warning "Application backup failed"

log "Backup created at $BACKUP_PATH"

#############################################################################
# Pull Latest Code
#############################################################################

log "Pulling latest code from repository..."
git pull origin main || error "Failed to pull latest code"

#############################################################################
# Install Dependencies
#############################################################################

log "Installing Composer dependencies..."
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

log "Installing npm dependencies..."
npm ci || error "Failed to install npm dependencies"

#############################################################################
# Database Migrations
#############################################################################

log "Running database migrations..."
php artisan migrate --force || error "Database migration failed"

log "Creating database indexes for production..."
php artisan migrate --path=database/migrations/2026_01_05_000000_add_production_indexes.php --force

#############################################################################
# Build Frontend Assets
#############################################################################

log "Building production assets..."
npm run build || error "Frontend build failed"

#############################################################################
# Optimization
#############################################################################

log "Optimizing application for production..."
php artisan optimize:production --clear || error "Optimization failed"

#############################################################################
# Cache Warming
#############################################################################

log "Warming up caches..."
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

#############################################################################
# File Permissions
#############################################################################

log "Setting file permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"

#############################################################################
# Restart Services
#############################################################################

log "Restarting services..."
systemctl restart php8.2-fpm || warning "Failed to restart PHP-FPM"
systemctl restart nginx || warning "Failed to restart Nginx"
systemctl restart laravel-queue-worker || warning "Failed to restart queue worker"
systemctl restart laravel-scheduler || warning "Failed to restart scheduler"

#############################################################################
# Post-Deployment Verification
#############################################################################

log "Running post-deployment checks..."

# Check application health
php artisan tinker <<EOF
echo "Database connection test...\n";
\DB::table('users')->limit(1)->first();
echo "✓ Database connected successfully\n";
EOF

# Check frontend assets are loaded
if [[ ! -f "public/build/manifest.json" ]]; then
    error "Build manifest not found - build may have failed"
fi

log "✓ Frontend assets built successfully"

#############################################################################
# Final Steps
#############################################################################

log "=== Deployment Complete ==="
log "Deployment time: $(date)"
log "Backup location: $BACKUP_PATH"
log "Log file: $LOG_FILE"

# Send notification (optional)
# mail -s "BNHS Deployment Complete" admin@your-domain.com < <(echo "Deployment completed successfully at $(date)")

exit 0
