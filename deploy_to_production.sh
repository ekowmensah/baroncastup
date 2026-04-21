#!/bin/bash

echo "🚀 Deploying SmartCast to Production Server..."
echo "=============================================="

# Configuration
PRODUCTION_PATH="/home/smartcast/public_html"
BACKUP_PATH="/home/smartcast/backups/$(date +%Y%m%d_%H%M%S)"

echo "📁 Production Path: $PRODUCTION_PATH"
echo "💾 Backup Path: $BACKUP_PATH"
echo ""

# Check if we're on the production server
if [ ! -d "$PRODUCTION_PATH" ]; then
    echo "❌ Production path not found. Are you on the production server?"
    echo "   Expected: $PRODUCTION_PATH"
    exit 1
fi

# Create backup
echo "💾 Creating backup..."
mkdir -p "$BACKUP_PATH"
cp -r "$PRODUCTION_PATH" "$BACKUP_PATH/"
echo "✅ Backup created at: $BACKUP_PATH"
echo ""

# Navigate to production directory
cd "$PRODUCTION_PATH"

# Check if composer is installed
echo "🔍 Checking for Composer..."
if ! command -v composer &> /dev/null; then
    echo "📦 Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    echo "✅ Composer installed"
else
    echo "✅ Composer already installed"
fi
echo ""

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
    echo "✅ Dependencies installed"
else
    echo "❌ composer.json not found!"
    exit 1
fi
echo ""

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 vendor/
chmod -R 644 vendor/*/
chmod 755 src/
chmod -R 644 src/*/
echo "✅ Permissions set"
echo ""

# Test email service
echo "📧 Testing email service..."
php -r "
require_once 'vendor/autoload.php';
require_once 'src/Core/Application.php';
use SmartCast\Services\EmailServiceFactory;
\$status = EmailServiceFactory::getStatus();
echo 'PHPMailer Available: ' . (\$status['phpmailer_available'] ? 'Yes' : 'No') . PHP_EOL;
echo 'Service Type: ' . (\$status['service_type'] ?? 'Unknown') . PHP_EOL;
"
echo ""

echo "🎉 Deployment completed successfully!"
echo ""
echo "📋 Next Steps:"
echo "1. Configure email settings in .env file"
echo "2. Test registration functionality"
echo "3. Monitor error logs for any issues"
echo ""
echo "📁 Files deployed to: $PRODUCTION_PATH"
echo "💾 Backup available at: $BACKUP_PATH"
