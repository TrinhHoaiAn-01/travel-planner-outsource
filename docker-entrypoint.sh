#!/bin/sh

set -e

echo "Waiting for MySQL..."

until php artisan db:show >/dev/null 2>&1
do
    sleep 2
done

echo "MySQL is ready."

php artisan migrate --force

echo "Laravel database is ready."

exec apache2-foreground