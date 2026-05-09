FROM php:8.3-fpm-alpine AS build

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    unzip \
    libzip-dev \
    oniguruma-dev \
    nodejs \
    npm

RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build
RUN chown -R www-data:www-data storage bootstrap/cache

COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/supervisord.conf /etc/supervisord.conf

EXPOSE 8080

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
