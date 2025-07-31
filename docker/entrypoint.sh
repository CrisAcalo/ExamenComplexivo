#!/bin/sh

echo "🔧 Ejecutando comandos de preparación de Laravel..."

# Esperar a que la base de datos esté lista
echo "⏳ Esperando a la base de datos en laravel_db:3306..."
until nc -z laravel_db 3306; do
  sleep 1
done
echo "✅ Base de datos lista."

# Ejecutar comandos de Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed
# php artisan db:seed --class=RoleSeeder
# php artisan db:seed --class=InitialSeeder
# php artisan db:seed --class=RubricaSeeder
php artisan storage:link

echo "✅ Laravel listo. Iniciando PHP-FPM..."
exec php-fpm
