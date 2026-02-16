#!/bin/bash

# GastroTrade Backup Script
# Ekzekuto këtë skript për backup të database dhe storage

set -e

# Konfigurim
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="${BACKUP_DIR:-/var/backups/gastrotrade}"
PROJECT_DIR="${PWD:-/var/www/gastrotrade}"
RETENTION_DAYS=7

# Ngjyra
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo "💾 Duke filluar backup për GastroTrade..."

# Krijoni backup directory nëse nuk ekziston
mkdir -p "$BACKUP_DIR"

# Lexo database credentials nga .env
if [ ! -f "$PROJECT_DIR/.env" ]; then
    echo -e "${RED}❌ .env nuk u gjet!${NC}"
    exit 1
fi

DB_CONNECTION=$(grep "^DB_CONNECTION=" "$PROJECT_DIR/.env" | cut -d '=' -f2)
DB_HOST=$(grep "^DB_HOST=" "$PROJECT_DIR/.env" | cut -d '=' -f2)
DB_PORT=$(grep "^DB_PORT=" "$PROJECT_DIR/.env" | cut -d '=' -f2)
DB_DATABASE=$(grep "^DB_DATABASE=" "$PROJECT_DIR/.env" | cut -d '=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" "$PROJECT_DIR/.env" | cut -d '=' -f2)
DB_PASSWORD=$(grep "^DB_PASSWORD=" "$PROJECT_DIR/.env" | cut -d '=' -f2)

# Backup Database
echo -e "${YELLOW}🗄️  Duke backup-uar database...${NC}"

if [ "$DB_CONNECTION" = "mysql" ]; then
    mysqldump -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_DIR/db_$DATE.sql"
    echo -e "${GREEN}✅ Database backup u krijua: db_$DATE.sql${NC}"
elif [ "$DB_CONNECTION" = "pgsql" ]; then
    export PGPASSWORD="$DB_PASSWORD"
    pg_dump -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" -d "$DB_DATABASE" > "$BACKUP_DIR/db_$DATE.sql"
    echo -e "${GREEN}✅ Database backup u krijua: db_$DATE.sql${NC}"
else
    echo -e "${YELLOW}⚠️  Database connection $DB_CONNECTION nuk mbështetet në këtë skript${NC}"
fi

# Backup Storage
echo -e "${YELLOW}📁 Duke backup-uar storage...${NC}"
if [ -d "$PROJECT_DIR/storage/app" ]; then
    tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" -C "$PROJECT_DIR" storage/app
    echo -e "${GREEN}✅ Storage backup u krijua: storage_$DATE.tar.gz${NC}"
fi

# Backup .env
echo -e "${YELLOW}⚙️  Duke backup-uar .env...${NC}"
cp "$PROJECT_DIR/.env" "$BACKUP_DIR/env_$DATE.env"
echo -e "${GREEN}✅ .env backup u krijua: env_$DATE.env${NC}"

# Fshi backup-et e vjetra
echo -e "${YELLOW}🧹 Duke fshirë backup-et më të vjetra se $RETENTION_DAYS ditë...${NC}"
find "$BACKUP_DIR" -type f -mtime +$RETENTION_DAYS -delete
echo -e "${GREEN}✅ Backup-et e vjetra u fshinë${NC}"

# Shfaq informacion
echo ""
echo -e "${GREEN}✅✅✅ Backup u përfundua me sukses!${NC}"
echo ""
echo "Backup-et janë në: $BACKUP_DIR"
echo ""
ls -lh "$BACKUP_DIR" | tail -n +2
