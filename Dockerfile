# Use an official PHP image
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    supervisor \
    cron \
    nginx \
    libpq-dev \
    libonig-dev \
    nodejs \
    npm



# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip


# Install Composer
COPY --from=composer:2.8.1 /usr/bin/composer /usr/bin/composer

# Copy configuration files
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/nginx/default.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Configure logging for Nginx
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

# Copy Cron configuration
COPY ./docker/cron/crontab /etc/cron.d/laravel-cron
RUN chmod 0644 /etc/cron.d/laravel-cron \
    && crontab /etc/cron.d/laravel-cron

WORKDIR /var/www

COPY . /var/www

# Fix permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

RUN mkdir -p /var/log/supervisord/ \
    && chown -R www-data:www-data /var/log/supervisord/ \
    && chmod -R 755 /var/log/supervisord/

RUN sed -i 's/\r$//' ./setup.sh \
    && bash ./setup.sh


EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

