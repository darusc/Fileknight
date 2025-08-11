FROM php:8.4-fpm-alpine AS base

ARG UID
ARG GID

WORKDIR /srv/app

# Build dependencies
RUN set -eu; apk add --no-cache --virtual .build-deps $PHPIZE_DEPS

# Install packages + PHP extensions
RUN set -eu; \
    apk add --no-cache bash git curl nano \
        libxml2-dev oniguruma-dev icu-dev zlib-dev libzip-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev sqlite-dev postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mbstring bcmath calendar exif gd intl pcntl \
        pdo pdo_mysql pdo_pgsql pdo_sqlite zip bz2 dom simplexml opcache \
    && pecl install ds \
    && docker-php-ext-enable ds

# Clean up
RUN set -eu; apk del .build-deps && rm -rf /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP config
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/

# User setup
RUN addgroup -S www-user -g $GID && \
    adduser -S www-user -G www-user -u $UID

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

################################
# PHP-FPM image
################################
FROM base AS php-fpm
COPY docker/php/php-fpm.d/website.conf /usr/local/etc/php-fpm.d/website.conf
RUN mkdir /var/run/php && chown -R www-user:www-user /var/run/php
USER www-user
ENTRYPOINT ["php-fpm"]