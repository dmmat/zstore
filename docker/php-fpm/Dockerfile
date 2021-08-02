FROM phpdockerio/php74-fpm:latest
WORKDIR "/www"

RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        git \
        php7.4-bz2 \
        php7.4-gmp \
        php7.4-gd \
        php7.4-imagick \
        php7.4-memcached \
        php7.4-xsl \
        php7.4-mysql; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


