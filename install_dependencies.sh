#!/bin/bash

echo "Installing SmartCast Dependencies..."

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "Composer is not installed. Please install Composer first:"
    echo "https://getcomposer.org/download/"
    exit 1
fi

# Install dependencies
echo "Installing PHPMailer and other dependencies..."
composer install

# Set permissions (if on Linux/Unix)
if [[ "$OSTYPE" == "linux-gnu"* ]] || [[ "$OSTYPE" == "darwin"* ]]; then
    echo "Setting permissions..."
    chmod -R 755 vendor/
    chmod -R 644 vendor/*/
fi

echo "Dependencies installed successfully!"
echo ""
echo "Next steps:"
echo "1. Copy .env.example to .env"
echo "2. Configure your email settings in .env"
echo "3. Test the email functionality"
