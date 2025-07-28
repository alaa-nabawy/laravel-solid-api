FROM php:8.2-cli-alpine

# Install runtime dependencies and Node.js in one layer
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    bash \
    vim \
    nano \
    htop \
    sudo \
    nodejs \
    npm \
    # Runtime libraries for PHP extensions
    libpng \
    libjpeg-turbo \
    freetype \
    libxml2 \
    postgresql-libs \
    oniguruma \
    icu-libs

# Install build dependencies, PHP extensions, and Xdebug in one optimized layer
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
        gd \
    && pecl install xdebug redis \
    && docker-php-ext-enable xdebug redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install global Composer packages
RUN composer global require laravel/installer laravel/pint

# Install global npm packages as root
RUN npm install -g vite concurrently

# Create non-root user and configure sudo
RUN addgroup -g 1000 -S sail && \
    adduser -u 1000 -S sail -G sail -s /bin/bash && \
    echo 'sail ALL=(ALL) NOPASSWD: /bin/chown' >> /etc/sudoers

# Set working directory
WORKDIR /var/www/html

# Copy Xdebug configuration
COPY docker/dev/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Copy workspace PHP configuration
COPY docker/dev/workspace-php.ini /usr/local/etc/php/php.ini

# Set up bash aliases and environment
RUN echo 'alias ll="ls -la"' >> /home/sail/.bashrc && \
    echo 'alias artisan="php artisan"' >> /home/sail/.bashrc && \
    echo 'alias tinker="php artisan tinker"' >> /home/sail/.bashrc && \
    echo 'alias test="php artisan test"' >> /home/sail/.bashrc && \
    echo 'alias pint="./vendor/bin/pint"' >> /home/sail/.bashrc && \
    echo 'export PATH="$PATH:$HOME/.composer/vendor/bin"' >> /home/sail/.bashrc

# Set permissions and create npm cache directory with proper ownership
RUN chown -R sail:sail /var/www/html && \
    mkdir -p /home/sail/.npm && \
    chown -R sail:sail /home/sail/.npm && \
    chmod -R 755 /home/sail/.npm

USER sail

# Keep container running
CMD ["tail", "-f", "/dev/null"]
