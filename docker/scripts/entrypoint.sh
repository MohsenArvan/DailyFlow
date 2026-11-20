#!/bin/sh
set -e

# Ensure permissions on storage and bootstrap/cache directories if they exist
if [ -d "/var/www/html/storage" ]; then
    mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache
    chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true
fi

exec "$@"
