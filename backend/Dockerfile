FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    curl \
    bash \
    postgresql-dev \
    autoconf \
    g++ \
    make \
    && docker-php-ext-install pdo pdo_pgsql \
    && apk del autoconf g++ make

WORKDIR /app

COPY composer.json composer.lock* ./

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize

RUN chmod +x init.sh seed.sh tests.sh lint.sh

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
