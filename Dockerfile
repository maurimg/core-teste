FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    curl \
    && docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

# Criar .env mínimo manualmente
RUN echo "APP_NAME=Laravel" > .env
RUN echo "APP_ENV=production" >> .env
RUN echo "APP_KEY=" >> .env
RUN echo "APP_DEBUG=false" >> .env
RUN echo "APP_URL=http://localhost" >> .env

# Instalar dependências SEM scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Gerar chave
RUN php artisan key:generate --force

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
