FROM logistino-app:docker-check

USER root

# Copy composer from local cached composer:2.8 image
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Custom entrypoint for DailyFlow
COPY --chmod=755 docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
