# Base image
FROM php:8.3.7-fpm-alpine

# Install system dependencies in optimized layers
RUN apk add --no-cache --virtual .build-deps \
        autoconf \
        g++ \
        make \
        linux-headers \
    && apk add --no-cache \
        bash \
        git \
        sudo \
        openssh \
        libxml2-dev \
        oniguruma-dev \
        npm \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        ssmtp \
        icu-dev \
    && apk upgrade --no-cache

# Install PHP extensions (grouped by similar operations)
RUN pecl channel-update pecl.php.net \
    && pecl install pcov swoole \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        mbstring \
        xml \
        pcntl \
        gd \
        zip \
        sockets \
        pdo \
        pdo_mysql \
        bcmath \
        soap \
        mysqli \
        intl \
    && docker-php-ext-enable \
        mbstring \
        xml \
        gd \
        zip \
        pcov \
        pcntl \
        sockets \
        bcmath \
        pdo \
        pdo_mysql \
        soap \
        swoole

# Install Composer and RoadRunner
RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin --filename=composer \
    && curl -L https://github.com/roadrunner-server/roadrunner/releases/download/v2.4.2/rr_linux_amd64 -o /usr/bin/rr \
    && chmod +x /usr/bin/rr

# Clean up build dependencies
RUN apk del .build-deps

# Set working directory
WORKDIR /app

# Copy composer files first to leverage Docker cache
COPY composer.* ./

# Install composer dependencies (cache this layer separately)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copy the rest of the application
COPY . .

# Complete composer installation
RUN composer dump-autoload --optimize \
    && composer require spiral/roadrunner --no-interaction \
    && mkdir -p /app/storage/logs

# Copy entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["/usr/local/bin/docker-entrypoint.sh"]
EXPOSE 8002
