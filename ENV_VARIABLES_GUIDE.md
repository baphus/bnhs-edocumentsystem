# BNHS Environment Variables Reference Guide

**Document Version:** 1.0  
**Last Updated:** January 5, 2026  
**Purpose:** Complete reference for all environment variables

---

## Application Configuration

### APP_NAME
- **Purpose:** Display name of the application
- **Type:** String
- **Example:** `BNHS Document Request System`
- **Required:** Yes

### APP_ENV
- **Purpose:** Application environment
- **Type:** Enum: `local`, `production`
- **Example:** `production`
- **Required:** Yes
- **Note:** Controls debug mode and logging level

### APP_DEBUG
- **Purpose:** Enable debug mode
- **Type:** Boolean (false/true)
- **Example:** `false`
- **Required:** Yes
- **Warning:** Must be `false` in production

### APP_KEY
- **Purpose:** Encryption key for Laravel
- **Type:** Base64 encoded string
- **Example:** `base64:GENERATED_KEY_HERE`
- **Required:** Yes
- **How to Generate:** `php artisan key:generate`

### APP_URL
- **Purpose:** Application base URL
- **Type:** URL
- **Example:** `https://your-domain.com`
- **Required:** Yes
- **Note:** Must use HTTPS in production

### APP_TIMEZONE
- **Purpose:** Application timezone
- **Type:** String (PHP timezone)
- **Example:** `UTC`
- **Required:** No
- **Default:** `UTC`

### APP_LOCALE
- **Purpose:** Application locale
- **Type:** String (language code)
- **Example:** `en`
- **Required:** No

---

## Database Configuration

### DB_CONNECTION
- **Purpose:** Database driver
- **Type:** Enum: `mysql`, `pgsql`, `sqlite`
- **Example:** `mysql`
- **Required:** Yes
- **Default:** `sqlite`

### DB_HOST
- **Purpose:** Database server hostname
- **Type:** String
- **Example:** `localhost` or `db.example.com`
- **Required:** Yes (unless using SQLite)

### DB_PORT
- **Purpose:** Database server port
- **Type:** Integer
- **Example:** `3306`
- **Required:** No
- **Default:** `3306` (MySQL), `5432` (PostgreSQL)

### DB_DATABASE
- **Purpose:** Database name
- **Type:** String
- **Example:** `bnhs_production`
- **Required:** Yes

### DB_USERNAME
- **Purpose:** Database username
- **Type:** String
- **Example:** `bnhs_user`
- **Required:** Yes

### DB_PASSWORD
- **Purpose:** Database password
- **Type:** String
- **Example:** `SecurePassword123!`
- **Required:** Yes
- **Security:** Store in secure vault, never commit

### DB_CHARSET
- **Purpose:** Database character set
- **Type:** String
- **Example:** `utf8mb4`
- **Required:** No
- **Default:** `utf8mb4`

### DB_COLLATION
- **Purpose:** Database collation
- **Type:** String
- **Example:** `utf8mb4_unicode_ci`
- **Required:** No
- **Default:** `utf8mb4_unicode_ci`

### DB_FOREIGN_KEYS
- **Purpose:** Enable foreign key constraints
- **Type:** Boolean
- **Example:** `true`
- **Required:** No
- **Default:** `true`

---

## Cache & Session Configuration

### CACHE_DRIVER
- **Purpose:** Cache driver
- **Type:** Enum: `file`, `redis`, `memcached`, `database`
- **Example:** `redis`
- **Required:** No
- **Default:** `file`
- **Production Recommended:** `redis`

### CACHE_TTL
- **Purpose:** Cache time-to-live in seconds
- **Type:** Integer
- **Example:** `3600`
- **Required:** No
- **Default:** `3600` (1 hour)

### SESSION_DRIVER
- **Purpose:** Session storage driver
- **Type:** Enum: `file`, `redis`, `database`
- **Example:** `redis`
- **Required:** No
- **Default:** `file`

### SESSION_LIFETIME
- **Purpose:** Session lifetime in minutes
- **Type:** Integer
- **Example:** `120`
- **Required:** No
- **Default:** `120` (2 hours)

### SESSION_ENCRYPT
- **Purpose:** Encrypt session data
- **Type:** Boolean
- **Example:** `true`
- **Required:** No
- **Default:** `false`

### SESSION_SECURE
- **Purpose:** HTTPS only cookies
- **Type:** Boolean
- **Example:** `true`
- **Required:** No
- **Default:** `false`

### SESSION_HTTP_ONLY
- **Purpose:** JavaScript cannot access cookies
- **Type:** Boolean
- **Example:** `true`
- **Required:** No
- **Default:** `true`

---

## Redis Configuration

### REDIS_HOST
- **Purpose:** Redis server hostname
- **Type:** String
- **Example:** `localhost` or `redis.example.com`
- **Required:** If using Redis
- **Default:** `127.0.0.1`

### REDIS_PASSWORD
- **Purpose:** Redis authentication password
- **Type:** String
- **Example:** `SecureRedisPassword!`
- **Required:** If Redis requires authentication
- **Security:** Store in secure vault

### REDIS_PORT
- **Purpose:** Redis server port
- **Type:** Integer
- **Example:** `6379`
- **Required:** No
- **Default:** `6379`

### REDIS_DB
- **Purpose:** Redis database number
- **Type:** Integer (0-15)
- **Example:** `0`
- **Required:** No
- **Default:** `0`

---

## Mail Configuration

### MAIL_DRIVER
- **Purpose:** Mail driver
- **Type:** Enum: `smtp`, `mailgun`, `postmark`, `ses`
- **Example:** `smtp`
- **Required:** Yes

### MAIL_HOST
- **Purpose:** SMTP server hostname
- **Type:** String
- **Example:** `smtp.gmail.com`
- **Required:** If using SMTP

### MAIL_PORT
- **Purpose:** SMTP server port
- **Type:** Integer
- **Example:** `587` or `465`
- **Required:** If using SMTP
- **Note:** 587 for TLS, 465 for SSL

### MAIL_USERNAME
- **Purpose:** SMTP username
- **Type:** String
- **Example:** `your-email@gmail.com`
- **Required:** If using SMTP
- **Security:** Store in secure vault

### MAIL_PASSWORD
- **Purpose:** SMTP password
- **Type:** String
- **Example:** `SmtpPassword123!`
- **Required:** If using SMTP
- **Security:** Store in secure vault

### MAIL_ENCRYPTION
- **Purpose:** SMTP encryption
- **Type:** Enum: `tls`, `ssl`
- **Example:** `tls`
- **Required:** If using SMTP

### MAIL_FROM_NAME
- **Purpose:** Display name for emails
- **Type:** String
- **Example:** `BNHS Document Request System`
- **Required:** Yes

### MAIL_FROM_ADDRESS
- **Purpose:** From email address
- **Type:** Email
- **Example:** `noreply@your-domain.com`
- **Required:** Yes

---

## Queue Configuration

### QUEUE_CONNECTION
- **Purpose:** Queue driver
- **Type:** Enum: `sync`, `redis`, `database`
- **Example:** `redis`
- **Required:** No
- **Default:** `sync`
- **Note:** Use `redis` or `database` in production

### QUEUE_TIMEOUT
- **Purpose:** Job timeout in seconds
- **Type:** Integer
- **Example:** `60`
- **Required:** No
- **Default:** `60`

### QUEUE_TRIES
- **Purpose:** Job retry attempts
- **Type:** Integer
- **Example:** `3`
- **Required:** No
- **Default:** `3`

### QUEUE_WAIT_TIME
- **Purpose:** Seconds to wait before retry
- **Type:** Integer
- **Example:** `3`
- **Required:** No
- **Default:** `3`

---

## Logging Configuration

### LOG_CHANNEL
- **Purpose:** Default log channel
- **Type:** Enum: `stack`, `single`, `daily`
- **Example:** `stack`
- **Required:** No
- **Default:** `stack`

### LOG_STACK
- **Purpose:** Channels for stack driver
- **Type:** Comma-separated string
- **Example:** `single,slack`
- **Required:** No
- **Default:** `single`

### LOG_LEVEL
- **Purpose:** Minimum log level
- **Type:** Enum: `debug`, `info`, `warning`, `error`, `critical`
- **Example:** `warning`
- **Required:** No
- **Default:** `debug`

### LOG_SLACK_WEBHOOK_URL
- **Purpose:** Slack webhook for critical errors
- **Type:** URL
- **Example:** `https://hooks.slack.com/services/YOUR/WEBHOOK/URL`
- **Required:** If using Slack logging
- **Security:** Store in secure vault

---

## File Storage Configuration

### FILESYSTEM_DISK
- **Purpose:** Default file disk
- **Type:** Enum: `local`, `s3`
- **Example:** `s3`
- **Required:** No
- **Default:** `local`

### FILESYSTEM_VISIBILITY
- **Purpose:** Default file visibility
- **Type:** Enum: `public`, `private`
- **Example:** `private`
- **Required:** No
- **Default:** `public`

### AWS_ACCESS_KEY_ID
- **Purpose:** AWS access key
- **Type:** String
- **Example:** `AKIA...`
- **Required:** If using S3
- **Security:** Store in secure vault

### AWS_SECRET_ACCESS_KEY
- **Purpose:** AWS secret key
- **Type:** String
- **Example:** `wJal...`
- **Required:** If using S3
- **Security:** Store in secure vault

### AWS_DEFAULT_REGION
- **Purpose:** AWS region
- **Type:** String
- **Example:** `us-east-1`
- **Required:** If using S3

### AWS_BUCKET
- **Purpose:** S3 bucket name
- **Type:** String
- **Example:** `bnhs-production-files`
- **Required:** If using S3

---

## Security Configuration

### CIPHER
- **Purpose:** Encryption algorithm
- **Type:** String
- **Example:** `AES-256-CBC`
- **Required:** No
- **Default:** `AES-256-CBC`

### CORS_ALLOWED_ORIGINS
- **Purpose:** CORS allowed origins
- **Type:** Comma-separated URLs
- **Example:** `https://your-domain.com`
- **Required:** No

### RATE_LIMIT_ENABLED
- **Purpose:** Enable rate limiting
- **Type:** Boolean
- **Example:** `true`
- **Required:** No

### RATE_LIMIT_OTP_SEND
- **Purpose:** OTP request rate limit
- **Type:** String (requests/minutes)
- **Example:** `5/60`
- **Required:** No

### RATE_LIMIT_LOGIN_ATTEMPT
- **Purpose:** Login rate limit
- **Type:** String (attempts/minutes)
- **Example:** `5/15`
- **Required:** No

---

## Monitoring & Debugging

### TELESCOPE_ENABLED
- **Purpose:** Enable Laravel Telescope (development)
- **Type:** Boolean
- **Example:** `false`
- **Required:** No
- **Default:** `false`
- **Note:** Must be disabled in production

### DEBUGBAR_ENABLED
- **Purpose:** Enable Laravel Debugbar
- **Type:** Boolean
- **Example:** `false`
- **Required:** No
- **Default:** `false`
- **Note:** Must be disabled in production

### TINKER_ENABLED
- **Purpose:** Enable PsySH Tinker
- **Type:** Boolean
- **Example:** `false`
- **Required:** No
- **Default:** `false`
- **Note:** Consider disabling in production

---

## Backup Configuration

### BACKUP_SCHEDULE_ENABLED
- **Purpose:** Enable automatic backups
- **Type:** Boolean
- **Example:** `true`
- **Required:** No

### BACKUP_SCHEDULE_TIME
- **Purpose:** Backup time (24h format)
- **Type:** String (HH:MM)
- **Example:** `02:00`
- **Required:** No

### BACKUP_RETENTION_DAYS
- **Purpose:** Keep backups for N days
- **Type:** Integer
- **Example:** `30`
- **Required:** No

### BACKUP_NOTIFICATION_EMAIL
- **Purpose:** Email for backup notifications
- **Type:** Email
- **Example:** `admin@your-domain.com`
- **Required:** No

---

## Development-Only Variables

> **Warning:** These should NEVER be enabled in production

### VITE_DEBUG
- **Purpose:** Enable Vite debug mode
- **Type:** Boolean
- **Example:** `false` (production)
- **Note:** For development only

### VITE_HMR
- **Purpose:** Enable Hot Module Reloading
- **Type:** Boolean
- **Example:** `false` (production)
- **Note:** For development only

---

## Environment Variable Examples

### Production .env
```
APP_NAME="BNHS Document Request System"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:XXXXX...

DB_HOST=db.example.com
DB_DATABASE=bnhs_prod
DB_USERNAME=db_user
DB_PASSWORD=SecurePassword123!

CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=cache.example.com
REDIS_PASSWORD=RedisPassword!

MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_FROM_ADDRESS=noreply@your-domain.com

AWS_BUCKET=bnhs-production
AWS_ACCESS_KEY_ID=XXXXX...
AWS_SECRET_ACCESS_KEY=XXXXX...

LOG_LEVEL=warning
```

### Development .env
```
APP_NAME="BNHS"
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:XXXXX...

DB_HOST=127.0.0.1
DB_DATABASE=bnhs_dev
DB_USERNAME=root
DB_PASSWORD=root

CACHE_DRIVER=file
SESSION_DRIVER=file

MAIL_DRIVER=log

FILESYSTEM_DISK=local

TELESCOPE_ENABLED=true
DEBUGBAR_ENABLED=true

LOG_LEVEL=debug
```

---

## Security Best Practices

1. **Never commit .env to version control**
2. **Use strong, random passwords (16+ characters)**
3. **Rotate credentials regularly**
4. **Store secrets in secure vault (AWS Secrets Manager, HashiCorp Vault)**
5. **Use environment-specific configurations**
6. **Monitor environment variable access**
7. **Encrypt sensitive data at rest**
8. **Use HTTPS for all connections**

---

## Validation Checklist

- [ ] All required variables set
- [ ] URLs use HTTPS in production
- [ ] Passwords are strong and random
- [ ] Database credentials correct
- [ ] Email settings tested
- [ ] File storage configured
- [ ] Cache/Redis configured
- [ ] Logging configured
- [ ] Backups enabled
- [ ] Security settings applied

---

**End of Reference Guide**

For questions about specific variables, consult the Laravel documentation or contact the DevOps team.
