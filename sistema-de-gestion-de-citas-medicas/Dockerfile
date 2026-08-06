FROM php:8.3-cli

# Instalar dependencias del sistema y extensiones de PHP requeridas por Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    libpq-dev

# Instalar Node.js 20.x para compilar assets del Frontend (Vite / Tailwind)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# Instalar extensiones necesarias de PHP (incluyendo zip, pdo_pgsql, etc.)
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar el código fuente del proyecto Laravel
COPY . /var/www

# Instalar dependencias de PHP con Composer y compilar el Frontend con Vite
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Crear la base de datos SQLite si no existe (fallback)
RUN mkdir -p /var/www/database && touch /var/www/database/database.sqlite

# Asignar permisos a storage y bootstrap/cache
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache /var/www/database

EXPOSE 8000

# Script de arranque: Ejecutar migraciones, seeders y servidor Laravel
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
