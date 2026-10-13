FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql mysqli zip

RUN pecl install redis && docker-php-ext-enable redis

RUN a2enmod rewrite

# Copiar configuración de PHP para SMTP
COPY php.ini /usr/local/etc/php/php.ini

# Copiar el código fuente
COPY src/ /var/www/html/

WORKDIR /var/www/html
