@echo off
echo CAT Laravel System - Quick Start
echo ===================================

echo.
echo Checking setup...
php setup.php

echo.
echo Starting PHP Development Server...
echo Access: http://localhost:8000
echo Press Ctrl+C to stop
echo.

php -S localhost:8000 -t public
