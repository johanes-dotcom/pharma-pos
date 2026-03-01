@echo off
cd /d "C:\Users\asus\OneDrive\Desktop\baru\pharma-pos"
"C:\xampp\php\php.exe" -r "copy('https://getcomposer.org/installer', 'composer-setup.php')"
"C:\xampp\php\php.exe" composer-setup.php
"C:\xampp\php\php.exe" composer.phar install
