FROM alpine:3.4

RUN addgroup php && adduser -s /bin/bash -D -G php php

RUN echo "http://dl-4.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories && \
    apk --update add \
        curl \
        git \
        openssh-client \
        php7 \
        php7-curl \
        php7-dom \
        php7-fpm \
        php7-json \
        php7-mbstring \
        php7-opcache \
        php7-openssl \
        php7-phar \
        php7-redis \
        php7-xdebug \
        php7-xml \
        php7-zip \
    && rm -rf /var/cache/apk/*

ENV LANG       en_GB.UTF-8
ENV LC_ALL     en_GB.UTF-8

RUN ln -s /usr/bin/php7 /usr/bin/php

RUN cd /tmp \
    && curl -sS https://getcomposer.org/installer | php \
    && mv -f /tmp/composer.phar /usr/local/bin/composer \
    && cd /

RUN chown -R php:php /tmp \
    && mkdir -p /var/log \
    && chown -R php:php /var/log

EXPOSE 9000

USER php

RUN mkdir /home/php/.composer

CMD ["php-fpm7", "-F"]
