#!/bin/bash

#############################################################################
# Rollback Script - Restore Previous Version in Case of Issues
#############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

APP_DIR="/var/www/bnhs"
BACKUP_DIR="/var/backups/bnhs"

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   error "This script must be run as root"
fi

# List available backups
log "Available backups:"
ls -la "$BACKUP_DIR" | tail -n +4

read -p "Enter backup date (YYYYMMDD_HHMMSS): " BACKUP_DATE

BACKUP_PATH="$BACKUP_DIR/$BACKUP_DATE"

if [[ ! -d "$BACKUP_PATH" ]]; then
    error "Backup not found: $BACKUP_PATH"
fi

log "Rolling back to $BACKUP_DATE..."

# Stop services
log "Stopping services..."
systemctl stop laravel-queue-worker laravel-scheduler nginx

# Restore database
if [[ -f "$BACKUP_PATH/database.sql" ]]; then
    log "Restoring database..."
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_PATH/database.sql"
fi

# Restore files
if [[ -f "$BACKUP_PATH/app_files.tar.gz" ]]; then
    log "Restoring application files..."
    cd "$APP_DIR"
    tar -xzf "$BACKUP_PATH/app_files.tar.gz"
fi

# Restart services
log "Restarting services..."
systemctl start nginx laravel-queue-worker laravel-scheduler

log "Rollback completed successfully"
