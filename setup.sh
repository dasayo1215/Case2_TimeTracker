#!/bin/bash
set -e

echo "Starting environment setup..."

# コンテナ起動
docker-compose up -d

# ✅ MySQL 起動待機（healthcheckで確実に）
echo "Waiting for MySQL to be healthy..."
until [ "$(docker inspect -f '{{.State.Health.Status}}' mysql 2>/dev/null)" = "healthy" ]; do
    echo "⏳ MySQL is starting up..."
    sleep 3
done
echo "✅ MySQL is ready!"

# Laravel 初期セットアップ
echo "Installing backend dependencies..."
docker-compose exec php bash -c "
    composer install &&
    cp -n .env.example .env &&
    php artisan key:generate &&
    php artisan migrate --seed &&
    mkdir -p storage/logs &&
    touch storage/logs/laravel.log &&
    mkdir -p bootstrap/cache &&
    chown -R www-data:www-data storage bootstrap/cache &&
    chmod -R 775 storage bootstrap/cache
"

# フロント依存インストール
echo "Installing frontend dependencies..."
cd frontend && npm install

echo "Setup complete! You can now run 'make start'"
