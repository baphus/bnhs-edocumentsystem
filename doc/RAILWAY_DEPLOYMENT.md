# Railway Deployment Guide for BNHS Document Request System

**Last Updated:** January 7, 2026  
**Platform:** Railway.app  
**Application:** BNHS Document Request System

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Initial Setup](#initial-setup)
3. [Environment Configuration](#environment-configuration)
4. [Database Setup](#database-setup)
5. [Deployment Steps](#deployment-steps)
6. [Post-Deployment](#post-deployment)
7. [Monitoring & Logs](#monitoring--logs)
8. [Troubleshooting](#troubleshooting)
9. [Rollback](#rollback)

---

## Prerequisites

### Required Software
- **Railway CLI:** Install globally with `npm install -g @railway/cli`
- **Git:** Latest version
- **Node.js:** 18+ LTS
- **Composer:** 2.0+

### Required Accounts
- Railway account (sign up at [railway.app](https://railway.app))
- GitHub account (for repository linking)

---

## Initial Setup

### 1. Install Railway CLI

```bash
npm install -g @railway/cli
```

### 2. Login to Railway

```bash
railway login
```

This will open a browser window for authentication.

### 3. Create a New Project

Option A: Via Railway Dashboard
1. Go to [railway.app/new](https://railway.app/new)
2. Choose "Deploy from GitHub repo"
3. Select your repository
4. Railway will auto-detect Laravel and configure accordingly

Option B: Via CLI
```bash
railway init
```

### 4. Link Your Local Project

If you created the project via the dashboard:
```bash
railway link
```

---

## Environment Configuration

### Required Environment Variables

Railway needs these environment variables configured. Set them via:
- Railway Dashboard → Your Project → Variables tab
- Or via CLI: `railway variables set KEY=VALUE`

#### Core Application Variables

```env
# Application
APP_NAME="BNHS Document Request System"
APP_ENV=production
APP_KEY=                                    # Generate with: php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://your-app.railway.app       # Railway will provide this

# Database (use Railway PostgreSQL or MySQL)
DB_CONNECTION=mysql                         # or 'pgsql' for PostgreSQL
DB_HOST=${MYSQLHOST}                       # Railway provides this automatically
DB_PORT=${MYSQLPORT}                       # Railway provides this automatically
DB_DATABASE=${MYSQLDATABASE}               # Railway provides this automatically
DB_USERNAME=${MYSQLUSER}                   # Railway provides this automatically
DB_PASSWORD=${MYSQLPASSWORD}               # Railway provides this automatically

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=database                      # Can use 'redis' if you add Redis service
QUEUE_CONNECTION=database                  # Can use 'redis' if you add Redis service

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com                   # Or your SMTP provider
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourschool.edu.ph
MAIL_FROM_NAME="${APP_NAME}"

# Security
JWT_SECRET=                                # Generate a random 64-char string
OTP_EXPIRATION=10                         # Minutes

# File Storage
FILESYSTEM_DISK=public                     # or 's3' for cloud storage

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=info
```

#### Quick Setup via CLI

```bash
# Set APP_KEY
railway variables set APP_KEY=$(php artisan key:generate --show)

# Set other variables
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set SESSION_DRIVER=database
railway variables set CACHE_DRIVER=database
railway variables set QUEUE_CONNECTION=database
```

---

## Database Setup

### Option 1: Railway MySQL (Recommended)

1. In Railway Dashboard, click "New" → "Database" → "Add MySQL"
2. Railway automatically injects these variables:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
   - `DATABASE_URL`

3. Configure your app to use these variables (already set in .env.railway.example)

### Option 2: Railway PostgreSQL

1. In Railway Dashboard, click "New" → "Database" → "Add PostgreSQL"
2. Update `DB_CONNECTION=pgsql` in variables
3. Railway provides:
   - `PGHOST`
   - `PGPORT`
   - `PGDATABASE`
   - `PGUSER`
   - `PGPASSWORD`

### Option 3: External Database

Set these variables manually:
```bash
railway variables set DB_HOST=your-db-host.com
railway variables set DB_PORT=3306
railway variables set DB_DATABASE=your_database
railway variables set DB_USERNAME=your_user
railway variables set DB_PASSWORD=your_password
```

---

## Deployment Steps

### Method 1: Automated Deployment (Recommended)

```bash
# Make the script executable
chmod +x deploy-railway.sh

# Run deployment
./deploy-railway.sh
```

This script will:
- Check Railway CLI installation
- Verify login status
- Run tests
- Build frontend assets
- Deploy to Railway

### Method 2: Manual Deployment

```bash
# 1. Ensure you're logged in
railway login

# 2. Link to your project
railway link

# 3. Build assets locally
npm run build

# 4. Deploy
railway up

# 5. Run migrations
railway run php artisan migrate --force

# 6. Check logs
railway logs
```

### Method 3: GitHub Integration (Automatic)

1. Connect your GitHub repository in Railway Dashboard
2. Enable automatic deployments
3. Every push to main branch will trigger a deployment
4. Railway will automatically:
   - Install dependencies
   - Build assets
   - Run migrations (via railway-start.sh)
   - Start the application

---

## Post-Deployment

### 1. Verify Deployment

```bash
# Check deployment status
railway status

# View logs
railway logs

# Open your application
railway open
```

### 2. Run Database Seeds (First Deploy Only)

```bash
railway run php artisan db:seed --force
```

### 3. Create Admin User

Access your application and register the first admin user, or run:
```bash
railway run php artisan tinker
# Then in tinker:
# User::create([...]);
```

### 4. Test Core Functionality

- [ ] Login works
- [ ] Document request submission
- [ ] Email notifications
- [ ] File uploads
- [ ] Admin dashboard

### 5. Setup Custom Domain (Optional)

1. Go to Settings → Domains in Railway Dashboard
2. Add your custom domain
3. Configure DNS records as shown
4. Update `APP_URL` variable

---

## Monitoring & Logs

### View Real-Time Logs

```bash
railway logs
```

### View Specific Service Logs

```bash
railway logs --service web
```

### Monitor Metrics

Visit your Railway Dashboard → Metrics tab to see:
- CPU usage
- Memory usage
- Network traffic
- Request volume

### Setup Alerts

1. Go to Settings → Notifications
2. Configure Slack, Discord, or email notifications
3. Set up alerts for:
   - Deployment failures
   - High error rates
   - Resource usage

---

## Troubleshooting

### Common Issues

#### 1. Build Fails

**Error:** npm install fails
```bash
# Solution: Clear Railway build cache
railway run rm -rf node_modules
railway up --detach
```

**Error:** Composer install fails
```bash
# Solution: Check PHP version in nixpacks.toml
# Ensure it matches your composer.json requirements
```

#### 2. Application Won't Start

**Check logs:**
```bash
railway logs
```

**Common causes:**
- Missing `APP_KEY` - Generate with `php artisan key:generate --show`
- Database connection issues - Verify DB variables
- Port binding - Railway uses `PORT` environment variable

#### 3. Database Connection Errors

```bash
# Verify database service is running
railway status

# Check database variables
railway variables

# Test database connection
railway run php artisan migrate:status
```

#### 4. 500 Internal Server Error

```bash
# Enable debug mode temporarily
railway variables set APP_DEBUG=true

# Check logs for detailed error
railway logs

# Remember to disable debug after fixing
railway variables set APP_DEBUG=false
```

#### 5. Asset Files Not Loading

**Cause:** Missing public/build directory

**Solution:**
```bash
# Rebuild assets
npm run build

# Redeploy
railway up
```

#### 6. Queue Jobs Not Processing

**Cause:** Queue worker not running

**Solution:** The railway-start.sh script starts a queue worker automatically. Check if it's running:
```bash
railway run ps aux | grep queue
```

---

## Rollback

### Rollback to Previous Deployment

Railway keeps all deployments. To rollback:

1. **Via Dashboard:**
   - Go to Deployments tab
   - Find the working deployment
   - Click "Redeploy"

2. **Via CLI:**
```bash
# View recent deployments
railway status

# Rollback to specific deployment (use deployment ID from status)
railway rollback <deployment-id>
```

### Database Rollback

```bash
# Rollback last migration
railway run php artisan migrate:rollback

# Rollback specific steps
railway run php artisan migrate:rollback --step=2
```

---

## Best Practices

### 1. Use Environment-Specific Variables

- Keep production secrets in Railway variables
- Never commit `.env` files
- Use `.env.railway.example` as template

### 2. Enable Redis for Better Performance

```bash
# Add Redis service in Railway Dashboard
# Then update variables:
railway variables set CACHE_DRIVER=redis
railway variables set QUEUE_CONNECTION=redis
railway variables set SESSION_DRIVER=redis
```

### 3. Regular Backups

```bash
# Backup database
railway run php artisan backup:run
```

Setup automated backups in Railway or use external backup service.

### 4. Monitor Application Health

- Setup uptime monitoring (UptimeRobot, Pingdom)
- Configure error tracking (Sentry, Bugsnag)
- Review logs regularly

### 5. Optimize for Production

```bash
# These are run automatically in railway-start.sh
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
```

---

## Useful Commands

```bash
# Deploy application
railway up

# Run artisan commands
railway run php artisan <command>

# View environment variables
railway variables

# Set environment variable
railway variables set KEY=VALUE

# Delete environment variable
railway variables delete KEY

# Open application in browser
railway open

# View deployment status
railway status

# SSH into container
railway shell

# View real-time logs
railway logs

# Restart service
railway restart

# Link different project
railway link <project-id>

# Disconnect from project
railway unlink
```

---

## Additional Resources

- **Railway Documentation:** https://docs.railway.app
- **Railway Discord:** https://discord.gg/railway
- **Laravel Deployment:** https://laravel.com/docs/deployment
- **Railway Status:** https://status.railway.app

---

## Support

For Railway-specific issues:
- Railway Discord: https://discord.gg/railway
- Railway Support: support@railway.app

For application issues:
- Check application logs: `railway logs`
- Review deployment documentation
- Contact development team

---

## Deployment Checklist

Before deploying to production:

- [ ] All tests passing
- [ ] Environment variables configured
- [ ] Database service added and connected
- [ ] `APP_KEY` generated and set
- [ ] `APP_DEBUG=false` in production
- [ ] Mail configuration tested
- [ ] Custom domain configured (if needed)
- [ ] SSL enabled (automatic on Railway)
- [ ] Backups configured
- [ ] Monitoring setup
- [ ] Error tracking enabled
- [ ] Documentation updated
- [ ] Team has access to Railway project

---

**Document Version:** 1.0  
**Maintained By:** Development Team  
**Last Review:** January 7, 2026
