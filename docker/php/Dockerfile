FROM php:8.3-fpm

# ADD NON-ROOT USER; IF THE VARIABLES ARE NOT SET, DOCKER COMPOSE DEFAULTS TO 1000:1000 "docker"

ARG UID
ARG GID
ARG UNAME

RUN groupadd -g ${GID} -o ${UNAME}

RUN usermod -a -G ${UNAME} www-data

RUN useradd -m -u ${UID} -g ${GID} -o -s /bin/bash ${UNAME}

# INSTALL DEPENDENCIES

ENV BUILD_DEPS g++ build-essential libsasl2-dev libssl-dev

ENV RUN_DEPS libzip-dev libicu-dev git wget vim curl less gnupg zlib1g-dev libpng-dev libjpeg-dev libonig-dev

# INIT (single command so the intermediates are not stored)

RUN apt-get update && apt-get install -y ${BUILD_DEPS} ${RUN_DEPS} \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) pdo_mysql bcmath mbstring zip intl sockets pcntl gd \
    && apt-get purge \
        -y --auto-remove \
        -o APT::AutoRemove::RecommendsImportant=false \
        ${BUILD_DEPS}

# PHP

ADD log.conf /usr/local/etc/php-fpm.d/zz-log.conf

# OPCache

RUN docker-php-ext-install opcache

COPY opcache.ini /tmp/opcache.ini

RUN cat /tmp/opcache.ini >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# XDEBUG

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY xdebug.ini /tmp/xdebug.ini

RUN cat /tmp/xdebug.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# COMPOSER

ENV COMPOSER_HOME /composer

ENV PATH /composer/vendor/bin:$PATH

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir -p /composer/cache && chown -R ${UID}:${GID} /composer/cache

# NODEJS + YARN

ENV PATH /root/.yarn/bin:$PATH

ENV YARN_CACHE_FOLDER /yarn

RUN rm -rf /var/lib/apt/lists/*

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -

RUN apt-get install -y nodejs

RUN npm install --global yarn@v2

RUN mkdir -p ${YARN_CACHE_FOLDER} && chmod -R 777 ${YARN_CACHE_FOLDER}

# VOLUME

VOLUME /var/www/html

RUN chmod 777 /var/www/html
