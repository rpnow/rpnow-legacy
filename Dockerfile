FROM php:5.6-fpm-alpine
RUN docker-php-ext-install pdo pdo_mysql