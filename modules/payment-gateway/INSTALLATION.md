# Payment Gateway Module - Installation Guide

Complete step-by-step installation guide for the Payment Gateway Module.

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- cURL extension enabled
- JSON extension enabled
- OpenSSL extension enabled
- Composer (optional, for autoloading)

## Step 1: Copy Module Files

```bash
# Copy the entire payment-gateway directory to your project
cp -r payment-gateway /path/to/your/project/modules/

# Or if integrating into existing project
cp -r payment-gateway/src/* /path/to/your/project/src/
```

## Step 2: Database Setup

### Import Database Schema

```bash
# Import payment tables
mysql -u your_user -p your_database < modules/payment-gateway/migrations/payment_tables.sql

# Import Hubtel-specific tables (if using Hubtel)
mysql -u your_user -p your_database < modules/payment-gateway/migrations/hubtel_integration.sql
```

### Manual Database Creation

If you prefer to create tables manually, see `migrations/payment_tables.sql` for the complete schema.

## Step 3: Configuration

### Create Configuration File

```bash
# Copy example configuration
cp modules/payment-gateway/config/payment_config.example.php config/payment_config.php
```

### Edit Configuration

Edit `config/payment_config.php`:

```php
<?php

return [
    // Default gateway to use
    'default_gateway' => 'hubtel', // or 'paystack'
    
    // Fallback gateway if default fails
    'fallback_gateway' => 'paystack',
    
    // Enable/disable gateway failover
    'enable_failover' => true,
    
    // Hubtel Configuration
    'hubtel' => [
        'client_id' => getenv('HUBTEL_CLIENT_ID') ?: 'your_client_id',
        'client_secret' => getenv('HUBTEL_CLIENT_SECRET') ?: 'your_client_secret',
        'merchant_account' => getenv('HUBTEL_MERCHANT_ACCOUNT') ?: 'your_merchant_account',
        'base_url' => 'https://rmp.hubtel.com',
        'status_check_url' => 'https://api-txnstatus.hubtel.com',
        'callback_url' => 'https://yourdomain.com/api/payment/webhook/hubtel',
        'is_active' => true,
        'test_mode' => false,
        'ip_whitelist' => [] // Add Hubtel IPs for production
    ],
    
    // Paystack Configuration
    'paystack' => [
        'secret_key' => getenv('PAYSTACK_SECRET_KEY') ?: 'sk_test_xxxxx',
        'public_key' => getenv('PAYSTACK_PUBLIC_KEY') ?: 'pk_test_xxxxx',
        'webhook_secret' => getenv('PAYSTACK_WEBHOOK_SECRET') ?: '',
        'base_url' => 'https://api.paystack.co',
        'callback_url' => 'https://yourdomain.com/api/payment/webhook/paystack',
        'is_active' => true,
        'test_mode' => false
    ],
    
    // OTP Configuration (optional)
    'otp' => [
        'enabled' => false,
        'length' => 6,
        'expiry_minutes' => 5,
        'rate_limit_attempts' => 3,
        'rate_limit_window_hours' => 1,
        'sms_gateway' => 'hubtel' // or custom SMS provider
    ]
];
```

## Step 4: Environment Variables (Recommended)

Create `.env` file in your project root:

```env
# Hubtel Credentials
HUBTEL_CLIENT_ID=your_client_id_here
HUBTEL_CLIENT_SECRET=your_client_secret_here
HUBTEL_MERCHANT_ACCOUNT=your_merchant_account_here

# Paystack Credentials
PAYSTACK_SECRET_KEY=sk_live_xxxxx
PAYSTACK_PUBLIC_KEY=pk_live_xxxxx
PAYSTACK_WEBHOOK_SECRET=your_webhook_secret

# Application Settings
APP_URL=https://yourdomain.com
APP_DEBUG=false
```

## Step 5: Autoloading (Optional)

### Using Composer

Create or update `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "PaymentGateway\\": "modules/payment-gateway/src/"
        }
    }
}
```

Run composer:

```bash
composer dump-autoload
```

### Manual Autoloading

Create `autoload.php`:

```php
<?php

spl_autoload_register(function ($class) {
    $prefix = 'PaymentGateway\\';
    $base_dir = __DIR__ . '/modules/payment-gateway/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
```

## Step 6: Test Installation

### Basic Test

Create `test_payment.php`:

```php
<?php

require 'autoload.php'; // or composer autoload

use PaymentGateway\Gateways\HubtelGateway;

// Load configuration
$config = require 'config/payment_config.php';

// Initialize gateway
$gateway = new HubtelGateway($config['hubtel']);

// Test payment
$result = $gateway->initializeMobileMoneyPayment([
    'amount' => 1.00,
    'phone' => '0244000000', // Test number
    'reference' => 'TEST_' . time(),
    'description' => 'Test payment'
]);

print_r($result);
```

Run the test:

```bash
php test_payment.php
```

## Step 7: Gateway-Specific Setup

### Hubtel Setup

1. **Login to Hubtel Dashboard**
   - Go to https://unity.hubtel.com

2. **Get API Credentials**
   - Navigate to Settings > API Keys
   - Copy Client ID and Client Secret

3. **Get Merchant Account**
   - Navigate to Receive Money > Accounts
   - Copy your POS Sales ID (Merchant Account Number)

4. **Configure Webhook**
   - Navigate to Receive Money > Settings
   - Add webhook URL: `https://yourdomain.com/api/payment/webhook/hubtel`

5. **IP Whitelisting (Production)**
   - Submit your server IPs to Hubtel support
   - Add approved IPs to config: `'ip_whitelist' => ['1.2.3.4', '5.6.7.8']`

### Paystack Setup

1. **Login to Paystack Dashboard**
   - Go to https://dashboard.paystack.com

2. **Get API Keys**
   - Navigate to Settings > API Keys & Webhooks
   - Copy Secret Key and Public Key
   - For production, use Live keys (sk_live_xxx)

3. **Configure Webhook**
   - Navigate to Settings > API Keys & Webhooks
   - Add webhook URL: `https://yourdomain.com/api/payment/webhook/paystack`
   - Copy webhook secret

4. **Enable Mobile Money**
   - Ensure mobile money is enabled for your account
   - Contact Paystack support if not available

## Step 8: Create API Endpoints

Create payment endpoints in your application:

```php
<?php

// api/payment/initialize.php
require '../autoload.php';

use PaymentGateway\Gateways\HubtelGateway;

$config = require '../config/payment_config.php';
$gateway = new HubtelGateway($config['hubtel']);

$data = json_decode(file_get_contents('php://input'), true);

$result = $gateway->initializeMobileMoneyPayment($data);

header('Content-Type: application/json');
echo json_encode($result);
```

```php
<?php

// api/payment/webhook/hubtel.php
require '../../autoload.php';

use PaymentGateway\Gateways\HubtelGateway;

$config = require '../../config/payment_config.php';
$gateway = new HubtelGateway($config['hubtel']);

$payload = json_decode(file_get_contents('php://input'), true);

$result = $gateway->processWebhook($payload);

// Process the result (update database, trigger actions, etc.)

header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
```

## Step 9: Frontend Integration

Add payment form to your HTML:

```html
<form id="payment-form">
    <input type="tel" id="phone" placeholder="Phone Number" required>
    <input type="number" id="amount" placeholder="Amount" required>
    <button type="submit">Pay Now</button>
</form>

<script>
document.getElementById('payment-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const response = await fetch('/api/payment/initialize', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            phone: document.getElementById('phone').value,
            amount: document.getElementById('amount').value,
            reference: 'PAY_' + Date.now()
        })
    });
    
    const result = await response.json();
    
    if (result.success) {
        alert('Payment initiated! Please approve on your phone.');
    } else {
        alert('Error: ' + result.message);
    }
});
</script>
```

## Step 10: Testing

### Test Credentials

**Hubtel Test Mode:**
- Use test Client ID and Secret from dashboard
- Test phone numbers: Any valid Ghana number

**Paystack Test Mode:**
- Use test secret key (sk_test_xxx)
- Test phone numbers: Any valid Ghana number
- Test will not deduct real money

### Test Checklist

- [ ] Payment initialization works
- [ ] Phone number validation works
- [ ] Gateway returns proper response
- [ ] Webhook endpoint is accessible
- [ ] Webhook signature verification works (Paystack)
- [ ] IP whitelisting works (Hubtel)
- [ ] Payment verification works
- [ ] Database records are created
- [ ] Error handling works properly

## Troubleshooting

### Common Issues

**1. cURL Error**
```
Solution: Enable cURL extension in php.ini
```

**2. Database Connection Error**
```
Solution: Check database credentials and ensure tables exist
```

**3. Webhook Not Receiving Requests**
```
Solution: 
- Ensure HTTPS is enabled
- Check webhook URL is publicly accessible
- Verify URL in gateway dashboard
- Check server firewall settings
```

**4. IP Whitelisting Error (Hubtel)**
```
Solution:
- Submit server IPs to Hubtel support
- Add IPs to config once approved
- For development, leave ip_whitelist empty
```

**5. Invalid Signature (Paystack)**
```
Solution:
- Verify webhook secret is correct
- Ensure payload is not modified before verification
```

## Security Checklist

- [ ] Use environment variables for credentials
- [ ] Never commit credentials to version control
- [ ] Enable HTTPS for all endpoints
- [ ] Verify webhook signatures
- [ ] Enable IP whitelisting (Hubtel)
- [ ] Use strong database passwords
- [ ] Implement rate limiting
- [ ] Log all transactions
- [ ] Set test_mode to false in production

## Next Steps

1. Review `examples/` directory for usage examples
2. Read `INTEGRATION_GUIDE.md` for advanced integration
3. Test payment flow end-to-end
4. Configure monitoring and alerts
5. Set up backup gateway for failover

## Support

For issues:
- Check `README.md` for documentation
- Review `examples/` for code samples
- Check gateway-specific documentation:
  - Hubtel: https://developers.hubtel.com
  - Paystack: https://paystack.com/docs

## Production Deployment

Before going live:

1. Switch to live API keys
2. Set `test_mode` to `false`
3. Configure production webhook URLs
4. Enable IP whitelisting (Hubtel)
5. Test with small amounts first
6. Monitor logs for errors
7. Set up alerts for failed payments
8. Configure backup/failover gateway

---

**Installation Complete!** 🎉

You're now ready to accept mobile money payments in your application.
