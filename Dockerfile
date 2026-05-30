FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip libssl-dev \
    pkg-config libpng-dev libonig-dev curl \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

RUN pecl install mongodb-1.21.0 \
    && docker-php-ext-enable mongodb

RUN docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod
ENV APP_SECRET=changeme

RUN composer install \
    --no-interaction --prefer-dist \
    --no-dev --optimize-autoloader

RUN php bin/console tailwind:build --minify || true
RUN php bin/console asset-map:compile || true
RUN php bin/console cache:warmup --env=prod || true

CMD ["sh", "-c", "php -S 0.0.0.0:$PORT -t public"]