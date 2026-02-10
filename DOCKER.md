# Быстрый старт с Docker

## Требования

- Docker
- Docker Compose

## Запуск

1. Убедитесь, что в `.env` указаны настройки базы данных:
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

3. Приложение доступно: http://localhost:8000

## Что происходит автоматически

- ✅ Установка зависимостей Composer
- ✅ Выполнение миграций
- ✅ Запуск сидеров
- ✅ Очистка кеша
- ✅ Генерация Swagger документации

## Полезные команды

```bash
# Просмотр логов
docker-compose logs -f

# Выполнение команд в контейнере
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker

# Остановка
docker-compose down

# Перезапуск
docker-compose restart
```

Подробная документация: [docker/README.md](./docker/README.md)

