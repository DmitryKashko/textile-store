FROM php:8.3-fpm-alpine

RUN apk add --no-cache icu-dev libzip-dev \
    && docker-php-ext-install pdo_mysql intl zip opcache bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
