FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-install \
        pdo_mysql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer

COPY . /var/www/html

RUN composer install \
    --no-ansi \
    --no-interaction \
    --no-progress \
    --prefer-dist

RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

RUN chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

RUN printf '%s\n' \
    '<VirtualHost *:80>' \
    '    DocumentRoot /var/www/html/public' \
    '    <Directory /var/www/html/public>' \
    '        AllowOverride All' \
    '        Require all granted' \
    '    </Directory>' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]