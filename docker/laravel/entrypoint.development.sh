#!/bin/bash
set -e

# If the application source (composer.json) is present, run normal startup.
if [ -f /var/www/html/composer.json ]; then
	echo "composer.json found, installing dependencies and starting app"
	composer install
	if ! grep -q '^APP_KEY=.' .env; then
		php artisan key:generate
	fi
	if [ ! -e public/storage ]; then
		php artisan storage:link
	fi
	php artisan migrate --force
	exec php artisan serve --host=0.0.0.0 --port=8000
else
	echo "No composer.json found in /var/www/html — skipping install/migrate/serve"
	tail -f /dev/null
fi