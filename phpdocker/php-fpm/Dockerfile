FROM php:fpm
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        librabbitmq-dev \
        supervisor \
        libpq-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
    && pecl install amqp \
    && docker-php-ext-enable amqp

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

WORKDIR "/app"