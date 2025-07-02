#!/bin/sh
set -e

until nc -z "$DB_HOST" "$DB_PORT"; do
  echo "La base de datos aún no está disponible..."
  sleep 2
done

if php artisan --quiet list | grep -q octane; then
    echo "Laravel Octane ya está instalado."
else
    echo "Instalando Laravel Octane..."
    composer require laravel/octane
    php artisan octane:install --server="swoole" --no-interaction
fi

php artisan storage:link || true

php artisan migrate --force

exec php artisan octane:start --server="swoole" --host="0.0.0.0"
