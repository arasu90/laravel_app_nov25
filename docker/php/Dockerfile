FROM php:8.5-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libzip-dev \
        zip \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the application first, including artisan
COPY . /var/www

# Install PHP dependencies
RUN composer install \
    --no-interaction \
    --prefer-dist

# Configure OPcache
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

CMD ["php-fpm"]