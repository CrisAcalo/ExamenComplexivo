# --- Etapa 1: Base ---
FROM php:8.2-fpm-alpine AS base

WORKDIR /var/www/html

# Instalar dependencias del sistema y PHP para Laravel
RUN apk add --no-cache \
        libpng-dev \
        libzip-dev \
        jpeg-dev \
        freetype-dev \
        libxml2-dev \
        oniguruma-dev \
        netcat-openbsd \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        zip \
        gd \
        xml

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# --- Etapa 2: Builder ---
FROM base AS builder

# Instalar Node y NPM
RUN apk add --no-cache nodejs-lts npm

# Copiar y preparar dependencias
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN npm install

# Copiar el código de la app
COPY . .

# Compilar assets con Vite
RUN npm run build


# --- Etapa 3: Producción ---
FROM base AS production

# Copiar dependencias PHP
COPY --from=builder /var/www/html/vendor ./vendor

# Copiar el código fuente
COPY . .

# Copiar assets compilados
COPY --from=builder /var/www/html/public/build ./public/build

# Copiar entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Configurar permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

# Ejecutar script Laravel + php-fpm
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
