# Email Setup Guide for bnhs.edu.ph Domain

This guide explains how to configure email sending from your `bnhs.edu.ph` domain.

---

## Option 1: Google Workspace (Recommended if you have it)

If your school uses Google Workspace (formerly G Suite) for `bnhs.edu.ph`:

### Setup Steps:
1. Create a service account email: `noreply@bnhs.edu.ph` in Google Workspace admin
2. Generate an App Password for that account:
   - Go to the Google Account
   - Security → 2-Step Verification → App Passwords
   - Generate password for "Mail"

### .env Configuration:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@bnhs.edu.ph
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@bnhs.edu.ph"
MAIL_FROM_NAME="BNHS eDocument System"
```

---

## Option 2: Your Hosting Provider's SMTP

If you have email hosting with your domain provider (cPanel, Plesk, etc.):

### Common Hosting Providers:

#### cPanel / Standard Hosting:
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.bnhs.edu.ph
MAIL_PORT=587
MAIL_USERNAME=noreply@bnhs.edu.ph
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@bnhs.edu.ph"
MAIL_FROM_NAME="BNHS eDocument System"
```

**Note:** Replace `mail.bnhs.edu.ph` with your actual mail server (check your hosting provider's documentation)

---

## Option 3: Mailgun (Recommended for Production)

Mailgun offers a free tier (5,000 emails/month) and supports custom domains.

### Setup Steps:
1. Sign up at [mailgun.com](https://www.mailgun.com) (free tier available)
2. Add and verify your domain `bnhs.edu.ph`
3. Add DNS records (MX, TXT, CNAME) as instructed
4. Get your API credentials from the dashboard

### Install Mailgun Package:
```bash
composer require symfony/mailgun-mailer symfony/http-client
```

### .env Configuration:
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=bnhs.edu.ph
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS="noreply@bnhs.edu.ph"
MAIL_FROM_NAME="BNHS eDocument System"
```

---

## Option 4: SendGrid (Alternative Service)

SendGrid offers 100 free emails/day forever.

### Setup Steps:
1. Sign up at [sendgrid.com](https://sendgrid.com)
2. Verify your domain `bnhs.edu.ph`
3. Create an API key

### Install SendGrid Package:
```bash
composer require symfony/sendgrid-mailer symfony/http-client
```

### .env Configuration:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@bnhs.edu.ph"
MAIL_FROM_NAME="BNHS eDocument System"
```

---

## Option 5: AWS SES (For High Volume)

If you need to send many emails and have AWS account:

### Setup Steps:
1. Verify your domain in AWS SES
2. Get AWS credentials (Access Key ID & Secret)
3. Request production access (move out of sandbox)

### .env Configuration:
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS="noreply@bnhs.edu.ph"
MAIL_FROM_NAME="BNHS eDocument System"
```

---

## Testing Your Configuration

After updating your `.env` file:

1. Clear config cache:
```bash
php artisan config:clear
```

2. Test email sending:
```bash
php artisan mail:test your-email@test.com
```

3. Test full OTP flow:
```bash
php artisan otp:test your-email@test.com
```

---

## Which Option Should I Choose?

| Option | Best For | Cost | Setup Difficulty |
|--------|----------|------|------------------|
| Google Workspace | Already using Google Workspace | Free (if you have it) | Easy |
| Hosting Provider SMTP | Simple, basic needs | Usually free | Easy |
| Mailgun | Production, good deliverability | Free tier (5k/month) | Medium |
| SendGrid | Simple, reliable | Free tier (100/day) | Medium |
| AWS SES | High volume, AWS users | Pay per email | Medium-Hard |

---

## Important Notes

- **Always test** with a real email address before going to production
- **Check spam folders** - some email providers are strict
- **Set up SPF/DKIM records** for better deliverability (especially with custom domains)
- **Use a dedicated email** like `noreply@bnhs.edu.ph` for automated emails

