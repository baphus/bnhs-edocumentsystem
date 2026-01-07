# BNHS Production Operations & Administration Guide

**Document Version:** 1.0  
**Last Updated:** January 5, 2026  
**Target Audience:** System Administrators, DevOps Engineers, IT Operations

---

## Table of Contents

1. [System Administration](#system-administration)
2. [User & Account Management](#user--account-management)
3. [Monitoring & Health Checks](#monitoring--health-checks)
4. [Database Management](#database-management)
5. [Backup & Recovery](#backup--recovery)
6. [Performance Tuning](#performance-tuning)
7. [Incident Response](#incident-response)
8. [Maintenance Windows](#maintenance-windows)
9. [Troubleshooting Guide](#troubleshooting-guide)

---

## System Administration

### Server Access

**SSH Access:**
```bash
# Connect to production server
ssh -i /path/to/key admin@your-domain.com

# Key locations (secure these)
~/.ssh/id_rsa          # Private key (chmod 600)
~/.ssh/authorized_keys # Public keys (chmod 644)
```

### User Management

```bash
# Create new system user
sudo useradd -m -s /bin/bash -G www-data username

# Add to sudoers
sudo usermod -aG sudo username

# Remove user
sudo deluser --remove-home username

# View all users
cat /etc/passwd

# View active sessions
who
w
```

### Service Management

```bash
# View all services
sudo systemctl list-units --type=service

# Start service
sudo systemctl start service-name

# Stop service
sudo systemctl stop service-name

# Restart service
sudo systemctl restart service-name

# Enable on boot
sudo systemctl enable service-name

# Check status
sudo systemctl status service-name

# View logs
sudo journalctl -u service-name -f  # Follow logs
sudo journalctl -u service-name --since "2 hours ago"
```

### Critical Services

```bash
# Web Server
sudo systemctl status nginx
sudo systemctl restart nginx

# PHP-FPM
sudo systemctl status php8.2-fpm
sudo systemctl restart php8.2-fpm

# Database
sudo systemctl status mysql
sudo systemctl restart mysql

# Cache
sudo systemctl status redis-server
sudo systemctl restart redis-server

# Queue Worker
sudo systemctl status laravel-queue-worker
sudo systemctl restart laravel-queue-worker

# Task Scheduler
sudo systemctl status laravel-scheduler
sudo systemctl restart laravel-scheduler
```

---

## User & Account Management

### Admin User Management

```bash
# Access admin panel
https://your-domain.com/admin

# Create admin account (via Tinker)
php artisan tinker

>>> $user = new App\Models\User();
>>> $user->name = 'Admin Name';
>>> $user->email = 'admin@your-domain.com';
>>> $user->password = Hash::make('SECURE_PASSWORD');
>>> $user->role = 'admin';
>>> $user->save();

# Grant superadmin privileges
>>> $user->update(['role' => 'superadmin']);

# Exit Tinker
>>> exit
```

### Reset User Password

```bash
php artisan tinker

>>> $user = User::where('email', 'user@example.com')->first();
>>> $user->update(['password' => Hash::make('NewPassword123!')]);
>>> echo "Password reset for: " . $user->email;
```

### View User Activity

```bash
php artisan tinker

>>> # Get user's last login
>>> $user = User::find(1);
>>> $user->last_login;

>>> # Get user's requests
>>> $user->documentRequests;

>>> # Get user's activity log
>>> RequestLog::where('user_id', $user->id)->get();
```

### Disable User Account

```bash
php artisan tinker

>>> $user = User::find($userId);
>>> $user->update(['email_verified_at' => null]);  # Disable login
>>> echo "User disabled";

# Or soft-delete
>>> $user->delete();
```

---

## Monitoring & Health Checks

### System Health Check

```bash
#!/bin/bash
# Run this periodically to check system health

echo "=== System Health Check ==="

# Check disk space
echo -e "\n1. Disk Space:"
df -h /

# Check memory
echo -e "\n2. Memory Usage:"
free -h

# Check processes
echo -e "\n3. Key Processes:"
ps aux | grep -E "nginx|php-fpm|mysql|redis"

# Check services
echo -e "\n4. Service Status:"
sudo systemctl status nginx php8.2-fpm mysql redis-server

# Check database
echo -e "\n5. Database Connection:"
mysql -u bnhs_user -p$DB_PASSWORD -e "SELECT COUNT(*) as user_count FROM users;"

# Check queue
echo -e "\n6. Failed Jobs:"
php artisan queue:failed

# Check logs for errors
echo -e "\n7. Recent Errors:"
tail -20 storage/logs/laravel.log | grep ERROR
```

### Monitoring Checklist

Daily:
- [ ] Server disk space (> 20% free)
- [ ] Database connection
- [ ] Email queue status
- [ ] Error logs (no critical errors)
- [ ] Application response time

Weekly:
- [ ] Database backups completed
- [ ] Backup integrity verified
- [ ] Failed job queue
- [ ] Security logs for suspicious activity
- [ ] Performance metrics trend

Monthly:
- [ ] Full system audit
- [ ] Security patch updates
- [ ] Database optimization
- [ ] Capacity planning
- [ ] Cost analysis

### Redis Monitoring

```bash
# Check Redis status
redis-cli ping
# Should return: PONG

# View stats
redis-cli INFO stats

# View memory usage
redis-cli INFO memory

# Flush cache (development only!)
redis-cli FLUSHDB

# Monitor commands
redis-cli MONITOR

# View all keys
redis-cli KEYS '*'

# Check specific key
redis-cli GET key_name

# Set TTL on key
redis-cli EXPIRE key_name 3600
```

### Application Health Endpoint

```bash
# Check application status
curl https://your-domain.com/up

# Should return HTTP 200 OK

# Check database connection
curl https://your-domain.com/api/health
```

---

## Database Management

### MySQL Backup

```bash
# Manual backup
mysqldump -u bnhs_user -p bnhs_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Compress backup
mysqldump -u bnhs_user -p bnhs_production | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz

# Full backup with all databases
mysqldump -u root -p --all-databases > full_backup.sql
```

### MySQL Restore

```bash
# Restore from backup
mysql -u bnhs_user -p bnhs_production < backup_file.sql

# Restore compressed backup
gunzip < backup_file.sql.gz | mysql -u bnhs_user -p bnhs_production
```

### Database Maintenance

```bash
# Check database size
mysql -e "SELECT table_schema 'Database', SUM(data_length + index_length) 'Size' FROM information_schema.TABLES GROUP BY table_schema;"

# Optimize tables
OPTIMIZE TABLE document_requests;
OPTIMIZE TABLE users;
OPTIMIZE TABLE otps;

# Analyze tables
ANALYZE TABLE document_requests;
ANALYZE TABLE users;

# Check table integrity
CHECK TABLE document_requests;

# Repair table (if needed)
REPAIR TABLE document_requests;
```

### Database Queries

```bash
# View active connections
SHOW PROCESSLIST;

# Kill long-running query
KILL QUERY process_id;

# View slow queries
SHOW VARIABLES LIKE 'long_query_time';
SET long_query_time = 1;

# View log file
tail -f /var/log/mysql/slow-queries.log
```

---

## Backup & Recovery

### Automated Backup Status

```bash
# Check if backups are running
ls -lah /var/backups/bnhs/

# Verify recent backup
ls -lh /var/backups/bnhs/$(date +%Y%m%d)*/

# Test restore process
cd /tmp
tar -xzf /var/backups/bnhs/BACKUP_DATE/app_files.tar.gz
mysql -u root -p bnhs_test < /var/backups/bnhs/BACKUP_DATE/database.sql
```

### Create Backup on Demand

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/bnhs"
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_PATH="$BACKUP_DIR/$BACKUP_DATE"

mkdir -p "$BACKUP_PATH"

# Backup database
mysqldump -u bnhs_user -p "$DB_PASSWORD" bnhs_production > "$BACKUP_PATH/database.sql"

# Backup application
tar -czf "$BACKUP_PATH/app_files.tar.gz" /var/www/bnhs \
  --exclude=node_modules --exclude=vendor --exclude=.git

echo "Backup created at $BACKUP_PATH"
```

### Recovery Procedure

```bash
# See DEPLOYMENT_GUIDE.md for full rollback procedure

# Quick recovery
cd /var/www/bnhs

# Stop services
sudo systemctl stop laravel-queue-worker laravel-scheduler nginx

# Restore files
tar -xzf /var/backups/bnhs/BACKUP_DATE/app_files.tar.gz

# Restore database
mysql -u bnhs_user -p bnhs_production < /var/backups/bnhs/BACKUP_DATE/database.sql

# Restart services
sudo systemctl start nginx laravel-queue-worker laravel-scheduler
```

---

## Performance Tuning

### PHP Optimization

```bash
# Edit PHP configuration
sudo nano /etc/php/8.2/fpm/php.ini

# Key settings:
memory_limit = 256M        # Increase if needed
max_execution_time = 300
max_input_time = 300
post_max_size = 10M
upload_max_filesize = 10M

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### MySQL Optimization

```bash
# Edit MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Key settings for production:
max_connections = 200
innodb_buffer_pool_size = 1G    # 50-80% of RAM
innodb_log_file_size = 256M
query_cache_size = 0            # Disable in MySQL 8.0+
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-queries.log
long_query_time = 2

# Restart MySQL
sudo systemctl restart mysql

# Verify settings
mysql -e "SHOW VARIABLES LIKE '%buffer%';"
```

### Nginx Optimization

```nginx
# Edit Nginx configuration
sudo nano /etc/nginx/sites-available/bnhs.conf

# Key settings:
worker_processes auto;      # Use all CPU cores
worker_connections 2048;    # Increase connection limit
keepalive_timeout 65;
client_max_body_size 10M;

# Gzip compression
gzip on;
gzip_vary on;
gzip_comp_level 6;
gzip_types text/plain text/css application/json application/javascript;

# Test and reload
sudo nginx -t
sudo systemctl reload nginx
```

### Redis Optimization

```bash
# Edit Redis configuration
sudo nano /etc/redis/redis.conf

# Key settings:
maxmemory 512mb
maxmemory-policy allkeys-lru
tcp-keepalive 300
timeout 300

# Restart Redis
sudo systemctl restart redis-server

# Monitor performance
redis-cli --latency
redis-cli --latency-history
redis-cli LATENCY LATEST
```

---

## Incident Response

### Critical Error Response

```bash
#!/bin/bash
# When application throws 500 errors

echo "1. Checking error logs..."
tail -50 /var/www/bnhs/storage/logs/laravel.log

echo "2. Checking system resources..."
free -h
df -h /

echo "3. Checking database connection..."
php artisan tinker <<EOF
DB::connection()->select('SELECT 1');
echo "Database OK\n";
exit;
EOF

echo "4. Restarting PHP-FPM..."
sudo systemctl restart php8.2-fpm

echo "5. Clearing application cache..."
cd /var/www/bnhs
php artisan cache:clear
php artisan config:clear

echo "6. Checking queue worker..."
sudo systemctl status laravel-queue-worker
```

### Memory Leak Investigation

```bash
# Check memory usage
php -m | grep -i xdebug  # Remove if present in production

# Monitor memory growth
watch -n 1 'free -h'

# Check process memory
ps aux --sort=-%mem | head -20

# If PHP-FPM memory grows unbounded:
sudo systemctl restart php8.2-fpm
```

### Database Lock Troubleshooting

```bash
# Check active processes
mysql -e "SHOW PROCESSLIST;"

# Kill long-running query
mysql -e "KILL QUERY 1234;"

# Check innodb locks
mysql -e "SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;"

# Check table locks
mysql -e "SHOW OPEN TABLES WHERE In_use > 0;"
```

---

## Maintenance Windows

### Planned Maintenance Procedure

1. **Announce Maintenance** (24 hours in advance)
```bash
# Add maintenance notification to application
# Or use maintenance mode:
php artisan down --render="errors::503" --secret=maintenance-token

# Access during maintenance
curl -H "X-LARAVEL-MAINTENANCE-SECRET: maintenance-token" https://your-domain.com
```

2. **Backup Data**
```bash
cd /var/www/bnhs
bash deploy.sh  # Creates backup automatically
```

3. **Perform Maintenance**
- Database optimization/repair
- System updates
- Security patches
- Configuration changes

4. **Bring Application Back Online**
```bash
php artisan up
```

### System Updates

```bash
# Update system packages
sudo apt update
sudo apt upgrade

# Update PHP
sudo apt install php8.2-{common,cli,fpm,xml,mysql,curl}

# Update Node/npm
sudo npm install -g npm@latest

# Update Composer
composer self-update

# Restart services
sudo systemctl restart nginx php8.2-fpm

# Test application
curl https://your-domain.com
```

---

## Troubleshooting Guide

### Application Won't Start

```bash
# Check PHP syntax
php -l /var/www/bnhs/artisan

# Check Laravel key
php artisan key:generate

# Clear caches
php artisan cache:clear
php artisan config:clear

# Check database connection
php artisan tinker
```

### Database Connection Error

```bash
# Test connection manually
mysql -h 127.0.0.1 -u bnhs_user -p bnhs_production

# Check credentials in .env
cat /var/www/bnhs/.env | grep DB_

# Check MySQL service
sudo systemctl restart mysql

# Check firewall
sudo ufw allow 3306
```

### Queue Worker Not Processing Jobs

```bash
# Check worker status
sudo systemctl status laravel-queue-worker

# View failed jobs
php artisan queue:failed

# Restart worker
sudo systemctl restart laravel-queue-worker

# Monitor in real-time
php artisan queue:work --tries=3 --timeout=300 -v

# Clear failed jobs
php artisan queue:flush
```

### Nginx Errors (502, 504)

```bash
# Check Nginx syntax
sudo nginx -t

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# View error log
tail -f /var/log/nginx/error.log

# Increase timeouts
# In nginx.conf:
fastcgi_connect_timeout 60s;
fastcgi_send_timeout 60s;
fastcgi_read_timeout 60s;
```

### High CPU Usage

```bash
# Identify culprit
top
htop

# Check Apache bench
ab -n 1000 -c 10 https://your-domain.com

# View slow queries
tail -f /var/log/mysql/slow-queries.log

# Optimize database
php artisan tinker
# Run optimization commands
```

### Disk Space Issues

```bash
# Check disk usage
du -sh /var/www/bnhs/*
du -sh /var/log/*
du -sh /var/backups/*

# Clean old logs
find /var/www/bnhs/storage/logs -mtime +30 -delete

# Compress old backups
find /var/backups -name "*.sql" -mtime +7 -exec gzip {} \;

# Clear PHP sessions
find /var/lib/php/sessions -mtime +30 -delete

# Check what's consuming space
ncdu /var/www/bnhs
ncdu /var/log
```

---

## Emergency Contacts

| Role | Name | Email | Phone |
|------|------|-------|-------|
| System Admin | | | |
| DevOps Lead | | | |
| Database Admin | | | |
| Security Team | | | |
| Management | | | |

---

## Quick Reference Commands

```bash
# Application Status
sudo systemctl status nginx php8.2-fpm mysql redis-server
systemctl status laravel-queue-worker laravel-scheduler

# View Logs
tail -f /var/www/bnhs/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Database
mysql -u bnhs_user -p bnhs_production
mysqldump -u bnhs_user -p bnhs_production > backup.sql

# Cache
redis-cli PING
redis-cli FLUSHDB

# Artisan Commands
php artisan tinker
php artisan migrate
php artisan queue:failed
php artisan optimize:production

# System Info
php -v
node -v
composer -V
```

---

**End of Operations Guide**

For critical issues, contact the emergency support team immediately.
