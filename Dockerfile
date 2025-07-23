# --- Etapa 1: Base (Sin cambios) ---
FROM php:8.2-fpm-alpine AS base
WORKDIR /var/www/html
RUN apk add --no-cache \
        libpng-dev libzip-dev jpeg-dev freetype-dev libxml2-dev oniguruma-dev netcat-openbsd \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip gd xml
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- Etapa 2: Builder (Un pequeño ajuste para robustez) ---
FROM base AS builder
RUN apk add --no-cache nodejs-lts npm

# Copiar solo los archivos de dependencias primero
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN npm install

# Ahora copiar el resto del código
COPY . .
# Asegúrate de tener un archivo .dockerignore para excluir node_modules y vendor/ de tu host

# Compilar assets
RUN npm run build

# --- Etapa 3: Producción (CORREGIDA) ---
FROM base AS production

# Crear el usuario y grupo de la aplicación
# RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www
# WORKDIR /var/www/html # Ya está en la imagen base

# Copiar las dependencias instaladas desde el builder
COPY --from=builder /var/www/html/vendor ./vendor

# Copiar los assets compilados desde el builder
COPY --from=builder /var/www/html/public/build ./public/build

# Copiar el código fuente de la aplicación desde el builder (NO desde el host)
COPY --from=builder /var/www/html .

# Copiar el entrypoint (esto sí es local)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Configurar permisos (no es necesario chown si corres como www-data)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
