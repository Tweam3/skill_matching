#!/bin/bash

# Laravel Skill Matching - Git Bash Startup Script

# Add XAMPP tools to PATH
export PATH="/e/XAMPP/php:$PATH"
export PATH="/e/XAMPP/mysql/bin:$PATH"

# MySQL data directory
MYSQL_DATADIR="/e/XAMPP/mysql/data"

# Function to start MySQL
start_mysql() {
    echo "Starting MySQL..."
    if ! netstat -ano | grep -q ':3306'; then
        mysqld --console --datadir="$MYSQL_DATADIR" &
        sleep 5
        echo "MySQL started."
    else
        echo "MySQL is already running."
    fi
}

# Function to stop MySQL
stop_mysql() {
    echo "Stopping MySQL..."
    taskkill /F /IM mysqld.exe 2>/dev/null
    echo "MySQL stopped."
}

# Main menu
echo "================================"
echo "Skill Matching System - Laravel"
echo "================================"
echo ""
echo "1. Start MySQL + Laravel Server"
echo "2. Start MySQL only"
echo "3. Stop MySQL"
echo "4. Run migrations"
echo "5. Seed database"
echo "6. Exit"
echo ""
read -p "Choose an option: " choice

case $choice in
    1)
        start_mysql
        echo "Starting Laravel server..."
        php artisan serve --host=0.0.0.0 --port=8000
        ;;
    2)
        start_mysql
        ;;
    3)
        stop_mysql
        ;;
    4)
        echo "Running migrations..."
        php artisan migrate --force
        ;;
    5)
        echo "Seeding database..."
        php artisan db:seed --force
        ;;
    6)
        echo "Goodbye!"
        exit 0
        ;;
    *)
        echo "Invalid option."
        exit 1
        ;;
esac
