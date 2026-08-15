#!/bin/bash
set -e

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

php /var/www/html/artisan key:generate --force
php /var/www/html/artisan config:clear
php /var/www/html/artisan route:clear
php /var/www/html/artisan migrate --force

exec "$@"
