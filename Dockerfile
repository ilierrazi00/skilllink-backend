FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    libssl-dev \
    pkg-config

RUN pecl install mongodb-1.21.5 \
    && docker-php-ext-enable mongodb

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-interaction --prefer-dist

COPY . .

RUN ls -la vendor
RUN ls -la vendor/autoload_runtime.php

CMD php -S 0.0.0.0:${PORT:-8000} -t public