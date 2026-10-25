# Imagen base de PHP con extensiones necesarias
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip zip curl \
    && docker-php-ext-install pdo pdo_mysql

# Instalar Node.js 20 y npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Copiar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias de npm y compilar Tailwind/Vite
RUN npm install
RUN npm run build

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Exponer el puerto del servidor interno de Laravel
EXPOSE 8080

# Comando de inicio:
# Ejecutamos PHP-FPM en segundo plano y Laravel en primer plano
CMD php-fpm -D && php artisan serve --host=0.0.0.0 --port=8080


