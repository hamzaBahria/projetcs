#!/bin/sh
set -e

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "ERROR: APP_KEY is not set"
    exit 1
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

if php artisan migrate --force 2>/dev/null; then
    echo "Migrations ran successfully"
else
    echo "Warning: Migrations failed (DB might not be ready)"
fi

exec supervisord -c /etc/supervisord.conf
