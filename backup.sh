#!/bin/bash

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Конфигурация
BACKUP_DIR="/var/backups/ctfplatform"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="ctf_backup_$DATE"
BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"

echo -e "${BLUE}💾 Начало создания резервной копии CTF Platform...${NC}"

# Проверка существования директории для бэкапов
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${YELLOW}Создание директории для бэкапов: $BACKUP_DIR${NC}"
    mkdir -p "$BACKUP_DIR"
    chmod 700 "$BACKUP_DIR"
fi

# Создание временной директории
TEMP_DIR="/tmp/ctf_backup_$DATE"
mkdir -p "$TEMP_DIR"

# 1. Бэкап базы данных
echo -e "${YELLOW}1. Бэкап базы данных...${NC}"
if [ -f ".env" ]; then
    DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2 | tr -d '[:space:]')
    DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2 | tr -d '[:space:]')
    DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2 | tr -d '[:space:]')
    
    if [ ! -z "$DB_NAME" ] && [ ! -z "$DB_USER" ]; then
        if mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$TEMP_DIR/database.sql" 2>/dev/null; then
            echo -e "${GREEN}✓ База данных успешно сохранена${NC}"
        else
            echo -e "${RED}✗ Ошибка при сохранении базы данных${NC}"
        fi
    else
        echo -e "${YELLOW}⚠️  Не удалось получить параметры БД из .env${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Файл .env не найден${NC}"
fi

# 2. Бэкап файлов приложения
echo -e "${YELLOW}2. Бэкап файлов приложения...${NC}"

# Важные директории для бэкапа
IMPORTANT_DIRS=(
    "app"
    "config"
    "database"
    "routes"
    "resources/views"
    "resources/lang"
    "public"
    ".env"
    "composer.json"
    "package.json"
)

# Копирование важных файлов
for dir in "${IMPORTANT_DIRS[@]}"; do
    if [ -e "$dir" ]; then
        cp -r "$dir" "$TEMP_DIR/" 2>/dev/null && echo -e "  ✓ $dir"
    fi
done

# 3. Бэкап файлов загрузок
echo -e "${YELLOW}3. Бэкап файлов загрузок...${NC}"
if [ -d "storage/app/public" ]; then
    cp -r storage/app/public "$TEMP_DIR/uploads" 2>/dev/null && echo -e "${GREEN}✓ Файлы загрузок сохранены${NC}"
fi

# 4. Создание архива
echo -e "${YELLOW}4. Создание архива...${NC}"
cd "$TEMP_DIR" || exit 1

# Создаем файл с информацией о бэкапе
echo "CTF Platform Backup" > backup_info.txt
echo "Date: $(date)" >> backup_info.txt
echo "Version: $(php artisan --version 2>/dev/null || echo 'Unknown')" >> backup_info.txt
echo "Database: $DB_NAME" >> backup_info.txt
echo "Filesize: $(du -sh . | cut -f1)" >> backup_info.txt

# Архивируем
if tar -czf "$BACKUP_PATH.tar.gz" .; then
    echo -e "${GREEN}✓ Архив создан: $BACKUP_PATH.tar.gz${NC}"
    
    # Проверяем размер архива
    FILESIZE=$(stat -c%s "$BACKUP_PATH.tar.gz")
    FILESIZE_MB=$((FILESIZE / 1024 / 1024))
    echo -e "  Размер архива: ${FILESIZE_MB}MB"
else
    echo -e "${RED}✗ Ошибка при создании архива${NC}"
    exit 1
fi

# 5. Удаление старых бэкапов (сохраняем последние 10)
echo -e "${YELLOW}5. Очистка старых бэкапов...${NC}"
cd "$BACKUP_DIR" || exit 1
BACKUP_COUNT=$(ls -1 *.tar.gz 2>/dev/null | wc -l)

if [ "$BACKUP_COUNT" -gt 10 ]; then
    OLD_BACKUPS=$((BACKUP_COUNT - 10))
    echo -e "  Удаление $OLD_BACKUPS старых бэкапов"
    ls -t *.tar.gz | tail -n $OLD_BACKUPS | xargs -I {} rm -- {}
fi

# 6. Очистка временных файлов
echo -e "${YELLOW}6. Очистка временных файлов...${NC}"
rm -rf "$TEMP_DIR"

# 7. Проверка целостности архива
echo -e "${YELLOW}7. Проверка целостности архива...${NC}"
if tar -tzf "$BACKUP_PATH.tar.gz" >/dev/null 2>&1; then
    echo -e "${GREEN}✓ Архив прошел проверку целостности${NC}"
else
    echo -e "${RED}✗ Архив поврежден${NC}"
    exit 1
fi

# 8. Копирование на удаленный сервер (опционально)
REMOTE_BACKUP=false
if [ "$REMOTE_BACKUP" = true ]; then
    echo -e "${YELLOW}8. Копирование на удаленный сервер...${NC}"
    # Пример для S3
    # aws s3 cp "$BACKUP_PATH.tar.gz" s3://your-bucket/ctf-backups/
    # или для rsync
    # rsync -avz "$BACKUP_PATH.tar.gz" user@remote-server:/backups/
    echo -e "  ⚠️  Настройте удаленное копирование в скрипте"
fi

# 9. Отчет
echo -e "\n${BLUE}📊 ОТЧЕТ О РЕЗЕРВНОМ КОПИРОВАНИИ${NC}"
echo "========================================"
echo "Время бэкапа: $(date)"
echo "Директория: $BACKUP_DIR"
echo "Имя файла: $BACKUP_NAME.tar.gz"
echo "Размер: ${FILESIZE_MB}MB"
echo "Всего бэкапов: $(ls -1 *.tar.gz 2>/dev/null | wc -l)"
echo ""
echo "Содержимое бэкапа:"
tar -tzf "$BACKUP_PATH.tar.gz" | head -10
echo "..."
echo "========================================"

echo -e "${GREEN}✅ Резервное копирование завершено успешно!${NC}"
echo -e "${YELLOW}💡 Для восстановления используйте команду: tar -xzf $BACKUP_PATH.tar.gz${NC}"