#!/bin/bash
# Script to start MySQL and set up the database

echo "Starting MySQL..."
brew services start mysql

echo "Waiting for MySQL to start..."
sleep 5

echo "Creating database..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS star_recruiting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1

if [ $? -eq 0 ]; then
    echo "✅ MySQL is running and database created!"
    echo "You can now run: php artisan migrate"
else
    echo "❌ MySQL connection failed. Please check:"
    echo "1. Is MySQL installed? (brew install mysql)"
    echo "2. Try: brew services restart mysql"
    echo "3. Check if MySQL requires a password: mysql -u root -p"
fi

