FROM --platform=linux/amd64 ubuntu:20.04
 
# php 7.4
RUN apt-get update && \
    apt-get upgrade -y --no-install-recommends --no-install-suggests && \
    apt-get install software-properties-common -y --no-install-recommends --no-install-suggests && \
    apt-get update && \
    apt-get install php7.4-fpm php7.4-cli -y --no-install-recommends --no-install-suggests

RUN apt-get update && \
    apt-get install -y --no-install-recommends --no-install-suggests \
    nginx \
    ca-certificates \
    gettext \
    mc \
    libmcrypt-dev  \
    libicu-dev \
    libcurl4-openssl-dev \
    mysql-client \
    libldap2-dev \
    libfreetype6-dev \
    libfreetype6 \
    libpcre3-dev  \
    curl \
    libpcsclite-dev \
    vim \ 
    unzip

# extsensions for php
RUN apt-get update && \
    apt-get install -y --no-install-recommends --no-install-suggests \
    php-common \
    php-mongodb \
    php-curl \
    php-intl \
    php-soap \
    php-xml \
    php-bcmath \
    php-mysql \
    php-amqp \
    php-mbstring \
    php-ldap \
    php-zip \
    php-json \
    php-xml \
    php-xmlrpc \
    php-gmp \
    php-ldap \
    php-gd \
    php-dev \
    php-redis \
    php-xmlreader \
    php-dom \
    php-fpm \
    php-imagick \
    php-tokenizer \
    php-posix \
    php-sockets \
    php-iconv \
    php-exif \
    php-ftp \
    php-simplexml \
    php-xmlreader \
    php-xdebug && \
    echo "extension=apcu.so" | tee -a /etc/php/7.4/mods-available/cache.ini 
#    php-mcrypt \

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Install node.js
RUN apt install -y gpg-agent && \
    curl -sL https://deb.nodesource.com/setup_14.x | bash - && \
    apt update && apt install -y nodejs yarn

# set timezone Asia/Almaty
RUN cp /usr/share/zoneinfo/Asia/Almaty /etc/localtime

# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
	&& ln -sf /dev/stderr /var/log/nginx/error.log \
	&& ln -sf /dev/stderr /var/log/php7.4-fpm.log
    
RUN rm -f /etc/nginx/sites-enabled/*
RUN rm -f /etc/nginx/sites-available/*
COPY ./nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./nginx/nginx.conf /etc/nginx/nginx.conf

COPY . /var/www/
RUN mkdir -p /var/run/php && touch /var/run/php/php7.4-fpm.sock && touch /var/run/php/php7.4-fpm.pid

COPY entrypoint.sh /entrypoint.sh

WORKDIR /var/www/
RUN chmod 755 /entrypoint.sh

COPY ./docker_files/opt/. /opt/
COPY /php-fpm/kalkancrypt.so /usr/lib/php/20190902/
COPY /php-fpm/kalkancrypt.ini /etc/php/7.4/fpm/conf.d/40-kalkancrypt.ini
COPY /php-fpm/kalkancrypt.ini /etc/php/7.4/cli/conf.d/40-kalkancrypt.ini
COPY /php-fpm/kalkancrypt.ini /etc/php/7.4/mods-available/40-kalkancrypt.ini

RUN chown -R www-data:www-data /var/www

RUN chmod 775 /var/www/
RUN chmod -R 777 /var/www/public
RUN chmod -R 777 /var/www/storage
RUN chmod -R 777 /var/www/bootstrap/cache

COPY ./docker_files/certs/. /usr/local/share/ca-certificates/
COPY ./docker_files/certs/. /usr/share/ca-certificates/
COPY ./docker_files/certs/*.pem /etc/ssl/certs/
RUN update-ca-certificates --fresh

RUN echo 'alias "export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/opt/kalkancrypt/:/opt/kalkancrypt/lib/engines"' >> ~/.bashrc

EXPOSE 80
CMD ["/entrypoint.sh"]
