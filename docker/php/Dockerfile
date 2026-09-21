FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    curl \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install \
        pdo_mysql \
        zip \
        opcache \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies first for better Docker caching
COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist

# Copy application
COPY . /var/www

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

CMD ["php-fpm"]
