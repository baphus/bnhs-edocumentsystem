# Railway Deployment Files

This directory contains all necessary files for deploying the BNHS Document Request System to Railway.

## Files Overview

### Configuration Files

- **`railway.json`** - Railway service configuration
- **`nixpacks.toml`** - Nixpacks build configuration (Railway's build system)
- **`Procfile.railway`** - Process definitions for Railway
- **`.env.railway.example`** - Environment variables template

### Scripts

- **`railway-start.sh`** - Application startup script (runs migrations, starts queue worker, starts web server)
- **`railway-postbuild.sh`** - Post-build script (sets permissions, caches config)
- **`deploy-railway.sh`** - Automated deployment helper script

### Documentation

- **`doc/RAILWAY_DEPLOYMENT.md`** - Complete deployment guide with troubleshooting
- **`doc/RAILWAY_QUICK_START.md`** - Quick 5-minute deployment guide

## Quick Deploy

```bash
# 1. Install Railway CLI
npm install -g @railway/cli

# 2. Login
railway login

# 3. Deploy
./deploy-railway.sh
```

See [RAILWAY_QUICK_START.md](./doc/RAILWAY_QUICK_START.md) for detailed quick start instructions.

## Architecture

### Build Process
1. Nixpacks detects PHP and Node.js
2. Installs dependencies (`composer install`, `npm install`)
3. Builds frontend assets (`npm run build`)
4. Caches Laravel configuration
5. Creates optimized autoloader

### Runtime Process
1. Sets proper permissions for Laravel directories
2. Creates storage symlink
3. Runs database migrations
4. Starts queue worker in background
5. Starts web server on Railway-provided PORT

### Database
Railway automatically provides MySQL environment variables:
- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`

These are automatically used by the application.

## Environment Variables

Required variables to set in Railway:
- `APP_KEY` - Laravel application key
- `APP_ENV=production`
- `APP_DEBUG=false`
- `MAIL_*` - Email configuration

See `.env.railway.example` for complete list.

## Key Features

✅ Automatic migrations on deployment
✅ Background queue worker
✅ Asset building and caching
✅ Storage permissions handled
✅ MySQL auto-configuration
✅ Health check endpoint
✅ Automatic restarts on failure

## Support

- [Railway Documentation](https://docs.railway.app)
- [Full Deployment Guide](./doc/RAILWAY_DEPLOYMENT.md)
- [Quick Start Guide](./doc/RAILWAY_QUICK_START.md)
