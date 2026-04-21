# SmartCast Modules - Quick Start Guide

This guide will help you quickly integrate the Payment Gateway and USSD Integration modules into your project.

## 📦 What's Included

### Payment Gateway Module
Complete payment integration for:
- ✅ Hubtel Mobile Money (MTN, Telecel, AirtelTigo)
- ✅ Paystack Mobile Money & Card Payments
- ✅ OTP Verification System
- ✅ Webhook Processing
- ✅ Multi-gateway Failover

### USSD Integration Module
Complete USSD system for:
- ✅ Multi-tenant USSD Codes
- ✅ Dynamic Menu Generation
- ✅ Payment Integration (Hubtel)
- ✅ Session Management
- ✅ Service Fulfillment

---

## 🚀 Quick Start - Payment Gateway

### 1. Copy Module Files

```bash
# Copy to your project
cp -r modules/payment-gateway /path/to/your/project/
```

### 2. Install Database

```bash
# Import database schema
mysql -u root -p your_database < modules/payment-gateway/migrations/hubtel_integration.sql
```

### 3. Configure

```bash
# Copy configuration
cp modules/payment-gateway/config/payment_config.example.php config/payment_config.php

# Edit config/payment_config.php with your credentials
```

### 4. Basic Usage

```php
<?php
require 'vendor/autoload.php';

use PaymentGateway\Gateways\HubtelGateway;

$config = require 'config/payment_config.php';
$gateway = new HubtelGateway($config['hubtel']);

// Initialize payment
$result = $gateway->initializeMobileMoneyPayment([
    'amount' => 10.00,
    'phone' => '0545644749',
    'reference' => 'PAY_' . time(),
    'description' => 'Payment for service'
]);

if ($result['success']) {
    echo "Payment initiated! Reference: " . $result['client_reference'];
}
```

### 5. Set Up Webhooks

```php
<?php
// api/payment/webhook/hubtel.php

use PaymentGateway\Gateways\HubtelGateway;

$config = require '../config/payment_config.php';
$gateway = new HubtelGateway($config['hubtel']);

$payload = json_decode(file_get_contents('php://input'), true);
$result = $gateway->processWebhook($payload);

// Update your database based on result
if ($result['action'] === 'payment_confirmed') {
    // Payment successful - deliver service
}

http_response_code(200);
echo json_encode(['status' => 'success']);
```

---

## 🚀 Quick Start - USSD Integration

### 1. Copy Module Files

```bash
# Copy to your project
cp -r modules/ussd-integration /path/to/your/project/
```

### 2. Install Database

```bash
# Import database schema
mysql -u root -p your_database < modules/ussd-integration/migrations/ussd_tables.sql
mysql -u root -p your_database < modules/ussd-integration/migrations/add_multi_tenant_ussd.sql
```

### 3. Configure

```bash
# Copy configuration
cp modules/ussd-integration/config/ussd_config.example.php config/ussd_config.php

# Edit config/ussd_config.php
```

### 4. Basic Usage

```php
<?php
// api/ussd/callback.php

use UssdIntegration\Controllers\UssdController;

$controller = new UssdController();
$controller->handleRequest();
```

### 5. Configure Hubtel Dashboard

1. Login to https://unity.hubtel.com
2. Create USSD Application
3. Set Service Code: `*711*734#`
4. Set Callback URL: `https://yourdomain.com/api/ussd/callback`
5. Activate application

---

## 🔧 Configuration

### Environment Variables (.env)

```env
# Hubtel Credentials
HUBTEL_CLIENT_ID=your_client_id
HUBTEL_CLIENT_SECRET=your_client_secret
HUBTEL_MERCHANT_ACCOUNT=your_merchant_account

# Paystack Credentials
PAYSTACK_SECRET_KEY=sk_live_xxxxx
PAYSTACK_PUBLIC_KEY=pk_live_xxxxx
PAYSTACK_WEBHOOK_SECRET=your_webhook_secret

# Application
APP_URL=https://yourdomain.com
APP_DEBUG=false
```

### Database Connection

```php
// config/database.php
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_NAME') ?: 'your_database',
    'username' => getenv('DB_USER') ?: 'your_username',
    'password' => getenv('DB_PASS') ?: 'your_password',
];
```

---

## 📝 Common Use Cases

### Use Case 1: Simple Payment

```php
$gateway = new HubtelGateway($config['hubtel']);

$result = $gateway->initializeMobileMoneyPayment([
    'amount' => 50.00,
    'phone' => '0244123456',
    'reference' => 'ORDER_12345',
    'description' => 'Product purchase'
]);
```

### Use Case 2: Payment with OTP

```php
// 1. Send OTP
$otpController = new OtpController();
$otpResult = $otpController->sendPaymentOtp(['phone' => '0244123456']);

// 2. Verify OTP
$verifyResult = $otpController->verifyPaymentOtp([
    'phone' => '0244123456',
    'otp' => '123456',
    'session_token' => $otpResult['session_token']
]);

// 3. Process payment if OTP verified
if ($verifyResult['success']) {
    $paymentResult = $gateway->initializeMobileMoneyPayment([...]);
}
```

### Use Case 3: Multi-Gateway with Failover

```php
$primaryGateway = new HubtelGateway($config['hubtel']);
$fallbackGateway = new PaystackGateway($config['paystack']);

// Try primary gateway
$result = $primaryGateway->initializeMobileMoneyPayment($data);

// Fallback if primary fails
if (!$result['success']) {
    $result = $fallbackGateway->initializeMobileMoneyPayment($data);
}
```

### Use Case 4: USSD Menu

```php
// UssdController automatically handles:
// - Session management
// - Menu navigation
// - Payment integration
// - Service fulfillment

// Just configure your menus in the database
// and the controller handles the rest
```

---

## 🧪 Testing

### Test Mode

```php
// In config/payment_config.php
'hubtel' => [
    'test_mode' => true,  // Enable test mode
    // ...
],
```

### Test Payment

```bash
# Run basic payment example
php modules/payment-gateway/examples/basic_payment.php
```

### Test USSD

```bash
# Simulate USSD request
curl -X POST https://yourdomain.com/api/ussd/callback \
  -H "Content-Type: application/json" \
  -d '{
    "SessionId": "test123",
    "ServiceCode": "711*734",
    "Mobile": "233244000000",
    "Message": "",
    "Type": "Initiation"
  }'
```

---

## 🔒 Security Checklist

Before going to production:

- [ ] Use environment variables for credentials
- [ ] Enable HTTPS for all endpoints
- [ ] Configure IP whitelisting (Hubtel)
- [ ] Verify webhook signatures
- [ ] Set `test_mode` to `false`
- [ ] Enable rate limiting
- [ ] Set up error logging
- [ ] Configure monitoring alerts
- [ ] Test failover scenarios
- [ ] Review security settings

---

## 📚 Documentation

### Payment Gateway
- **README.md** - Complete documentation
- **INSTALLATION.md** - Installation guide
- **examples/** - Code examples

### USSD Integration
- **README.md** - Complete documentation
- **INSTALLATION.md** - Installation guide
- **examples/** - Code examples

---

## 🆘 Troubleshooting

### Payment Issues

**Problem:** Payment initialization fails
```
Solution: Check API credentials in config
Verify: curl test to gateway API
```

**Problem:** Webhook not receiving requests
```
Solution: 
1. Ensure HTTPS is enabled
2. Check webhook URL in gateway dashboard
3. Verify firewall settings
```

**Problem:** IP whitelisting error (Hubtel)
```
Solution:
1. Submit server IPs to Hubtel support
2. Add IPs to config once approved
```

### USSD Issues

**Problem:** UUE error when dialing USSD
```
Solution:
1. Verify service code in Hubtel dashboard: *711*734#
2. Check application status is "Active"
3. Verify callback URL is accessible
```

**Problem:** Session timeout
```
Solution:
1. Increase session timeout in config
2. Check session cleanup cron job
```

---

## 🎯 Next Steps

### For Payment Gateway

1. ✅ Set up production credentials
2. ✅ Configure webhook endpoints
3. ✅ Test with small amounts
4. ✅ Implement payment verification
5. ✅ Set up monitoring
6. ✅ Configure backup gateway

### For USSD Integration

1. ✅ Configure Hubtel USSD application
2. ✅ Design menu structure
3. ✅ Test USSD flow
4. ✅ Integrate payment
5. ✅ Test service fulfillment
6. ✅ Go live

---

## 💡 Tips

1. **Always test in test mode first**
2. **Use unique references for each transaction**
3. **Implement proper error handling**
4. **Log all transactions**
5. **Monitor payment success rates**
6. **Have a fallback gateway**
7. **Keep credentials secure**
8. **Regular security audits**
9. **Monitor webhook logs**
10. **Document your integration**

---

## 📞 Support

### Gateway Documentation
- **Hubtel:** https://developers.hubtel.com
- **Paystack:** https://paystack.com/docs

### Module Documentation
- See individual module README files
- Check examples directory
- Review integration guides

---

## ✅ Success!

You're now ready to accept payments and handle USSD interactions in your application!

**Key Features:**
- ✅ Production-ready code
- ✅ Comprehensive error handling
- ✅ Security best practices
- ✅ Well-documented
- ✅ Easy to integrate
- ✅ Scalable architecture

**Happy Coding! 🚀**
