@echo off
echo Installing SmartCast Dependencies...

REM Check if composer is installed
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Composer is not installed. Please install Composer first:
    echo https://getcomposer.org/download/
    pause
    exit /b 1
)

REM Install dependencies
echo Installing PHPMailer and other dependencies...
composer install

echo Dependencies installed successfully!
echo.
echo Next steps:
echo 1. Copy .env.example to .env
echo 2. Configure your email settings in .env
echo 3. Test the email functionality
pause
