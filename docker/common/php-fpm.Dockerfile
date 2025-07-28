# Multi-stage Dockerfile for Laravel PHP-FPM
# This Dockerfile supports both development and production builds

# Base PHP image with FPM
FROM php:8.2-fpm-alpine AS base

# Install runtime dependencies
RUN apk add --no-cache \
    git=2.45.2-r0 \
    curl=8.10.1-r0 \
    zip=3.0-r12 \
    unzip=6.0-r14 \
    # Runtime libraries for PHP extensions
    libpng=1.6.43-r0 \
    libjpeg-turbo=3.0.2-r0 \
    freetype=2.13.2-r0 \
    libxml2=2.12.7-r0 \
    postgresql-libs=16.4-r0 \
    oniguruma=6.9.9-r0 \
    icu-libs=74.2-r0

# Install build dependencies and PHP extensions in one optimized layer
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev=1.6.43-r0 \
    libjpeg-turbo-dev=3.0.2-r0 \
    freetype-dev=2.13.2-r0 \
    libxml2-dev=2.12.7-r0 \
    postgresql-dev=16.4-r0 \
    oniguruma-dev=6.9.9-r0 \
    icu-dev=74.2-r0 \
    autoconf=2.72-r0 \
    g++=13.2.1_git20240309-r0 \
    make=4.4.1-r2 \
    linux-headers=6.6-r0 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
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
RUN apk add --no-cache --virtual .xdebug-deps linux-headers=6.6-r0 autoconf=2.72-r0 g++=13.2.1_git20240309-r0 make=4.4.1-r2 \
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
RUN apk add --no-cache nodejs=20.15.1-r0 npm=10.8.0-r0

# Copy and install composer dependencies first (better caching)
COPY composer.json composer.lock ./
# Copy essential Laravel files needed for post-autoload-dump script
COPY artisan ./
COPY bootstrap/ ./bootstrap/
COPY config/ ./config/
COPY app/ ./app/
COPY routes/ ./routes/
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

# Copy and install npm dependencies (including dev dependencies for build)
COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

# Copy vite config and resources needed for build
COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

# Copy remaining application code
COPY . .

# Build assets and clean up
RUN npm run build && npm prune --omit=dev && npm cache clean --force

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
RUN chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

USER www

EXPOSE 9000

CMD ["php-fpm"]
