FROM alpine:3.21

LABEL maintainer="Taylor Otwell"

ARG WWWGROUP=1000
ARG NODE_VERSION=24
ARG MYSQL_CLIENT="mysql-client"
ARG POSTGRES_VERSION=17
ARG WWWUSER=1000

WORKDIR /var/www/html

ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php84 -d variables_order=EGPCS /var/www/html/artisan octane:start --server=frankenphp --host=0.0.0.0 --port=80 --admin-port=2019"
ENV SUPERVISOR_PHP_USER="sail"

# Install base and PHP dependencies
RUN apk add --no-cache \
    tzdata \
    gnupg \
    su-exec \
    curl \
    ca-certificates \
    zip \
    unzip \
    git \
    supervisor \
    sqlite \
    libcap \
    libpng-dev \
    python3 \
    bind-tools \
    ffmpeg \
    nano \
    php84 \
    php84-cli \
    php84-dev \
    php84-pgsql \
    php84-pdo_pgsql \
    php84-sqlite3 \
    php84-pdo_sqlite \
    php84-gd \
    php84-curl \
    php84-mbstring \
    php84-xml \
    php84-xmlwriter \
    php84-xmlreader \
    php84-simplexml \
    php84-zip \
    php84-bcmath \
    php84-soap \
    php84-sodium \
    php84-intl \
    php84-pcntl \
    php84-posix \
    php84-redis \
    php84-pecl-swoole \
    php84-phar \
    php84-openssl \
    php84-iconv \
    php84-dom \
    php84-fileinfo \
    php84-tokenizer \
    php84-session \
    php84-ctype \
    nodejs \
    npm \
    postgresql-client

# Link php84 to php
RUN ln -s /usr/bin/php84 /usr/bin/php

# Configure Timezone
RUN cp /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install Composer
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Set Capabilities for PHP
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php84

# Setup User and Group
RUN addgroup -g ${WWWGROUP} sail || true \
    && adduser -D -G sail -u ${WWWUSER} -s /bin/sh sail || true \
    && git config --global --add safe.directory /var/www/html \
    && mkdir -p /var/log/supervisor /run

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php84/conf.d/99-sail.ini

RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
