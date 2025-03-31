#!/bin/bash
set -e

# Wait for the database to be ready
echo "Waiting for database connection..."
ATTEMPTS=0
until [ $ATTEMPTS -ge 5 ] || mysql -h$DB_HOST -u$DB_USER -p$DB_PASS -e "SELECT 1" > /dev/null 2>&1; do
    ATTEMPTS=$((ATTEMPTS+1))
    echo "Waiting for database connection... (attempt $ATTEMPTS/5)"
    sleep 5
done

if [ $ATTEMPTS -ge 5 ]; then
    echo "Warning: Could not connect to database, continuing anyway"
fi

# Make sure Composer dependencies are installed and up to date
if [ ! -d /var/www/html/vendor ] || [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction
else
    echo "Composer dependencies already installed."
fi

# Ensure proper file ownership
chown -R www-data:www-data /var/www/html

# Start Apache in foreground
echo "Starting Apache..."
apache2-foreground 