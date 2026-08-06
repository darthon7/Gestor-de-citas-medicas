FROM php:8.2-cli

# Instalar dependencias del sistema y extensiones de PHP requeridas por Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar el código fuente del proyecto Laravel
COPY . /var/www

# Instalar dependencias de PHP con Composer
RUN composer install --no-dev --optimize-autoloader

# Crear la base de datos SQLite si no existe
RUN mkdir -p /var/www/database && touch /var/www/database/database.sqlite

# Asignar permisos a storage y bootstrap/cache
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache /var/www/database

EXPOSE 8000

# Script de arranque: Ejecutar migraciones, seeders y servidor Laravel
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
