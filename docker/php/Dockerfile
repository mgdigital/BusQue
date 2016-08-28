FROM php:7-alpine

RUN apk --update add \
        autoconf \
        build-base \
        curl \
        git \
        libcurl \
        libxml2-dev \
        openssh-client \
        zlib-dev \
    && rm -rf /var/cache/apk/*

RUN docker-php-ext-install \
        curl \
        dom \
        opcache \
        phar \
        xml \
        zip

RUN pecl install \
        xdebug \
    &&  rm -rf /tmp/pear \
    && docker-php-ext-enable \
        xdebug

RUN git clone -b php7 https://github.com/phpredis/phpredis.git /tmp/phpredis \
    && cd /tmp/phpredis \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini \
    && cd / \
    && rm -rf /tmp/phpredis

ENV LANG       en_GB.UTF-8
ENV LC_ALL     en_GB.UTF-8

RUN cd /tmp \
    && curl -sS https://getcomposer.org/installer | php \
    && mv -f /tmp/composer.phar /usr/local/bin/composer \
    && cd /

RUN addgroup php && adduser -s /bin/bash -D -G php php

RUN chown -R php:php /tmp \
    && mkdir -p /var/log \
    && chown -R php:php /var/log

USER php

RUN mkdir /home/php/.composer

CMD ["tail", "-f", "/dev/null"]
