FROM php:8.4-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    netcat-openbsd \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копирование файлов composer
COPY composer.json composer.lock ./

# Установка зависимостей (без dev зависимостей для продакшена, но с dev для разработки)
RUN composer install --no-scripts --no-autoloader || true

# Копирование остальных файлов
COPY . .

# Завершение установки Composer
RUN composer dump-autoload --optimize || true

# Копирование entrypoint скрипта
COPY docker-entrypoint.sh /var/www/html/docker-entrypoint.sh
RUN chmod +x /var/www/html/docker-entrypoint.sh

# Установка прав на storage и bootstrap/cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port 9000 для PHP-FPM
EXPOSE 9000

# PHP-FPM будет запускаться через entrypoint скрипт
CMD ["php-fpm", "-F"]

