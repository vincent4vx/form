ARG PHP_VERSION=8.1.3
FROM php:${PHP_VERSION}-cli

RUN mkdir /form
WORKDIR /form

RUN apt-get update && apt-get install -y libzip-dev
RUN docker-php-ext-install -j$(nproc) opcache zip

COPY php.ini /usr/local/etc/php/conf.d/php.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

VOLUME /form
