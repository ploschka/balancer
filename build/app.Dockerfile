FROM composer:2.7 AS build
WORKDIR /app
COPY ./ /app
RUN composer install && composer dump-env prod

FROM ploshka/symfony-app:8.2-3.0
COPY --from=build --chown=www-data:www-data /app /app

CMD php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --all-or-nothing && php-fpm
