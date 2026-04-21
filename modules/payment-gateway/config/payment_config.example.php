<?php

/**
 * Payment Gateway Configuration
 * 
 * Copy this file to payment_config.php and update with your credentials
 * Never commit payment_config.php to version control
 */

return [
    // Default gateway to use for payments
    'default_gateway' => 'hubtel', // Options: 'hubtel', 'paystack'
    
    // Fallback gateway if default fails
    'fallback_gateway' => 'paystack',
    
    // Enable automatic failover to fallback gateway
    'enable_failover' => true,
    
    // Maximum retry attempts for failed payments
    'max_retry_attempts' => 3,
    
    // Payment timeout in seconds
    'payment_timeout' => 300, // 5 minutes
    
    // ============================================
    // Hubtel Configuration
    // ============================================
    'hubtel' => [
        // API Credentials (get from Hubtel dashboard)
        'client_id' => getenv('HUBTEL_CLIENT_ID') ?: '',
        'client_secret' => getenv('HUBTEL_CLIENT_SECRET') ?: '',
        'merchant_account' => getenv('HUBTEL_MERCHANT_ACCOUNT') ?: '', // POS Sales ID
        
        // API URLs
        'base_url' => 'https://rmp.hubtel.com',
        'status_check_url' => 'https://api-txnstatus.hubtel.com',
        
        // Webhook Configuration
        'callback_url' => getenv('APP_URL') . '/api/payment/webhook/hubtel',
        
        // IP Whitelisting (add your server IPs for production)
        'ip_whitelist' => [
            // '1.2.3.4',
            // '5.6.7.8'
        ],
        
        // Currency
        'currency' => 'GHS',
        
        // Gateway Status
        'is_active' => true,
        'test_mode' => true, // Set to false in production
        
        // Supported Networks
        'supported_networks' => ['mtn-gh', 'vodafone-gh', 'tigo-gh'],
        
        // Transaction Limits
        'min_amount' => 1.00,
        'max_amount' => 10000.00,
    ],
    
    // ============================================
    // Paystack Configuration
    // ============================================
    'paystack' => [
        // API Credentials (get from Paystack dashboard)
        'secret_key' => getenv('PAYSTACK_SECRET_KEY') ?: 'sk_test_xxxxx',
        'public_key' => getenv('PAYSTACK_PUBLIC_KEY') ?: 'pk_test_xxxxx',
        'webhook_secret' => getenv('PAYSTACK_WEBHOOK_SECRET') ?: '',
        
        // API URL
        'base_url' => 'https://api.paystack.co',
        
        // Webhook Configuration
        'callback_url' => getenv('APP_URL') . '/api/payment/webhook/paystack',
        
        // Currency
        'currency' => 'GHS',
        
        // Gateway Status
        'is_active' => true,
        'test_mode' => true, // Set to false in production
        
        // Supported Channels
        'supported_channels' => ['mobile_money', 'card', 'bank', 'ussd'],
        
        // Transaction Limits
        'min_amount' => 1.00,
        'max_amount' => 10000.00,
    ],
    
    // ============================================
    // OTP Configuration (Optional)
    // ============================================
    'otp' => [
        // Enable OTP verification before payment
        'enabled' => false,
        
        // OTP Settings
        'length' => 6,
        'expiry_minutes' => 5,
        'session_expiry_minutes' => 10,
        
        // Rate Limiting
        'rate_limit_attempts' => 3,
        'rate_limit_window_hours' => 1,
        
        // SMS Gateway for OTP delivery
        'sms_gateway' => 'hubtel', // Options: 'hubtel', 'custom'
        
        // Skip OTP for registered users
        'skip_for_registered_users' => true,
    ],
    
    // ============================================
    // Database Configuration
    // ============================================
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'database' => getenv('DB_NAME') ?: 'your_database',
        'username' => getenv('DB_USER') ?: 'your_username',
        'password' => getenv('DB_PASS') ?: 'your_password',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    
    // ============================================
    // Logging Configuration
    // ============================================
    'logging' => [
        // Enable detailed logging
        'enabled' => true,
        
        // Log file path
        'log_file' => __DIR__ . '/../logs/payment.log',
        
        // Log level (debug, info, warning, error)
        'log_level' => 'info',
        
        // Log to database
        'log_to_database' => true,
        
        // Log sensitive data (disable in production)
        'log_sensitive_data' => false,
    ],
    
    // ============================================
    // Security Configuration
    // ============================================
    'security' => [
        // Verify webhook signatures
        'verify_webhook_signatures' => true,
        
        // Require HTTPS for webhooks
        'require_https' => true,
        
        // IP whitelisting for webhooks
        'enable_ip_whitelist' => false,
        
        // Rate limiting
        'enable_rate_limiting' => true,
        'rate_limit_requests' => 100,
        'rate_limit_window_minutes' => 60,
    ],
    
    // ============================================
    // Notification Configuration
    // ============================================
    'notifications' => [
        // Send email notifications
        'email_enabled' => true,
        'email_from' => 'noreply@yourdomain.com',
        'email_admin' => 'admin@yourdomain.com',
        
        // Send SMS notifications
        'sms_enabled' => false,
        
        // Webhook notifications
        'webhook_enabled' => false,
        'webhook_url' => '',
    ],
    
    // ============================================
    // Testing Configuration
    // ============================================
    'testing' => [
        // Test mode settings
        'test_mode' => true,
        
        // Test phone numbers (won't charge real money)
        'test_phone_numbers' => [
            '0244000000', // MTN
            '0200000000', // Vodafone
            '0270000000', // AirtelTigo
        ],
        
        // Test amounts
        'test_amounts' => [1.00, 5.00, 10.00],
        
        // Mock gateway responses
        'mock_responses' => false,
    ],
];
