# Production Web Server Configuration

## Nginx Configuration (.server.conf)

Create `/etc/nginx/sites-available/bnhs.conf`:

```nginx
# BNHS Document Request System - Production Configuration
# Nginx Configuration for Laravel Application

upstream laravel_app {
    server 127.0.0.1:9000;
    keepalive 32;
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    
    # Allow Let's Encrypt verification
    location /.well-known/acme-challenge/ {
        root /var/www/bnhs/public;
    }
    
    # Redirect all HTTP to HTTPS
    location / {
        return 301 https://$server_name$request_uri;
    }
}

# HTTPS Server Block
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    # SSL Security Settings
    ssl_protocols TLSv1.3 TLSv1.2;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    ssl_stapling on;
    ssl_stapling_verify on;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript 
               application/json application/javascript application/xml+rss 
               application/atom+xml image/svg+xml;
    gzip_disable "msie6";
    
    # Root directory
    root /var/www/bnhs/public;
    index index.php index.html index.htm;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
    
    # Prevent access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ ~$ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass laravel_app;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_param HTTPS on;
        fastcgi_param HTTP_SCHEME https;
        fastcgi_buffering off;
    }
    
    # Static assets caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Deny access to Laravel internals
    location ~ ^/(app|bootstrap|config|database|resources|routes|storage|tests|vendor) {
        deny all;
    }
    
    # Logging
    access_log /var/log/nginx/bnhs_access.log;
    error_log /var/log/nginx/bnhs_error.log warn;
    
    client_max_body_size 10M;
    send_timeout 30s;
}
```

## Systemd Service Files for Queue Worker

Create `/etc/systemd/system/laravel-queue-worker.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/bnhs
ExecStart=/usr/bin/php /var/www/bnhs/artisan queue:work --queue=default --tries=3 --timeout=300
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

Create `/etc/systemd/system/laravel-scheduler.service`:

```ini
[Unit]
Description=Laravel Scheduler
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/bnhs
ExecStart=/bin/bash -c 'while [ 1 ]; do /usr/bin/php /var/www/bnhs/artisan schedule:run >> /dev/null 2>&1; sleep 60; done'
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

## Enable and Start Services

```bash
sudo systemctl enable laravel-queue-worker laravel-scheduler
sudo systemctl start laravel-queue-worker laravel-scheduler

# Monitor services
sudo systemctl status laravel-queue-worker
sudo systemctl status laravel-scheduler
```
