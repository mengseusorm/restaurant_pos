#!/bin/bash
set -e

cd /var/www/html

echo "$(date '+%F %T') Checking vendor folder..."
if [ ! -d "vendor" ]; then
  echo "$(date '+%F %T') Running composer install..."
  composer install --no-interaction --prefer-dist --no-progress || {
    echo "❌ Composer install failed!"
    exit 1
  }
else
  echo "$(date '+%F %T') Vendor exists, skipping composer install."
fi

if [ ! -f ".env" ]; then
  echo "No .env file found. Copying .env.example..."
  cp .env.example .env
  php artisan key:generate
fi

# Load Laravel .env as environment variables
export $(grep -v '^#' .env | xargs)

echo "$(date '+%F %T') Waiting for MySQL to be ready..."
until mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent; do
  echo "Waiting for database..."
  sleep 3
done

if [ -f "vendor/autoload.php" ]; then
  echo "$(date '+%F %T') Running database migrations..."
  php artisan migrate --force
else
  echo "❌ vendor/autoload.php missing, cannot run artisan migrate."
  exit 1
fi

if [ ! -d "node_modules" ]; then
  echo "$(date '+%F %T') Installing Node dependencies..."
  npm install
fi

npm install
php artisan config:clear
php artisan cache:clear
php artisan storage:link


echo "$(date '+%F %T') Building frontend..."
# npm run build
npm run dev
npm run watch &

echo "$(date '+%F %T') Starting Apache..."
exec apache2-foreground
