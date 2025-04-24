#!/bin/bash

echo "⏳ Waiting for MySQL to be ready at gatekeeper-mysql:3306..."

while ! mysqladmin ping -hgatekeeper-mysql -P3306 -ugatekeeper -p123456 --silent; do
    sleep 2
done

echo "✅ MySQL is up - running migrations..."
php artisan migrate:fresh --seed --env=dusk.local
