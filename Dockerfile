FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    zip \
    libonig-dev \
    && docker-php-ext-install \
        zip \
        mbstring \
        bcmath \
        pdo \
        pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Limpar qualquer cache quebrado do Laravel
RUN php artisan config:clear
RUN php artisan route:clear
RUN php artisan cache:clear
RUN php artisan view:clear

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
