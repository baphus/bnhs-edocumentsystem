# Railway Deployment Checklist

Use this checklist to ensure a smooth deployment to Railway.

## Pre-Deployment (Before You Start)

### Local Development
- [ ] All features working locally
- [ ] All tests passing (`npm run test:all`)
- [ ] No console errors or warnings
- [ ] Database migrations tested
- [ ] Frontend build successful (`npm run build`)
- [ ] Code committed to Git
- [ ] `.env.railway.example` reviewed and updated if needed

### Accounts & Access
- [ ] Railway account created ([railway.app](https://railway.app))
- [ ] Railway CLI installed (`npm install -g @railway/cli`)
- [ ] Logged into Railway CLI (`railway login`)
- [ ] Email SMTP credentials ready (Gmail, SendGrid, etc.)

### Documentation Review
- [ ] Read [RAILWAY_QUICK_START.md](./RAILWAY_QUICK_START.md)
- [ ] Reviewed [RAILWAY_DEPLOYMENT.md](./RAILWAY_DEPLOYMENT.md)
- [ ] Environment variables list prepared

---

## Railway Setup

### Project Creation
- [ ] Created new Railway project (`railway init`)
- [ ] Project linked to local directory (`railway link`)
- [ ] MySQL database added to project (`railway add` → MySQL)

### Environment Variables
Set these via `railway variables set KEY=VALUE` or Railway Dashboard:

#### Required Variables
- [ ] `APP_KEY` - Generated with `php artisan key:generate --show`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` - Your Railway app URL (e.g., https://your-app.railway.app)

#### Database Variables (Auto-configured by Railway MySQL)
- [ ] Verified MySQL service is running
- [ ] Database variables automatically injected by Railway

#### Mail Configuration
- [ ] `MAIL_MAILER=smtp`
- [ ] `MAIL_HOST` - SMTP host (e.g., smtp.gmail.com)
- [ ] `MAIL_PORT=587`
- [ ] `MAIL_USERNAME` - Your email
- [ ] `MAIL_PASSWORD` - App password (not regular password!)
- [ ] `MAIL_ENCRYPTION=tls`
- [ ] `MAIL_FROM_ADDRESS` - Sender email
- [ ] `MAIL_FROM_NAME` - Sender name

#### Cache & Session
- [ ] `SESSION_DRIVER=database`
- [ ] `CACHE_DRIVER=database`
- [ ] `QUEUE_CONNECTION=database`

#### Security
- [ ] `JWT_SECRET` - Random 64-character string
- [ ] `OTP_EXPIRATION=10`

### Optional Enhancements
- [ ] Redis service added (for better performance)
- [ ] Redis variables configured if using Redis
- [ ] S3 bucket configured (if using external storage)
- [ ] Error tracking service setup (Sentry, Bugsnag)

---

## Deployment

### Build & Deploy
- [ ] Frontend assets built locally (`npm run build`)
- [ ] Deployed to Railway (`railway up` or `./deploy-railway.sh`)
- [ ] Build completed successfully
- [ ] No build errors in logs (`railway logs`)

### Database Setup
- [ ] Migrations ran successfully (`railway run php artisan migrate --force`)
- [ ] Default settings seeded (`railway run php artisan db:seed --force`)
- [ ] Database tables verified

### Application Start
- [ ] Application started successfully
- [ ] No startup errors in logs
- [ ] Health check passing
- [ ] Port binding successful

---

## Post-Deployment Verification

### Application Testing
- [ ] Application accessible via Railway URL (`railway open`)
- [ ] Home page loads correctly
- [ ] CSS and JavaScript loading properly
- [ ] Images and assets loading

### Authentication
- [ ] Registration works
- [ ] Login works
- [ ] OTP email received and works
- [ ] Logout works
- [ ] Password reset works

### Core Functionality
- [ ] Document request submission
- [ ] File upload works
- [ ] Email notifications sent
- [ ] Document type selection
- [ ] Track request by tracking code
- [ ] Admin login
- [ ] Admin dashboard accessible

### Admin Features
- [ ] View all requests
- [ ] Update request status
- [ ] Process requests
- [ ] View audit logs
- [ ] User management
- [ ] Settings management

### Performance & Security
- [ ] Page load times acceptable (< 3 seconds)
- [ ] HTTPS enabled (automatic on Railway)
- [ ] No exposed sensitive data
- [ ] Rate limiting working
- [ ] Error pages (404, 500) showing correctly
- [ ] Logs not showing sensitive information

---

## Monitoring Setup

### Railway Dashboard
- [ ] Deployment status monitored
- [ ] Resource usage checked (CPU, Memory)
- [ ] Logs reviewed for errors
- [ ] Metrics tab reviewed

### Logging
- [ ] Real-time logs accessible (`railway logs`)
- [ ] No critical errors in logs
- [ ] Log level appropriate (info, not debug)

### Alerts (Optional)
- [ ] Deployment failure notifications configured
- [ ] Error rate alerts setup
- [ ] Resource usage alerts configured

---

## Documentation & Handoff

### Team Access
- [ ] Team members invited to Railway project
- [ ] Deployment documentation shared
- [ ] Environment variables documented
- [ ] Admin credentials shared securely

### Documentation Updates
- [ ] `APP_URL` updated in documentation
- [ ] Deployment date recorded
- [ ] Known issues documented
- [ ] Rollback procedure reviewed

### Backup & Recovery
- [ ] Database backup strategy confirmed
- [ ] Backup schedule documented
- [ ] Recovery procedure tested
- [ ] Rollback plan in place

---

## Optional Enhancements

### Custom Domain
- [ ] Custom domain purchased
- [ ] Domain added in Railway Settings → Domains
- [ ] DNS records configured
- [ ] SSL certificate issued
- [ ] `APP_URL` updated to custom domain

### CI/CD Pipeline
- [ ] GitHub repository connected to Railway
- [ ] Automatic deployments enabled
- [ ] Branch protection rules configured
- [ ] Deploy previews enabled (for PRs)

### Performance Optimization
- [ ] Redis added for cache and sessions
- [ ] Queue worker confirmed running
- [ ] Asset minification verified
- [ ] Database queries optimized
- [ ] CDN configured (if needed)

### Monitoring & Analytics
- [ ] Uptime monitoring setup (UptimeRobot, Pingdom)
- [ ] Error tracking configured (Sentry)
- [ ] Application performance monitoring (New Relic, Datadog)
- [ ] Google Analytics or similar installed

---

## Final Checklist

- [ ] All critical functionality verified
- [ ] No critical errors in logs
- [ ] Performance acceptable
- [ ] Security measures in place
- [ ] Team trained on deployment
- [ ] Documentation complete
- [ ] Monitoring active
- [ ] Backup strategy confirmed

---

## Post-Launch (First 24 Hours)

- [ ] Monitor logs for errors (`railway logs`)
- [ ] Check resource usage in Railway Dashboard
- [ ] Verify email deliverability
- [ ] Test with real users
- [ ] Collect user feedback
- [ ] Monitor performance metrics
- [ ] Address any immediate issues

---

## Emergency Contacts

**Railway Support:**
- Discord: https://discord.gg/railway
- Email: support@railway.app

**Application Issues:**
- Check logs: `railway logs`
- Rollback: Railway Dashboard → Deployments → Select previous → Redeploy

**Database Issues:**
- Check connection: `railway run php artisan migrate:status`
- Rollback migration: `railway run php artisan migrate:rollback`

---

## Quick Commands Reference

```bash
# View deployment status
railway status

# View logs
railway logs

# Run artisan command
railway run php artisan <command>

# Set environment variable
railway variables set KEY=VALUE

# Open application
railway open

# Restart service
railway restart

# Rollback deployment
railway rollback <deployment-id>
```

---

## Success Criteria

Your deployment is successful when:
- ✅ Application accessible and loads quickly
- ✅ All core features working
- ✅ Emails sending correctly
- ✅ No critical errors in logs
- ✅ Database migrations applied
- ✅ File uploads working
- ✅ Admin panel accessible
- ✅ HTTPS enabled
- ✅ Monitoring active

---

**Deployment Date:** _________________  
**Deployed By:** _________________  
**Railway Project ID:** _________________  
**Application URL:** _________________

**Notes:**
