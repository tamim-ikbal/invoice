# 1. Base Image
FROM php:8.4-fpm

# 2. System dependencies (Added libpq-dev for Postgres and libonig-dev for mbstring)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# 3. PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath pgsql pdo_pgsql

# 4. Install Composer (latest stable 2.x)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Install Node.js & Yarn (Debian-compatible method)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g yarn --force

WORKDIR /var/www/html

# 6. Copy dependencies first for better caching
COPY composer.json composer.lock ./
RUN composer install --no-scripts

COPY package.json yarn.lock ./
RUN yarn

# 7. Copy the rest of the application
COPY . .

# Optional: Set permissions for Laravel (if needed)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Build and Laravel setup
RUN yarn build

RUN php artisan storage:link

# RUN php artisan optimize:clear

# RUN php artisan migrate --force

# RUN php artisan optimize

EXPOSE 9000

CMD ["php-fpm"]