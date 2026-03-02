# 🚀 Udhëzues për Deployment në Production

Ky dokument përmban udhëzime të detajuara për publikimin e projektit AronTrade në production.

## 📋 Kërkesat e Serverit

### Minimum Requirements:
- **PHP**: >= 8.2
- **Composer**: >= 2.0
- **Node.js**: >= 16.x (për build)
- **MySQL/PostgreSQL**: >= 5.7 / >= 10
- **Nginx** ose **Apache**
- **SSL Certificate** (Let's Encrypt rekomandohet)

### Recommended:
- **RAM**: Minimum 2GB
- **CPU**: 2+ cores
- **Storage**: 20GB+ (varësisht nga imazhet e produkteve)

---

## 🔧 Hapat për Deployment

### 1. Përgatitja e Serverit

#### Instalimi i PHP dhe Extensions:
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath

# CentOS/RHEL
sudo yum install php82 php82-php-fpm php82-php-mysqlnd php82-php-mbstring php82-php-xml php82-php-curl php82-php-zip php82-php-gd php82-php-bcmath
```

#### Instalimi i Composer:
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### Instalimi i Node.js:
```bash
# Duke përdorur nvm (rekomandohet)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
nvm install 18
nvm use 18
```

#### Instalimi i MySQL/PostgreSQL:
```bash
# MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# PostgreSQL
sudo apt install postgresql postgresql-contrib
```

---

### 2. Upload i Kodit në Server

#### Opsioni A: Git Clone (Rekomandohet)
```bash
cd /var/www
sudo git clone https://github.com/username/gastrotrade.git
cd gastrotrade
sudo chown -R www-data:www-data .
```

#### Opsioni B: Upload me FTP/SFTP
- Upload të gjitha skedarët në `/var/www/gastrotrade`
- Sigurohu që `.env` nuk është uploaduar (ajo do të krijohet më vonë)

---

### 3. Konfigurimi i Bazës së të Dhënave

#### Krijimi i Database:
```sql
-- MySQL
CREATE DATABASE gastrotrade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gastrotrade_user'@'localhost' IDENTIFIED BY 'password_i_fortë_këtu';
GRANT ALL PRIVILEGES ON gastrotrade.* TO 'gastrotrade_user'@'localhost';
FLUSH PRIVILEGES;

-- PostgreSQL
CREATE DATABASE gastrotrade;
CREATE USER gastrotrade_user WITH PASSWORD 'password_i_fortë_këtu';
GRANT ALL PRIVILEGES ON DATABASE gastrotrade TO gastrotrade_user;
```

---

### 4. Konfigurimi i .env për Production

```bash
cd /var/www/gastrotrade
cp .env.example .env
nano .env
```

#### Konfigurimi i .env:
```env
APP_NAME="AronTrade"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://domaini-yt.com

APP_LOCALE=sq
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gastrotrade
DB_USERNAME=gastrotrade_user
DB_PASSWORD=password_i_fortë_këtu

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=svalon95@gmail.com
MAIL_PASSWORD=app_password_këtu
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="svalon95@gmail.com"
MAIL_FROM_NAME="AronTrade"
```

#### Gjenerimi i APP_KEY:
```bash
php artisan key:generate
```

---

### 5. Instalimi i Dependencies dhe Build

```bash
# Instalimi i PHP dependencies (pa dev dependencies)
composer install --no-dev --optimize-autoloader

# Instalimi i Node dependencies
npm install

# Build i frontend assets për production
npm run build

# Opsionale: Optimizimi i autoloader
composer dump-autoload --optimize
```

---

### 6. Migracionet dhe Seeders

```bash
# Ekzekutimi i migracioneve
php artisan migrate --force

# Ekzekutimi i seeders (vetëm nëse dëshiron të kesh të dhëna test)
php artisan db:seed --force
```

**Shënim**: Nëse admini ekziston tashmë, mund të përdorësh seeders ose të krijosh manualisht:
```sql
UPDATE users SET role = 'admin', is_admin = 1 WHERE email = 'svalon95@gmail.com';
```

---

### 7. Konfigurimi i Permissions

```bash
# Permissions për storage dhe cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Permissions për public directory
sudo chown -R www-data:www-data public
```

---

### 8. Optimizimi i Laravel për Production

```bash
# Cache i config
php artisan config:cache

# Cache i routes
php artisan route:cache

# Cache i views
php artisan view:cache

# Cache i events
php artisan event:cache
```

**Shënim**: Nëse bën ndryshime në config/routes/views, ekzekuto:
```bash
php artisan optimize:clear
```

---

### 9. Konfigurimi i Web Server

### Nginx Configuration

Krijo skedarin `/etc/nginx/sites-available/gastrotrade`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name domaini-yt.com www.domini-yt.com;
    
    # Redirect në HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name domaini-yt.com www.domini-yt.com;
    
    root /var/www/gastrotrade/public;
    index index.php;

    # SSL Configuration (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/domini-yt.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/domini-yt.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;

    # Laravel Public Directory
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Aktivizo site-in:
```bash
sudo ln -s /etc/nginx/sites-available/gastrotrade /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache Configuration

Nëse përdor Apache, sigurohu që `.htaccess` ekziston në `public/` dhe mod_rewrite është aktiv.

---

### 10. SSL Certificate (Let's Encrypt)

```bash
# Instalimi i Certbot
sudo apt install certbot python3-certbot-nginx

# Marrja e SSL certificate
sudo certbot --nginx -d domaini-yt.com -d www.domini-yt.com

# Auto-renewal (tashmë konfiguruar automatikisht)
sudo certbot renew --dry-run
```

---

### 11. Konfigurimi i Queue Worker (Opsionale)

Nëse përdor queues, konfiguro supervisor:

```bash
sudo apt install supervisor
```

Krijo `/etc/supervisor/conf.d/gastrotrade-worker.conf`:

```ini
[program:gastrotrade-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gastrotrade/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/gastrotrade/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start gastrotrade-worker:*
```

---

### 12. Backup dhe Monitoring

#### Backup i Database (Cron Job):
```bash
# Krijo skedarin backup.sh
nano /var/www/gastrotrade/backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/gastrotrade"
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u gastrotrade_user -p'password' gastrotrade > $BACKUP_DIR/db_$DATE.sql

# Backup storage
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/gastrotrade/storage/app

# Fshi backup-et më të vjetra se 7 ditë
find $BACKUP_DIR -type f -mtime +7 -delete
```

```bash
chmod +x /var/www/gastrotrade/backup.sh

# Shto në crontab (çdo ditë në 2:00 AM)
crontab -e
# Shto:
0 2 * * * /var/www/gastrotrade/backup.sh
```

---

## 🔄 Update i Aplikacionit

Kur bën update në kod:

```bash
cd /var/www/gastrotrade

# Pull changes nga Git
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear dhe rebuild cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers (nëse ka)
sudo supervisorctl restart gastrotrade-worker:*
```

---

## ✅ Checklist përpara Launch

- [ ] `.env` është konfiguruar për production
- [ ] `APP_DEBUG=false` në `.env`
- [ ] `APP_ENV=production` në `.env`
- [ ] SSL certificate është instaluar dhe funksionon
- [ ] Database është krijuar dhe migracionet janë ekzekutuar
- [ ] Frontend assets janë build-uar (`npm run build`)
- [ ] Permissions për storage dhe cache janë konfiguruar
- [ ] Laravel cache është optimizuar
- [ ] Web server (Nginx/Apache) është konfiguruar
- [ ] Queue workers janë konfiguruar (nëse nevojiten)
- [ ] Backup system është konfiguruar
- [ ] Email configuration është testuar
- [ ] Admin user është krijuar dhe mund të kyçet
- [ ] Test i aplikacionit në production URL

---

## 🐛 Troubleshooting

### Problemet e Zakonshme:

1. **500 Error**: Kontrollo permissions dhe logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Assets nuk ngarkohen**: Sigurohu që `npm run build` është ekzekutuar dhe `public/build` ekziston.

3. **Database Connection Error**: Kontrollo `.env` dhe sigurohu që database user ka permissions.

4. **Permission Denied**: 
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

---

## 📞 Kontakt për Support

Nëse ke probleme gjatë deployment, kontakto:
- Email: svalon95@gmail.com
- Telefon: 048 75 66 46 / 044 82 43 14

---

**Shënim**: Ky dokument duhet të përditësohet bazuar në nevojat specifike të projektit dhe infrastrukturës së serverit.
