# Stage 1
FROM composer:latest AS vendor

WORKDIR /app
COPY . /app
RUN composer install --no-interaction --prefer-dist

# Stage 2
FROM php:8.2.4-cli

WORKDIR /app
RUN apt-get update && apt-get install -y zip unzip git curl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=vendor /app /app
