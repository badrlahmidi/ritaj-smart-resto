@echo off
echo ================================
echo Installation des Settings Ritaj
echo ================================
echo.

echo [1/3] Dump autoload Composer...
call composer dump-autoload

echo.
echo [2/3] Clear config cache...
call php artisan config:clear

echo.
echo [3/3] Create storage link...
call php artisan storage:link

echo.
echo ================================
echo Installation terminee !
echo ================================
pause
