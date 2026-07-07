FROM php:8.4-fpm-alpine

# Install nginx, supervisor, and required PHP extensions
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip gd opcache

# Install Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# Ensure correct permissions on writable directories
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/bin/sh", "/var/www/html/docker/start.sh"]
