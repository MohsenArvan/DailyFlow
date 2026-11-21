FROM node:22-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources resources
COPY vite.config.ts tailwind.config.js postcss.config.js tsconfig.json ./
RUN npm run build

FROM php:8.3-fpm-alpine AS php
RUN apk add --no-cache libpq libzip icu-libs oniguruma \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev libzip-dev icu-dev linux-headers \
    && docker-php-ext-install pdo_pgsql zip intl pcntl opcache \
    && pecl install redis-6.2.0 \
    && docker-php-ext-enable redis \
    && apk del .build-deps

FROM php AS dependencies
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
WORKDIR /build
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY app app
COPY database database
RUN composer dump-autoload --no-dev --optimize --no-scripts

FROM nginx:alpine AS web
COPY public /var/www/html/public
COPY --from=assets /build/public/build /var/www/html/public/build
RUN rm -f /var/www/html/public/hot
COPY docker/prod/nginx/default.conf /etc/nginx/conf.d/default.conf

FROM php AS app
WORKDIR /var/www/html
COPY --from=dependencies /build/vendor vendor
COPY app app
COPY bootstrap bootstrap
COPY config config
COPY database database
COPY routes routes
COPY resources/views resources/views
COPY artisan composer.json ./
COPY public public
COPY --from=assets /build/public/build public/build
COPY docker/prod/php/opcache.ini /usr/local/etc/php/conf.d/production.ini
COPY --chmod=755 docker/prod/php/entrypoint.sh /usr/local/bin/dailyflow
RUN rm -f public/hot bootstrap/cache/*.php \
    && mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
ENTRYPOINT ["dailyflow"]
CMD ["php-fpm", "-F"]
