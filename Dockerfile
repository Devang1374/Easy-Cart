FROM php:8.5.7-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath

# Enable Apache rewrite
RUN a2enmod rewrite

# Configure Apache
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies
RUN npm install

# Build Vite assets
RUN npm run build

RUN chown -R www-data:www-data storage bootstrap/cache

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]