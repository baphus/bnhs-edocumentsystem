# BNHS Production Deployment Guide

**Document Version:** 1.0  
**Last Updated:** January 5, 2026  
**Application:** BNHS Document Request System  
**Status:** Ready for Production Deployment

---

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Environment Configuration](#environment-configuration)
4. [Database Setup](#database-setup)
5. [Deployment Instructions](#deployment-instructions)
6. [Post-Deployment Verification](#post-deployment-verification)
7. [Monitoring & Maintenance](#monitoring--maintenance)
8. [Troubleshooting](#troubleshooting)
9. [Rollback Procedure](#rollback-procedure)
10. [Security Considerations](#security-considerations)

---

## System Requirements

### Server Specifications (Minimum)
- **OS:** Ubuntu 20.04 LTS or newer
- **CPU:** 2 vCPU (4 vCPU recommended)
- **RAM:** 4 GB minimum (8 GB recommended)
- **Storage:** 20 GB SSD

### Software Requirements
- **PHP:** 8.2 or higher
  - Extensions: pdo_mysql, bcmath, ctype, fileinfo, json, mbstring, openssl, tokenizer, xml, curl
- **MySQL/MariaDB:** 8.0+ (or PostgreSQL 12+)
- **Node.js:** 18+ LTS
- **Nginx:** Latest stable or Apache 2.4+
- **Composer:** 2.0+
- **Git:** Latest version
- **SSL Certificate:** Let's Encrypt or valid SSL

### Optional Services (Production Recommended)
- **Redis:** For caching and sessions (6.0+)
- **Elasticsearch:** For advanced search (optional)
- **Monitoring:** Datadog, New Relic, or similar
- **Email Service:** SMTP server or cloud provider (SendGrid, Mailgun)
- **Object Storage:** AWS S3 or similar for file backups

---

## Pre-Deployment Checklist

### Code Readiness
- [ ] All features tested locally
- [ ] Code reviewed and merged to main branch
- [ ] All tests passing (`npm run test` and `composer test`)
- [ ] Frontend build successful (`npm run build`)
- [ ] No console warnings or errors in production build
- [ ] All dependencies up-to-date
- [ ] Unused code and debug statements removed
- [ ] Environment variables documented

### Database Readiness
- [ ] All migrations reviewed and tested
- [ ] Production indexes created
- [ ] Database backup strategy in place
- [ ] Rollback plan documented
- [ ] Data integrity checks passing

### Security Readiness
- [ ] Security audit completed
- [ ] HTTPS/SSL configured
- [ ] CORS settings reviewed
- [ ] Rate limiting configured
- [ ] Authentication system verified
- [ ] Permission system tested
- [ ] All sensitive data encrypted
- [ ] API endpoints protected
- [ ] CSRF tokens enabled

### Infrastructure Readiness
- [ ] Server provisioned and configured
- [ ] Nginx/Apache configured
- [ ] PHP-FPM configured
- [ ] Database server ready
- [ ] Redis configured (if using)
- [ ] SSL certificates installed
- [ ] Firewall rules configured
- [ ] Backup solutions in place
- [ ] Monitoring tools configured
- [ ] Log aggregation setup

### Documentation Readiness
- [ ] Deployment guide complete
- [ ] Environment variables documented
- [ ] Admin documentation prepared
- [ ] User guide finalized
- [ ] API documentation updated
- [ ] Emergency contacts listed

---

## Environment Configuration

### Production .env File Setup

1. Copy the production environment template:
```bash
cp .env.production .env
```

2. Update all required variables:
```bash
# Core Application
APP_NAME="BNHS Document Request System"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE  # Generate with: php artisan key:generate
APP_URL=https://your-domain.com

# Database
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=bnhs_production
DB_USERNAME=db_user
DB_PASSWORD=SECURE_PASSWORD_HERE

# Cache & Session (Redis recommended)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=REDIS_PASSWORD

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=email@domain.com
MAIL_PASSWORD=SMTP_PASSWORD
MAIL_FROM_ADDRESS=noreply@your-domain.com

# Storage (AWS S3 recommended)
AWS_ACCESS_KEY_ID=YOUR_AWS_KEY
AWS_SECRET_ACCESS_KEY=YOUR_AWS_SECRET
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=bnhs-production
```

3. Secure the .env file:
```bash
chmod 600 .env
chown www-data:www-data .env
```

4. Verify all variables are set:
```bash
php artisan config:cache
php artisan config:clear  # Clear and re-cache
```

---

## Database Setup

### Create Production Database

```bash
# Connect to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE bnhs_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bnhs_user'@'localhost' IDENTIFIED BY 'SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON bnhs_production.* TO 'bnhs_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Run Migrations

```bash
cd /var/www/bnhs

# Test migrations first (dry-run)
php artisan migrate --pretend

# Run migrations
php artisan migrate --force

# Add production indexes
php artisan migrate --path=database/migrations/2026_01_05_000000_add_production_indexes.php --force
```

### Verify Database

```bash
php artisan tinker
>>> DB::table('users')->count();  // Should return number of users
>>> DB::table('document_types')->count();  // Should return document types
```

---

## Deployment Instructions

### Automated Deployment (Recommended)

Use the provided deployment script:

```bash
cd /var/www/bnhs
sudo bash deploy.sh
```

This script will:
1. Create backups
2. Pull latest code
3. Install dependencies
4. Run migrations
5. Build frontend assets
6. Optimize application
7. Restart services
8. Verify deployment

### Manual Deployment

If automated deployment fails or you prefer manual control:

#### 1. Pull Latest Code
```bash
cd /var/www/bnhs
git pull origin main
```

#### 2. Install Dependencies
```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
```

#### 3. Database Migrations
```bash
php artisan migrate --force
php artisan migrate --path=database/migrations/2026_01_05_000000_add_production_indexes.php --force
```

#### 4. Build Frontend
```bash
npm run build
```

#### 5. Production Optimization
```bash
php artisan optimize:production --clear
```

#### 6. Set File Permissions
```bash
chown -R www-data:www-data /var/www/bnhs
chmod -R 755 /var/www/bnhs
chmod -R 775 /var/www/bnhs/storage
chmod -R 775 /var/www/bnhs/bootstrap/cache
```

#### 7. Restart Services
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo systemctl restart laravel-queue-worker
sudo systemctl restart laravel-scheduler
```

---

## Post-Deployment Verification

### Application Health Checks

```bash
# Check application status
curl -I https://your-domain.com

# Test database connection
php artisan tinker
>>> DB::table('users')->count();
>>> exit;

# Check queue worker
systemctl status laravel-queue-worker

# Check logs for errors
tail -f storage/logs/laravel.log
tail -f storage/logs/security.log
tail -f storage/logs/audit.log
```

### Functional Testing

1. **User Registration/Login**
   - [ ] Email OTP verification working
   - [ ] User session created properly
   - [ ] Password reset functional

2. **Document Request Flow**
   - [ ] Document type selection works
   - [ ] Email verification required
   - [ ] Request submission successful
   - [ ] Tracking ID generated
   - [ ] Confirmation email sent

3. **Admin Dashboard**
   - [ ] Admin login working
   - [ ] Dashboard data loading
   - [ ] Request management functional
   - [ ] User management accessible

4. **API Endpoints**
   - [ ] REST endpoints responding
   - [ ] Authentication working
   - [ ] Rate limiting in effect
   - [ ] CORS properly configured

### Performance Verification

```bash
# Check PHP-FPM processes
ps aux | grep php-fpm

# Check Nginx processes
ps aux | grep nginx

# Monitor system resources
htop

# Check response times
ab -n 100 -c 10 https://your-domain.com

# Check cache hit ratio
redis-cli INFO stats
```

### Security Verification

```bash
# SSL certificate check
curl -vI https://your-domain.com

# Security headers check
curl -I https://your-domain.com | grep -E "Strict-Transport|X-Content|X-Frame"

# Test CSRF protection
# Try POST request without token - should fail

# Test rate limiting
# Try OTP endpoint multiple times - should be rate limited
```

---

## Monitoring & Maintenance

### Daily Monitoring Tasks

- [ ] Check application logs for errors
- [ ] Monitor database performance
- [ ] Verify backup completion
- [ ] Check server resource usage
- [ ] Monitor queue worker status
- [ ] Review security logs

### Weekly Maintenance

```bash
# Update dependencies (after testing)
composer update
npm update

# Database maintenance
php artisan tinker
>>> DB::table('failed_jobs')->count();  // Check failed jobs

# Optimize database
mysql> OPTIMIZE TABLE document_requests, users, otps;

# Review and archive old logs
find storage/logs -name "*.log" -mtime +30 -delete
```

### Monthly Tasks

- [ ] Full system backup
- [ ] Database consistency checks
- [ ] Security audit
- [ ] Performance review
- [ ] Update dependencies
- [ ] Test disaster recovery

### Log Monitoring

Monitor these logs regularly:

```bash
# Application logs
tail -f storage/logs/laravel.log

# Security/Audit logs
tail -f storage/logs/security.log
tail -f storage/logs/audit.log

# Email logs
tail -f storage/logs/email.log

# Web server logs
tail -f /var/log/nginx/bnhs_access.log
tail -f /var/log/nginx/bnhs_error.log
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. 500 Internal Server Error

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear

# Generate missing app key
php artisan key:generate

# Verify file permissions
ls -la bootstrap/cache
ls -la storage
```

#### 2. Database Connection Failed

```bash
# Test connection
mysql -u bnhs_user -p bnhs_production

# Check .env settings
cat .env | grep DB_

# Verify MySQL is running
systemctl status mysql

# Check credentials
mysql -u root -p
SHOW GRANTS FOR 'bnhs_user'@'localhost';
```

#### 3. Queue Worker Issues

```bash
# Check queue worker status
systemctl status laravel-queue-worker

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Monitor queue
watch 'php artisan queue:work --tries=1 --timeout=60'
```

#### 4. High Memory Usage

```bash
# Check memory usage
free -h

# Identify memory hogs
ps aux --sort=-%mem | head

# Clear caches
php artisan cache:clear
php artisan view:clear

# Optimize database
mysql> OPTIMIZE TABLE document_requests;
```

#### 5. Slow Response Times

```bash
# Enable query logging (development only!)
# Check slow query log
tail -f /var/log/mysql/slow-queries.log

# Analyze database indexes
php artisan tinker
>>> \DB::enableQueryLog();
>>> // Run your queries
>>> dd(\DB::getQueryLog());

# Check Redis cache
redis-cli
> INFO stats
```

---

## Rollback Procedure

If deployment fails or issues are discovered:

### Automated Rollback

```bash
cd /var/www/bnhs
sudo bash rollback.sh

# Follow prompts to select backup to restore
```

### Manual Rollback

1. **Stop services**
```bash
sudo systemctl stop laravel-queue-worker laravel-scheduler nginx
```

2. **Restore previous version**
```bash
cd /var/www/bnhs
git reset --hard HEAD~1  # Go back one commit
git pull
```

3. **Restore database** (from backup)
```bash
mysql -u root -p bnhs_production < /var/backups/bnhs/BACKUP_DATE/database.sql
```

4. **Restart services**
```bash
sudo systemctl start nginx laravel-queue-worker laravel-scheduler
```

5. **Verify rollback**
```bash
php artisan tinker
>>> DB::table('users')->count();
```

---

## Security Considerations

### Environment Variable Security
- [ ] Never commit `.env` file to version control
- [ ] Use strong, randomly generated passwords
- [ ] Rotate credentials regularly
- [ ] Store secrets in secure vault (AWS Secrets Manager, HashiCorp Vault)

### Database Security
- [ ] Enable database encryption at rest
- [ ] Use SSL for database connections
- [ ] Implement regular backups with encryption
- [ ] Restrict database access to application server only
- [ ] Use prepared statements (already done with Eloquent)

### Application Security
- [ ] Enable HTTPS/TLS (SSL certificate installed)
- [ ] Security headers configured (HSTS, CSP, etc.)
- [ ] CORS properly restricted
- [ ] Rate limiting enabled
- [ ] Input validation enforced
- [ ] SQL injection protection (Eloquent)
- [ ] XSS protection enabled
- [ ] CSRF tokens required

### File Upload Security
- [ ] Store uploads outside webroot
- [ ] Validate file types
- [ ] Scan files for malware
- [ ] Implement file size limits
- [ ] Use AWS S3 or similar for storage

### Access Control
- [ ] Restrict admin access by IP whitelist
- [ ] Implement 2FA for admin accounts
- [ ] Regular audit of user permissions
- [ ] Monitor failed login attempts
- [ ] Implement account lockout after failed attempts

### Backup & Disaster Recovery
- [ ] Daily automated backups
- [ ] Test restore procedures monthly
- [ ] Geographically distributed backups
- [ ] Encrypted backup storage
- [ ] Clear backup retention policy

### Monitoring & Alerting
- [ ] Real-time error monitoring
- [ ] Security event logging
- [ ] Performance monitoring
- [ ] Uptime monitoring
- [ ] Alert on suspicious activity

---

## Support & Contact

### Emergency Contacts
- **Primary Admin:** admin@your-domain.com
- **Technical Lead:** tech@your-domain.com
- **DevOps Team:** devops@your-domain.com

### Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

### Incident Response
1. Document the incident
2. Stop the bleeding (disable affected feature if needed)
3. Investigate the root cause
4. Fix the issue
5. Deploy fix
6. Verify resolution
7. Post-mortem and lessons learned

---

**End of Document**

For questions or updates, contact the DevOps team.
