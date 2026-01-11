FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libicu-dev libzip-dev zlib1g-dev libpq-dev libcurl4-openssl-dev \
    $PHPIZE_DEPS \
 && docker-php-ext-install intl zip pdo pdo_pgsql curl \
 && pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && apt-get purge -y --auto-remove $PHPIZE_DEPS \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/php.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www/html

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
USER www-data
