FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y --no-install-recommends git unzip libzip-dev \
  && docker-php-ext-install pdo pdo_mysql zip \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json .env.example ./
COPY src/helpers.php ./src/helpers.php
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
