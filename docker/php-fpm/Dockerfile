FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libavif-dev \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        unzip \
    && docker-php-ext-configure gd \
        --with-avif \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        curl \
        dom \
        exif \
        gd \
        mbstring \
        opcache \
        pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php-fpm/app.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html
