#!/bin/sh
set -e

PORT="${PORT:-10000}"
sed -i "s/listen 10000;/listen ${PORT};/" /etc/nginx/http.d/default.conf

mkdir -p \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache \
  public/images/Uploads

chown -R www-data:www-data storage bootstrap/cache public/images/Uploads 2>/dev/null || true
chmod -R 775 storage bootstrap/cache public/images/Uploads 2>/dev/null || true

if [ -n "${APP_KEY}" ]; then
  php artisan migrate --force --no-interaction || true
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
