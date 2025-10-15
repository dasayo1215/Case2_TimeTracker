#!/bin/bash
set -e

echo "Starting environment setup..."

# コンテナ起動
docker-compose up -d

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
