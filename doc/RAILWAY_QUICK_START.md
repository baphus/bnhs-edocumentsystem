# Railway Quick Start Guide

This guide will help you deploy the BNHS Document Request System to Railway in minutes.

## Prerequisites

- Railway account ([Sign up here](https://railway.app))
- Railway CLI: `npm install -g @railway/cli`
- Git repository

## Quick Deploy (5 minutes)

### Step 1: Install Railway CLI

```bash
npm install -g @railway/cli
```

### Step 2: Login

```bash
railway login
```

### Step 3: Initialize Project

```bash
# From your project root
railway init
```

Select "Empty Project" and give it a name.

### Step 4: Add MySQL Database

```bash
railway add
```

Select "MySQL" from the list. Railway will automatically create and link the database.

### Step 5: Set Environment Variables

```bash
# Generate and set APP_KEY
railway variables set APP_KEY=$(php artisan key:generate --show)

# Set basic variables
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set SESSION_DRIVER=database
railway variables set CACHE_DRIVER=database
railway variables set QUEUE_CONNECTION=database

# Set mail configuration (replace with your details)
railway variables set MAIL_MAILER=smtp
railway variables set MAIL_HOST=smtp.gmail.com
railway variables set MAIL_PORT=587
railway variables set MAIL_USERNAME=your-email@gmail.com
railway variables set MAIL_PASSWORD=your-app-password
railway variables set MAIL_ENCRYPTION=tls
railway variables set MAIL_FROM_ADDRESS=noreply@yourschool.edu.ph
```

### Step 6: Deploy

```bash
# Build assets
npm run build

# Deploy to Railway
railway up
```

### Step 7: Run Migrations

```bash
railway run php artisan migrate --force
railway run php artisan db:seed --force
```

### Step 8: Open Your App

```bash
railway open
```

## That's It! 🎉

Your application is now live on Railway!

## Next Steps

1. **Configure Custom Domain** (Optional)
   - Go to Railway Dashboard → Settings → Domains
   - Add your custom domain and update DNS records

2. **Monitor Your App**
   ```bash
   railway logs
   ```

3. **Setup Automatic Deployments**
   - Connect your GitHub repository in Railway Dashboard
   - Enable automatic deployments on push

## Common Commands

```bash
# View logs
railway logs

# Run artisan commands
railway run php artisan <command>

# Check status
railway status

# Open in browser
railway open

# View variables
railway variables
```

## Troubleshooting

### Build fails?
```bash
railway logs
```
Check the logs for specific errors.

### Database connection error?
```bash
railway variables
```
Verify that MySQL variables are set.

### Need help?
See the full guide: [RAILWAY_DEPLOYMENT.md](./RAILWAY_DEPLOYMENT.md)

---

For detailed documentation, monitoring setup, and advanced configuration, refer to:
- [Full Railway Deployment Guide](./RAILWAY_DEPLOYMENT.md)
- [Environment Variables Guide](./ENV_VARIABLES_GUIDE.md)
