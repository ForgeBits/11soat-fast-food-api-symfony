FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libicu-dev libzip-dev zlib1g-dev libpq-dev \
 && docker-php-ext-install intl zip pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /var/www/html

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
USER www-data
