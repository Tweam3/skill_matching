#!/bin/bash
set -e

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

if [ -n "$DB_CONNECTION" ]; then sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=$DB_CONNECTION/" /var/www/html/.env; fi
if [ -n "$DB_HOST" ]; then sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" /var/www/html/.env; fi
if [ -n "$DB_PORT" ]; then sed -i "s/^DB_PORT=.*/DB_PORT=$DB_PORT/" /var/www/html/.env; fi
if [ -n "$DB_DATABASE" ]; then sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" /var/www/html/.env; fi
if [ -n "$DB_USERNAME" ]; then sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" /var/www/html/.env; fi
if [ -n "$DB_PASSWORD" ]; then sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" /var/www/html/.env; fi

php /var/www/html/artisan key:generate --force
php /var/www/html/artisan config:clear
php /var/www/html/artisan route:clear
php /var/www/html/artisan migrate --force
# seeding removed: run manually with `php artisan db:seed --force` when needed
rm -f /var/www/html/public/storage
php /var/www/html/artisan storage:link

exec "$@"
