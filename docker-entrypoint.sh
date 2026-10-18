#!/bin/sh
set -eu

mkdir -p storage/app/public storage/app/private storage/logs \
    storage/framework/cache/data storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage bootstrap/cache

if [ "$1" = "apache2-foreground" ]; then
    php artisan config:cache
    php artisan view:cache
fi

exec "$@"
