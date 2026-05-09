#!/bin/bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan storage:link
php artisan view:cache
