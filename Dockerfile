FROM node:22-bookworm-slim AS frontend
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php:8.3-apache AS base
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libjpeg62-turbo-dev libpng-dev libzip-dev libonig-dev libsqlite3-dev unzip \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd mbstring pdo_mysql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite
WORKDIR /var/www/html
COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer
RUN printf '%s\n' \
    '<VirtualHost *:80>' \
    '    DocumentRoot /var/www/html/public' \
    '    <Directory /var/www/html/public>' \
    '        Options -Indexes +FollowSymLinks' \
    '        AllowOverride All' \
    '        Require all granted' \
    '    </Directory>' \
    '</VirtualHost>' > /etc/apache2/sites-available/000-default.conf
EXPOSE 80
CMD ["apache2-foreground"]

# CI and local development need PHPUnit and the other development packages.
FROM base AS testing
COPY . .
RUN mkdir -p storage/app/public storage/app/private storage/logs \
        storage/framework/cache/data storage/framework/sessions storage/framework/views \
    && composer install --no-interaction --no-progress --prefer-dist \
    && chown -R www-data:www-data storage bootstrap/cache
COPY --from=frontend /build/public/build ./public/build

# Only application files and production dependencies enter the release image.
FROM base AS production
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan composer.json composer.lock .env.example ./
RUN mkdir -p storage/app/public storage/app/private storage/logs \
        storage/framework/cache/data storage/framework/sessions storage/framework/views \
    && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader \
    && ln -s /var/www/html/storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache
COPY --from=frontend /build/public/build ./public/build
COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/travel-planner-entrypoint
ENTRYPOINT ["travel-planner-entrypoint"]
CMD ["apache2-foreground"]
