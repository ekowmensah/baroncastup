# Payment Gateway Module

A comprehensive, production-ready payment gateway integration module supporting Hubtel and Paystack for mobile money and card payments.

## Features

### Payment Gateways
- **Hubtel Direct Receive Money API**
  - MTN Mobile Money
  - Telecel Cash (Vodafone)
  - AirtelTigo Money
  - IP whitelisting support
  - Webhook verification

- **Paystack Payment API**
  - Mobile Money (MTN, Vodafone, AirtelTigo)
  - Card payments (Visa, Mastercard)
  - Bank transfers
  - USSD payments
  - Webhook signature verification

### Security Features
- OTP verification system
- Phone number validation
- Rate limiting (3 OTP attempts/hour)
- Bcrypt password hashing for OTPs
- Session token management (SHA-256)
- IP whitelisting (Hubtel)
- Webhook signature verification
- Single-use OTPs with 5-minute expiry

### Additional Features
- Multi-gateway support with automatic failover
- Transaction logging and tracking
- Comprehensive error handling
- Phone number normalization (Ghana/Nigeria)
- Channel auto-detection (MTN, Vodafone, AirtelTigo)
- Payment status checking
- Metadata support for custom data

## Directory Structure

```
payment-gateway/
├── README.md                          # This file
├── INSTALLATION.md                    # Installation guide
├── INTEGRATION_GUIDE.md               # Integration instructions
├── composer.json                      # Composer dependencies (optional)
├── config/
│   ├── payment_config.example.php    # Configuration template
│   └── gateway_credentials.example.php
├── src/
│   ├── Gateways/
│   │   ├── HubtelGateway.php         # Hubtel integration
│   │   ├── PaystackGateway.php       # Paystack integration
│   │   └── GatewayInterface.php      # Gateway interface
│   ├── Services/
│   │   ├── PaymentService.php        # Main payment service
│   │   ├── MoMoPaymentService.php    # Mobile money service
│   │   └── WebhookService.php        # Webhook processing
│   ├── Controllers/
│   │   └── OtpController.php         # OTP verification
│   ├── Models/
│   │   └── OtpRequest.php            # OTP model
│   └── Helpers/
│       └── PhoneValidator.php        # Phone validation
├── migrations/
│   ├── payment_tables.sql            # Database schema
│   └── hubtel_integration.sql        # Hubtel-specific tables
├── examples/
│   ├── basic_payment.php             # Basic payment example
│   ├── with_otp.php                  # Payment with OTP
│   ├── webhook_handler.php           # Webhook example
│   └── multi_gateway.php             # Multi-gateway example
├── public/
│   └── assets/
│       └── js/
│           └── otp-payment-handler.js # Frontend OTP handler
└── views/
    └── partials/
        └── otp-verification.php      # OTP UI component
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- cURL extension
- JSON extension
- OpenSSL extension
- PDO MySQL extension

## Quick Start

### 1. Installation

```bash
# Copy module to your project
cp -r payment-gateway /path/to/your/project/

# Import database schema
mysql -u your_user -p your_database < migrations/payment_tables.sql
mysql -u your_user -p your_database < migrations/hubtel_integration.sql
```

### 2. Configuration

```php
// config/payment_config.php
return [
    'default_gateway' => 'hubtel',
    'fallback_gateway' => 'paystack',
    
    'hubtel' => [
        'client_id' => 'your_client_id',
        'client_secret' => 'your_client_secret',
        'merchant_account' => 'your_merchant_account',
        'base_url' => 'https://rmp.hubtel.com',
        'is_active' => true
    ],
    
    'paystack' => [
        'secret_key' => 'your_secret_key',
        'public_key' => 'your_public_key',
        'base_url' => 'https://api.paystack.co',
        'is_active' => true
    ]
];
```

### 3. Basic Usage

```php
use PaymentGateway\Services\PaymentService;

// Initialize payment service
$paymentService = new PaymentService($config);

// Process payment
$result = $paymentService->processPayment([
    'amount' => 10.00,
    'phone' => '0545644749',
    'email' => 'user@example.com',
    'reference' => 'TXN_' . time(),
    'description' => 'Payment for service',
    'gateway' => 'hubtel' // or 'paystack'
]);

if ($result['success']) {
    echo "Payment initiated: " . $result['reference'];
} else {
    echo "Payment failed: " . $result['message'];
}
```

## Payment Flow

### Standard Payment Flow
1. User initiates payment
2. System validates phone number
3. Payment request sent to gateway
4. User receives mobile money prompt
5. User approves payment on phone
6. Gateway sends webhook notification
7. System verifies and processes payment

### Payment with OTP Flow
1. User enters phone number
2. System sends 6-digit OTP via SMS
3. User enters OTP
4. System verifies OTP
5. Phone number locked for payment
6. Payment initiated with gateway
7. User approves on phone
8. Payment processed

## API Endpoints

### Payment Endpoints
- `POST /api/payment/initialize` - Initialize payment
- `GET /api/payment/status/{reference}` - Check payment status
- `POST /api/payment/webhook/hubtel` - Hubtel webhook
- `POST /api/payment/webhook/paystack` - Paystack webhook

### OTP Endpoints
- `POST /api/otp/send-payment-otp` - Send OTP
- `POST /api/otp/verify-payment-otp` - Verify OTP

## Gateway-Specific Features

### Hubtel
- **Channels:** MTN, Telecel, AirtelTigo
- **IP Whitelisting:** Required for production
- **Response Codes:**
  - `0000` - Success
  - `2001` - Pending (user hasn't approved)
  - Other codes indicate errors

### Paystack
- **Channels:** Mobile Money, Card, Bank Transfer, USSD
- **Webhook Signature:** Verified using secret key
- **Response:** Returns authorization URL for redirect

## Security Best Practices

1. **Never commit credentials** - Use environment variables
2. **Enable IP whitelisting** - For Hubtel webhooks
3. **Verify webhook signatures** - Prevent fraudulent requests
4. **Use HTTPS** - For all webhook endpoints
5. **Implement rate limiting** - Prevent abuse
6. **Log all transactions** - For audit trail
7. **Validate phone numbers** - Before processing
8. **Use OTP for sensitive operations** - Additional security layer

## Error Handling

The module provides comprehensive error handling:

```php
try {
    $result = $paymentService->processPayment($data);
} catch (\Exception $e) {
    error_log("Payment error: " . $e->getMessage());
    // Handle error appropriately
}
```

Common error codes:
- `INVALID_PHONE` - Invalid phone number format
- `GATEWAY_ERROR` - Gateway API error
- `INSUFFICIENT_BALANCE` - User has insufficient balance
- `TRANSACTION_FAILED` - Transaction failed
- `OTP_EXPIRED` - OTP has expired
- `OTP_INVALID` - Invalid OTP code

## Testing

### Test Mode
Both gateways support test mode:

```php
$config['hubtel']['test_mode'] = true;
$config['paystack']['test_mode'] = true;
```

### Test Credentials
- **Hubtel:** Use test client ID and secret from dashboard
- **Paystack:** Use test secret key (starts with `sk_test_`)

### Test Phone Numbers
- MTN: 0244000000
- Vodafone: 0200000000
- AirtelTigo: 0270000000

## Production Deployment

### Checklist
- [ ] Update all API credentials
- [ ] Configure webhook URLs in gateway dashboards
- [ ] Enable IP whitelisting (Hubtel)
- [ ] Set up SSL certificates
- [ ] Configure SMS gateway for OTP
- [ ] Test payment flow end-to-end
- [ ] Set up monitoring and alerts
- [ ] Configure backup gateway
- [ ] Review security settings
- [ ] Set `test_mode` to `false`

### Webhook URLs
- Hubtel: `https://yourdomain.com/api/payment/webhook/hubtel`
- Paystack: `https://yourdomain.com/api/payment/webhook/paystack`

## Support

For gateway-specific issues:
- **Hubtel:** https://developers.hubtel.com
- **Paystack:** https://paystack.com/docs

## License

Free to use in your projects.

## Changelog

### Version 1.0.0 (2024)
- Initial release
- Hubtel Direct Receive Money integration
- Paystack mobile money integration
- OTP verification system
- Webhook processing
- Multi-gateway support
