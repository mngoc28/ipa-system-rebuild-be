#!/bin/bash

# Copy .env.dev to .env only if it exists
if [ -f "/var/www/.env.dev" ]; then
    cp /var/www/.env.dev /var/www/.env
fi

# Install Composer dependencies
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Optimize Laravel cache
php artisan optimize

# Install npm and generate API documentation
if [ -d "api-doc" ]; then
    npm install
    cd api-doc
    npm install apidoc -g
    apidoc -i . -o ../public/apidoc
    cd ..
fi

# Ensure the correct permissions for Laravel
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

