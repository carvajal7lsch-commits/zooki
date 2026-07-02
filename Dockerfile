FROM php:8.2-apache

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar el directorio de trabajo
WORKDIR /var/www/html/Zooki

# Copiar los archivos de la aplicación
COPY . .

# Instalar dependencias de PHP (si existe el archivo composer.json)
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader; fi

# Configurar permisos para que el servidor web pueda leer/escribir
RUN chown -R www-data:www-data /var/www/html/Zooki
