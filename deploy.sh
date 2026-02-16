#!/bin/bash

# GastroTrade Deployment Script
# Ekzekuto këtë skript pasi të kesh uploaduar kodet në server

set -e

echo "🚀 Duke filluar deployment për GastroTrade..."

# Ngjyra për output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Kontrollo nëse jemi në directory-n e duhur
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Gabim: Kjo nuk duket të jetë një projekt Laravel!${NC}"
    echo "Sigurohu që je në directory-n e projektit."
    exit 1
fi

# 1. Kontrollo nëse .env ekziston
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️  .env nuk ekziston. Duke krijuar nga .env.example...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✅ .env u krijua. Ju lutem konfigurojeni atë para se të vazhdoni!${NC}"
    echo "Hap .env dhe vendos:"
    echo "  - APP_ENV=production"
    echo "  - APP_DEBUG=false"
    echo "  - APP_URL=https://domaini-yt.com"
    echo "  - Database credentials"
    read -p "Shtyp Enter pasi të kesh konfiguruar .env..."
fi

# 2. Gjenero APP_KEY nëse nuk ekziston
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "${YELLOW}⚠️  APP_KEY nuk është vendosur. Duke gjeneruar...${NC}"
    php artisan key:generate
    echo -e "${GREEN}✅ APP_KEY u gjenerua${NC}"
fi

# 3. Instalo PHP dependencies
echo -e "${YELLOW}📦 Duke instaluar PHP dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✅ PHP dependencies u instaluan${NC}"

# 4. Instalo Node dependencies
echo -e "${YELLOW}📦 Duke instaluar Node dependencies...${NC}"
npm install
echo -e "${GREEN}✅ Node dependencies u instaluan${NC}"

# 5. Build frontend assets
echo -e "${YELLOW}🔨 Duke build-uar frontend assets...${NC}"
npm run build
echo -e "${GREEN}✅ Frontend assets u build-uan${NC}"

# 6. Ekzekuto migracionet
echo -e "${YELLOW}🗄️  Duke ekzekutuar migracionet...${NC}"
read -p "A dëshiron të ekzekutosh migracionet? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo -e "${GREEN}✅ Migracionet u ekzekutuan${NC}"
else
    echo -e "${YELLOW}⏭️  Migracionet u kaluan${NC}"
fi

# 7. Konfiguro permissions
echo -e "${YELLOW}🔐 Duke konfiguruar permissions...${NC}"
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✅ Permissions u konfiguruan${NC}"

# 8. Optimizo Laravel
echo -e "${YELLOW}⚡ Duke optimizuar Laravel...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo -e "${GREEN}✅ Laravel u optimizua${NC}"

# 9. Link storage nëse nevojitet
if [ ! -L "public/storage" ]; then
    echo -e "${YELLOW}🔗 Duke krijuar symbolic link për storage...${NC}"
    php artisan storage:link
    echo -e "${GREEN}✅ Storage link u krijua${NC}"
fi

echo ""
echo -e "${GREEN}✅✅✅ Deployment u përfundua me sukses!${NC}"
echo ""
echo "Hapat e mbetur:"
echo "1. Sigurohu që web server (Nginx/Apache) është konfiguruar"
echo "2. Kontrollo permissions për storage dhe cache"
echo "3. Testo aplikacionin në browser"
echo "4. Konfiguro backup dhe monitoring"
echo ""
echo "Për më shumë informacion, shiko DEPLOYMENT.md"
