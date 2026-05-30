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

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-dev \
    --optimize-autoloader

RUN test -f vendor/autoload_runtime.php && echo "FOUND"

COPY . .

RUN ls -la vendor
RUN ls -la vendor/autoload_runtime.php

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]