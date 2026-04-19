FROM php:8.3-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libpq \
    libsodium-dev \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    opcache

# Install Xdebug for coverage
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug sodium

# Xdebug config for coverage
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=no" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Create keys directory
RUN mkdir -p /var/www/keys && chmod 700 /var/www/keys

# Create non-root user
RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

# Change ownership
RUN chown -R www:www /var/www

# Switch to non-root user
USER www

EXPOSE 9000
CMD ["php-fpm"]
