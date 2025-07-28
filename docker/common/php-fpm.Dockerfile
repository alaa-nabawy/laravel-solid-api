# Multi-stage Dockerfile for Laravel PHP-FPM
# This Dockerfile supports both development and production builds

# Base PHP image with FPM
FROM php:8.2-fpm-alpine AS base

# Install runtime dependencies
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    # Runtime libraries for PHP extensions
    libpng \
    libjpeg-turbo \
    freetype \
    libxml2 \
    postgresql-libs \
    oniguruma \
    icu-libs

# Install build dependencies and PHP extensions in one optimized layer
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    postgresql-dev \
    oniguruma-dev \
    icu-dev \
    autoconf \
    g++ \
    make \
    linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        intl \
        opcache \
        gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create non-root user
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Development stage
FROM base AS development

# Install Xdebug for development with required headers
RUN apk add --no-cache --virtual .xdebug-deps linux-headers autoconf g++ make \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .xdebug-deps \
    && rm -rf /tmp/pear

# Copy Xdebug configuration
COPY docker/dev/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Copy PHP configuration for development
COPY docker/dev/php.ini /usr/local/etc/php/php.ini
COPY docker/dev/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Set permissions for development (more permissive)
RUN chown -R www:www /var/www/html

USER www

EXPOSE 9000

CMD ["php-fpm"]

# Production build stage
FROM base AS builder

# Install Node.js for asset compilation
RUN apk add --no-cache nodejs npm

# Copy and install composer dependencies first (better caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

# Copy and install npm dependencies
COPY package.json package-lock.json* ./
RUN npm ci --only=production

# Copy application code
COPY . .

# Build assets
RUN npm run build && npm cache clean --force

# Production stage
FROM base AS production

# Copy PHP configuration for production
COPY docker/prod/php.ini /usr/local/etc/php/php.ini
COPY docker/prod/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy application from builder
COPY --from=builder --chown=www:www /var/www/html /var/www/html

# Remove unnecessary files for production
RUN rm -rf \
    /var/www/html/tests \
    /var/www/html/storage/logs/* \
    /var/www/html/.env.example \
    /var/www/html/README.md \
    /var/www/html/docker \
    /var/www/html/node_modules

# Set proper permissions
RUN chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

USER www

EXPOSE 9000

CMD ["php-fpm"]
