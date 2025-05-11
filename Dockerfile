FROM php:8.2.4-cli

WORKDIR /app

RUN apt-get update && apt-get install -y zip unzip git \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /app

RUN composer install --no-interaction --optimize-autoloader
