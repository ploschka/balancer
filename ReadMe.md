# Балансировщик нагрузки

## Развёртывание в production окружении

1. Создать `db.prod.env` и заполнить переменные окружения базы данных по примеру `db.env`

    ```sh
    MYSQL_DATABASE=balancer
    MYSQL_ROOT_PASSWORD=secret
    MYSQL_PASSWORD=secret
    MYSQL_USER=app
    ```

2. Создать `.env.prod.local` и заполнить переменные окружения для секретного ключа symfony и URL для подключения к базе данных по примеру `.env.prod`

    ```sh
    DATABASE_URL="mysql://app:secret@database/balancer?serverVersion=8.3.0&charset=utf8mb4"
    APP_SECRET=production_secret
    ```

3. (Опционально) Установить переменную окружения `WEBSERVER_PORT` для внешнего порта веб-сервера

4. Развернуть приложение в docker

    ```sh
    docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
    ```

## Развёртывание в окружении разработчика

1. Установить зависимости

    ```sh
    composer install
    ```

2. Установить переменные окружения для Symfony

    ```sh
    composer dump-env dev
    ```

3. Развернуть базу данных в docker

    ```sh
    docker compose up -d
    ```

4. Применить миграции к базе данных

    ```sh
    php bin/console doctrine:migrations:migrate
    ```

5. Запустить локальный сервер (рекомендуется symfony)

    ```sh
    symfony local:server:start
    ```
