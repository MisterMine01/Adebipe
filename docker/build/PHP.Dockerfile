FROM php:8.2-fpm-alpine3.19

ARG WITH_XDEBUG=true

RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev && \
    docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
    docker-php-ext-install -j$(nproc) gd && \
    apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev

RUN if [ ${WITH_XDEBUG} = "true" ] ; then \
    apk add --no-cache ${PHPIZE_DEPS} linux-headers && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug; \
    fi;

RUN docker-php-ext-install mysqli pdo pdo_mysql