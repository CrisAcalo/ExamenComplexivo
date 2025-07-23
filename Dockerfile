FROM php:8.2-fpm-alpine AS base
WORKDIR /var/www/html
RUN apk add --no-cache \
    libpng-dev libzip-dev jpeg-dev freetype-dev libxml2-dev oniguruma-dev netcat-openbsd \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip gd xml
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

FROM base AS builder
RUN apk add --no-cache nodejs-lts npm
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --prefer-dist
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

FROM base AS production
COPY --from=builder /var/www/html/vendor ./vendor
COPY --from=builder /var/www/html/public/build ./public/build
COPY --from=builder /var/www/html .
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
