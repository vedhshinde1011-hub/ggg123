# Manual Installation Steps

## Step 1: Install PHP
1. Download PHP from: https://windows.php.net/download/
2. Extract to C:\php
3. Add C:\php to your Windows PATH

## Step 2: Install Composer
1. Download from: https://getcomposer.org/Composer-Setup.exe
2. Run the installer
3. It will automatically detect PHP

## Step 3: Install Dependencies
Open Command Prompt in your project folder and run:
```
composer install
```

## Alternative: Use XAMPP
1. Download XAMPP from: https://www.apachefriends.org/
2. Install XAMPP (includes PHP, Apache, MySQL)
3. Start Apache from XAMPP Control Panel
4. Place your website in C:\xampp\htdocs\
5. Open Command Prompt in your project folder
6. Run: composer install

## Quick Test
After installation, test with:
```
php -v
composer --version
```