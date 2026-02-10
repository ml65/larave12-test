# Docker Compose для локальной разработки

Простое развертывание Laravel приложения для разработки.

## Требования

- Docker
- Docker Compose
- Файл `.env` с настройками базы данных

## Быстрый старт

1. Убедитесь, что файл `.env` содержит настройки базы данных:
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=root
```

2. Запустите контейнеры:
```bash
docker-compose up -d
```

3. Приложение будет доступно по адресу: http://localhost:8000

## Что происходит при запуске

1. **Composer install** - автоматически устанавливаются зависимости (если нужно)
2. **Миграции** - автоматически выполняются `php artisan migrate --force`
3. **Сидеры** - автоматически выполняются `php artisan db:seed --force` (если есть)

## Структура

- **app** - PHP-FPM контейнер с Laravel
- **nginx** - Nginx веб-сервер
- **mysql** - MySQL 8.0 база данных

## Volumes

- Код приложения монтируется из текущей директории (`./:/var/www/html`)
- База данных сохраняется в volume `mysql_data` (данные сохраняются при перезапуске)

## Полезные команды

### Просмотр логов
```bash
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql
```

### Выполнение команд в контейнере
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose exec app composer install
```

### Перезапуск контейнеров
```bash
docker-compose restart
```

### Остановка контейнеров
```bash
docker-compose down
```

### Остановка с удалением volumes (удалит базу данных!)
```bash
docker-compose down -v
```

### Пересборка образов
```bash
docker-compose build --no-cache
docker-compose up -d
```

## Изменения кода

Код монтируется как volume, поэтому все изменения в локальных файлах сразу видны в контейнере. Не требуется перезапуск контейнеров для применения изменений в PHP коде.

## База данных

База данных сохраняется в Docker volume `mysql_data`. При перезапуске контейнеров данные сохраняются.

Для подключения к базе данных извне Docker:
- Host: `localhost`
- Port: `3306`
- Database: значение из `DB_DATABASE` в `.env`
- Username: значение из `DB_USERNAME` в `.env`
- Password: значение из `DB_PASSWORD` в `.env`

## Troubleshooting

### Проблемы с правами доступа
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

### Очистка кеша
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Пересоздание базы данных
```bash
docker-compose down -v
docker-compose up -d
```

