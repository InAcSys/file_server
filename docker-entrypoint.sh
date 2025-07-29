#!/bin/sh
set -e

# Wait for database to be ready
until nc -z "$DB_HOST" "$DB_PORT"; do
  echo "La base de datos aún no está disponible..."
  sleep 2
done

echo "Database is ready. Starting application setup..."

# Ensure proper ownership and permissions
chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null || true
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true

# Clear any existing cached config/routes that might conflict
echo "Clearing application caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Ensure Octane is properly installed
echo "Setting up Laravel Octane..."
if ! php artisan --quiet list | grep -q "octane:start"; then
    echo "Installing Laravel Octane..."
    composer require laravel/octane --no-interaction
    php artisan octane:install --server="swoole" --no-interaction
else
    echo "Laravel Octane is already installed."
fi

# Create storage link
echo "Creating storage link..."
php artisan storage:link 2>/dev/null || true

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Verify that the application can bootstrap properly
echo "Verifying application bootstrap..."
if ! php test-bootstrap.php; then
    echo "ERROR: Laravel application cannot bootstrap properly"
    echo "Attempting to debug the issue..."
    php artisan --version || echo "Artisan command failed"
    exit 1
fi

echo "Application bootstrap verified successfully"

echo "Starting Laravel Octane server..."
# Start Octane with proper server configuration
exec php artisan octane:start --server="swoole" --host="0.0.0.0" --port="8000" --workers=4
