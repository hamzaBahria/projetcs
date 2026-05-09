#!/bin/sh
set -e

echo "=== Checking PHP ==="
php -v
php -m | grep -E "pdo|mbstring|xml|gd|openssl" || true

echo "=== Checking DB config ==="
echo "DB_CONNECTION=$DB_CONNECTION"
echo "DATABASE_URL=${DATABASE_URL:+set}"
echo "DB_HOST=$DB_HOST"

echo "=== Checking APP_KEY ==="
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "ERROR: APP_KEY is not set"
    exit 1
fi
echo "APP_KEY is set"

echo "=== Checking storage permissions ==="
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "=== Clearing cached config ==="
php artisan config:clear 2>/dev/null || true

echo "=== Caching config ==="
php artisan config:cache 2>&1 || echo "Warning: config:cache failed"

echo "=== Caching routes ==="
php artisan route:cache 2>&1 || echo "Warning: route:cache failed"

echo "=== Caching views ==="
php artisan view:cache 2>&1 || echo "Warning: view:cache failed"

echo "=== Storage link ==="
php artisan storage:link --force 2>&1 || true

echo "=== Waiting for database ==="
for i in 1 2 3 4 5; do
    if php artisan migrate --pretend --force 2>/dev/null; then
        echo "Database connected on attempt $i"
        break
    fi
    echo "Waiting for database... attempt $i"
    sleep 3
done

echo "=== Running migrations ==="
if php artisan migrate --force 2>&1; then
    echo "Migrations ran successfully"
else
    echo "Warning: Migrations failed (DB might not be ready)"
fi

echo "=== Testing app ==="
php artisan route:list 2>&1 | head -20 || echo "Warning: route list failed"

echo "=== Starting services ==="
exec supervisord -c /etc/supervisord.conf
