#!/bin/sh
set -e

# Wait for database to be ready
echo "Waiting for database connection..."
if command -v nc >/dev/null 2>&1; then
    until nc -z mysql 3306; do
        echo "Database is unavailable - sleeping"
        sleep 1
    done
    echo "Database is up!"
else
    echo "netcat not available, skipping database wait check"
    sleep 5
fi

# Set proper permissions (run as root if needed)
if [ "$(id -u)" = "0" ]; then
    echo "Setting permissions..."
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
    
    # Create storage directories if they don't exist
    mkdir -p /var/www/html/storage/framework/{sessions,views,cache} 2>/dev/null || true
    mkdir -p /var/www/html/storage/logs 2>/dev/null || true
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
fi

# Install/update composer dependencies if composer.lock is present
if [ -f composer.lock ] && command -v composer >/dev/null 2>&1; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev 2>/dev/null || \
    composer install --no-interaction --prefer-dist --optimize-autoloader 2>/dev/null || true
fi

# Install/update npm dependencies if package.json is present
if [ -f package.json ] && command -v npm >/dev/null 2>&1; then
    echo "Installing NPM dependencies..."
    if [ -f package-lock.json ]; then
        npm ci --silent 2>/dev/null || npm install --silent 2>/dev/null || true
    else
        npm install --silent 2>/dev/null || true
    fi
    
    # Build frontend assets if in production
    if [ "$APP_ENV" = "production" ]; then
        echo "Building production assets..."
        npm run build 2>/dev/null || true
    fi
fi

# Generate application key if .env exists but key is not set
if [ -f .env ] && command -v php >/dev/null 2>&1; then
    if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
        echo "Generating application key..."
        php artisan key:generate --force 2>/dev/null || true
    fi
fi

# Run database migrations if artisan is available
if command -v php >/dev/null 2>&1 && [ -f artisan ]; then
    echo "Running database migrations..."
    php artisan migrate --force 2>/dev/null || true
    
    # Create storage link
    php artisan storage:link 2>/dev/null || true
    
    # Clear cache
    php artisan config:clear 2>/dev/null || true
    php artisan cache:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    
    # Optimize for production
    if [ "$APP_ENV" = "production" ]; then
        echo "Optimizing for production..."
        php artisan config:cache 2>/dev/null || true
        php artisan route:cache 2>/dev/null || true
        php artisan view:cache 2>/dev/null || true
    fi
fi

echo "Entrypoint script completed. Starting application..."

# PHP-FPM will handle user switching via its configuration
# The entrypoint runs as root to allow permission changes
exec "$@"
