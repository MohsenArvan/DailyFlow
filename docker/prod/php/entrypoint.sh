#!/bin/sh
set -eu

if [ "${DAILYFLOW_MIGRATE:-0}" = "1" ]; then
    php artisan migrate --force
fi

php artisan config:cache
php artisan package:discover
php artisan route:cache
php artisan view:cache
php artisan event:cache

exec "$@"
