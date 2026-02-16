#!/bin/bash

# GastroTrade Update Script
# Ekzekuto këtë skript kur bën update në kod

set -e

echo "🔄 Duke filluar update për GastroTrade..."

# Ngjyra për output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Kontrollo nëse jemi në directory-n e duhur
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Gabim: Kjo nuk duket të jetë një projekt Laravel!${NC}"
    exit 1
fi

# 1. Pull changes nga Git (nëse përdor Git)
if [ -d ".git" ]; then
    echo -e "${YELLOW}📥 Duke marrë ndryshimet nga Git...${NC}"
    git pull origin main || git pull origin master
    echo -e "${GREEN}✅ Git pull u krye${NC}"
else
    echo -e "${YELLOW}⚠️  Nuk u gjet repository Git. Duke vazhduar...${NC}"
fi

# 2. Backup .env (sigurohu që nuk humbet)
if [ -f ".env" ]; then
    cp .env .env.backup
    echo -e "${GREEN}✅ .env u backup-ua${NC}"
fi

# 3. Update PHP dependencies
echo -e "${YELLOW}📦 Duke përditësuar PHP dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✅ PHP dependencies u përditësuan${NC}"

# 4. Update Node dependencies
echo -e "${YELLOW}📦 Duke përditësuar Node dependencies...${NC}"
npm install
echo -e "${GREEN}✅ Node dependencies u përditësuan${NC}"

# 5. Rebuild frontend assets
echo -e "${YELLOW}🔨 Duke rebuild-uar frontend assets...${NC}"
npm run build
echo -e "${GREEN}✅ Frontend assets u rebuild-uan${NC}"

# 6. Run migrations
echo -e "${YELLOW}🗄️  Duke ekzekutuar migracionet...${NC}"
php artisan migrate --force
echo -e "${GREEN}✅ Migracionet u ekzekutuan${NC}"

# 7. Clear dhe rebuild cache
echo -e "${YELLOW}🧹 Duke pastruar cache...${NC}"
php artisan optimize:clear
echo -e "${GREEN}✅ Cache u pastrua${NC}"

echo -e "${YELLOW}⚡ Duke optimizuar Laravel...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo -e "${GREEN}✅ Laravel u optimizua${NC}"

# 8. Restart queue workers (nëse ka)
if command -v supervisorctl &> /dev/null; then
    echo -e "${YELLOW}🔄 Duke restart-uar queue workers...${NC}"
    sudo supervisorctl restart gastrotrade-worker:* || echo -e "${YELLOW}⚠️  Queue workers nuk u gjetën ose nuk janë konfiguruar${NC}"
fi

echo ""
echo -e "${GREEN}✅✅✅ Update u përfundua me sukses!${NC}"
echo ""
echo "Aplikacioni është i përditësuar dhe gati për përdorim."
