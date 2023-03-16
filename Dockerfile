FROM webdevops/php-nginx:8.0

COPY . /app

RUN composer install -d /app