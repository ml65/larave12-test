#!/bin/bash

set -e

echo "Waiting for MySQL to be ready..."
max_attempts=30
attempt=0
while ! nc -z mysql 3306; do
  attempt=$((attempt + 1))
  if [ $attempt -ge $max_attempts ]; then
    echo "MySQL is not ready after $max_attempts attempts. Exiting."
    exit 1
  fi
  echo "Waiting for MySQL... ($attempt/$max_attempts)"
  sleep 2
done

echo "MySQL is ready!"

# Установка зависимостей Composer (если нужно)
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader || true
fi

# Ожидание готовности базы данных
echo "Waiting for database connection..."
for i in {1..10}; do
    php artisan migrate:status > /dev/null 2>&1 && break || sleep 2
done

# Запуск миграций
echo "Running migrations..."
php artisan migrate --force || true

# Запуск сидеров (если есть)
if [ -f "database/seeders/DatabaseSeeder.php" ]; then
    echo "Running seeders..."
    php artisan db:seed --force || true
fi

# Очистка кеша
echo "Clearing cache..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Генерация Swagger документации (если нужно)
if [ -f "vendor/bin/openapi" ]; then
    echo "Generating Swagger documentation..."
    php artisan l5-swagger:generate || true
fi

echo "Application is ready!"

exec "$@"
