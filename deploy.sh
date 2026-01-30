#!/bin/bash

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Начало развертывания CTF Platform...${NC}"

# Проверка прав
if [[ $EUID -eq 0 ]]; then
    echo -e "${RED}Ошибка: Не запускайте скрипт от root пользователя.${NC}"
    exit 1
fi

# Получение обновлений кода
echo -e "${YELLOW}📥 Получение обновлений кода...${NC}"
git pull origin main
if [ $? -ne 0 ]; then
    echo -e "${RED}Ошибка при получении обновлений кода${NC}"
    exit 1
fi

# Установка зависимостей PHP
echo -e "${YELLOW}📦 Установка PHP зависимостей...${NC}"
composer install --optimize-autoloader --no-dev
if [ $? -ne 0 ]; then
    echo -e "${RED}Ошибка при установке PHP зависимостей${NC}"
    exit 1
fi

# Установка зависимостей Node.js
echo -e "${YELLOW}📦 Установка Node.js зависимостей...${NC}"
npm ci --only=production
if [ $? -ne 0 ]; then
    echo -e "${RED}Ошибка при установке Node.js зависимостей${NC}"
    exit 1
fi

# Сборка фронтенда
echo -e "${YELLOW}🔨 Сборка фронтенда...${NC}"
npm run production
if [ $? -ne 0 ]; then
    echo -e "${RED}Ошибка при сборке фронтенда${NC}"
    exit 1
fi

# Миграции базы данных
echo -e "${YELLOW}🗄️  Выполнение миграций...${NC}"
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo -e "${RED}Ошибка при выполнении миграций${NC}"
    exit 1
fi

# Очистка кэша
echo -e "${YELLOW}🧹 Очистка кэша...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Кэширование
echo -e "${YELLOW}⚡ Оптимизация приложения...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Права доступа
echo -e "${YELLOW}🔒 Настройка прав доступа...${NC}"
sudo chown -R www-data:www-data .
sudo chmod -R 755 storage bootstrap/cache
sudo chmod -R 775 storage/logs

# Проверка .env файла
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️  Файл .env не найден, создаю из .env.example...${NC}"
    cp .env.example .env
    php artisan key:generate
fi

# Перезапуск сервисов
echo -e "${YELLOW}🔄 Перезапуск сервисов...${NC}"
sudo systemctl restart apache2
sudo systemctl restart redis-server

# Проверка статуса сервисов
echo -e "${YELLOW}🔍 Проверка статуса сервисов...${NC}"
if systemctl is-active --quiet apache2; then
    echo -e "${GREEN}✓ Apache2 запущен${NC}"
else
    echo -e "${RED}✗ Apache2 не запущен${NC}"
fi

if systemctl is-active --quiet redis-server; then
    echo -e "${GREEN}✓ Redis запущен${NC}"
else
    echo -e "${RED}✗ Redis не запущен${NC}"
fi

echo -e "${GREEN}✅ Развертывание завершено успешно!${NC}"
echo -e "${BLUE}🌐 Сайт доступен по адресу: http://$(curl -s ifconfig.me)${NC}"