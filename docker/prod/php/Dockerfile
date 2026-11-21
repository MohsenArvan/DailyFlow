FROM node:22-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources resources
COPY vite.config.ts tailwind.config.js postcss.config.js tsconfig.json ./
RUN npm run build

FROM alpine:3.22 AS php
RUN apk add --no-cache ca-certificates php83 php83-ctype php83-curl php83-dom \
    php83-fileinfo php83-fpm php83-iconv php83-intl php83-mbstring php83-opcache \
    php83-openssl php83-pcntl php83-pdo_pgsql php83-pecl-redis php83-phar php83-posix \
    php83-session php83-simplexml php83-sodium php83-tokenizer php83-xml php83-xmlreader \
    php83-xmlwriter php83-zip \
    && ln -s /usr/bin/php83 /usr/local/bin/php
RUN if ! id www-data >/dev/null 2>&1; then \
    getent group www-data >/dev/null || addgroup -S www-data; \
    adduser -S -D -H -G www-data www-data; \
    fi

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
COPY docker/prod/php/opcache.ini /etc/php83/conf.d/99-production.ini
COPY docker/prod/php/php-fpm.conf /etc/php83/php-fpm.conf
COPY --chmod=755 docker/prod/php/entrypoint.sh /usr/local/bin/dailyflow
RUN rm -f public/hot bootstrap/cache/*.php \
    && mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
ENTRYPOINT ["dailyflow"]
CMD ["php-fpm83", "-F"]
