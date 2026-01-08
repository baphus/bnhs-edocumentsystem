# Heroku Deployment Guide

## Prerequisites
1. Install [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli)
2. Create a Heroku account

## Initial Setup

### 1. Login to Heroku
```bash
heroku login
```

### 2. Create Heroku App
```bash
heroku create bnhs-edocument-system
```

Or use a different name if that's taken:
```bash
heroku create your-app-name
```

### 3. Add PostgreSQL Database
```bash
heroku addons:create heroku-postgresql:essential-0
```

### 4. Set Environment Variables
```bash
# Generate and set APP_KEY
php artisan key:generate --show
heroku config:set APP_KEY=base64:YOUR_GENERATED_KEY

# Set other required variables
heroku config:set APP_NAME="BNHS eDocument Request"
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set LOG_CHANNEL=stack
heroku config:set LOG_LEVEL=error

# Session & Cache
heroku config:set SESSION_DRIVER=database
heroku config:set CACHE_STORE=database
heroku config:set QUEUE_CONNECTION=database

# Mail Configuration (update with your SMTP details)
heroku config:set MAIL_MAILER=smtp
heroku config:set MAIL_HOST=smtp.gmail.com
heroku config:set MAIL_PORT=587
heroku config:set MAIL_USERNAME=your-email@gmail.com
heroku config:set MAIL_PASSWORD=your-app-password
heroku config:set MAIL_ENCRYPTION=tls
heroku config:set MAIL_FROM_ADDRESS=your-email@gmail.com
heroku config:set MAIL_FROM_NAME="BNHS eDocument Request"
```

### 5. Configure Buildpacks
```bash
heroku buildpacks:add --index 1 heroku/nodejs
heroku buildpacks:add --index 2 heroku/php
```

### 6. Set PHP Version
The app requires PHP 8.3+. This is already configured in `composer.json`.

## Deploy

### First Deployment
```bash
git push heroku main
```

### Subsequent Deployments
```bash
git add .
git commit -m "Your commit message"
git push heroku main
```

## Post-Deployment Tasks

### Run Migrations Manually (if needed)
```bash
heroku run php artisan migrate --force
```

### Seed Database
```bash
heroku run php artisan db:seed --force
```

### Clear Cache
```bash
heroku run php artisan cache:clear
heroku run php artisan config:clear
heroku run php artisan route:clear
heroku run php artisan view:clear
```

### Create Storage Link
```bash
heroku run php artisan storage:link
```

## Viewing Logs
```bash
# View real-time logs
heroku logs --tail

# View recent logs
heroku logs --num 100
```

## Common Commands

### Open App in Browser
```bash
heroku open
```

### Access App Console
```bash
heroku run bash
```

### Access Tinker
```bash
heroku run php artisan tinker
```

### Database Console
```bash
heroku pg:psql
```

### Restart App
```bash
heroku restart
```

## Environment Variables Reference

All environment variables can be set with:
```bash
heroku config:set VARIABLE_NAME=value
```

View current config:
```bash
heroku config
```

### Required Variables
- `APP_KEY` - Laravel encryption key
- `APP_NAME` - Application name
- `APP_ENV` - Should be "production"
- `APP_DEBUG` - Should be "false"
- `DATABASE_URL` - Auto-set by Heroku PostgreSQL addon

### Optional Variables
- `DEV_BYPASS_OTP` - Set to "false" in production
- `DEV_DEFAULT_OTP` - Remove in production
- `DEV_TOOLS_ENABLED` - Set to "false" in production

## File Storage

Heroku has an **ephemeral filesystem** - files uploaded by users will be lost on dyno restart.

### Solutions:
1. **Amazon S3** (Recommended)
   ```bash
   heroku config:set FILESYSTEM_DISK=s3
   heroku config:set AWS_ACCESS_KEY_ID=your-key
   heroku config:set AWS_SECRET_ACCESS_KEY=your-secret
   heroku config:set AWS_DEFAULT_REGION=us-east-1
   heroku config:set AWS_BUCKET=your-bucket-name
   ```

2. **Cloudinary** addon
   ```bash
   heroku addons:create cloudinary
   ```

## Queue Workers

For background job processing:

### Option 1: Upgrade to Hobby Dyno
```bash
heroku ps:scale worker=1
```

### Option 2: Use Scheduler Addon
```bash
heroku addons:create scheduler:standard
heroku addons:open scheduler
```
Then add: `php artisan queue:work --stop-when-empty`

## Scaling

### Scale web dynos
```bash
heroku ps:scale web=1
```

### Upgrade dyno type
```bash
heroku ps:type web=hobby
```

## Troubleshooting

### Build Failed
```bash
heroku logs --tail
heroku run bash
composer install --verbose
```

### Database Issues
```bash
heroku pg:info
heroku pg:reset DATABASE_URL --confirm your-app-name
heroku run php artisan migrate:fresh --seed --force
```

### Clear All Caches
```bash
heroku run php artisan optimize:clear
```

## Security Checklist

- ✅ `APP_DEBUG=false`
- ✅ `APP_ENV=production`
- ✅ Strong `APP_KEY` generated
- ✅ Database SSL enabled (automatic with Heroku Postgres)
- ✅ Remove development config (`DEV_*` variables)
- ✅ HTTPS enforced (automatic on Heroku)
- ✅ Set proper CORS origins

## Custom Domain

```bash
heroku domains:add www.yourdomain.com
heroku domains:add yourdomain.com
```

Follow the DNS configuration instructions provided.

Enable automatic SSL:
```bash
heroku certs:auto:enable
```

## Monitoring

- **Heroku Dashboard**: https://dashboard.heroku.com/apps/your-app-name
- **Metrics**: View dyno performance and request metrics
- **Logs**: Use `heroku logs --tail` or add log drain service

## Cost Optimization

- **Eco Dynos**: $5/month for basic apps
- **Hobby Dynos**: $7/month for apps that need 24/7 uptime
- **Essential Postgres**: Free tier available, $5/month for essential-0

## Support

- Heroku Dev Center: https://devcenter.heroku.com/
- Laravel Deployment: https://laravel.com/docs/deployment
