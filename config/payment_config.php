<?php

/**
 * Payment Gateway Configuration
 * 
 * This file contains configuration for all payment gateways
 * Add your API keys to .env file for security
 */

return [
    // Paystack Configuration (Ghana - Cards & Mobile Money)
    'paystack' => [
        'secret_key' => getenv('PAYSTACK_SECRET_KEY') ?: '',
        'public_key' => getenv('PAYSTACK_PUBLIC_KEY') ?: '',
        'webhook_secret' => getenv('PAYSTACK_WEBHOOK_SECRET') ?: '',
        'api_url' => 'https://api.paystack.co',
        'enabled' => true,
        'countries' => ['GH', 'NG', 'ZA', 'KE'],
        'payment_methods' => ['card', 'mobile_money', 'bank_transfer']
    ],
    
    // Hubtel Configuration (Ghana - Mobile Money & USSD)
    'hubtel' => [
        'client_id' => getenv('HUBTEL_CLIENT_ID') ?: '',
        'client_secret' => getenv('HUBTEL_CLIENT_SECRET') ?: '',
        'merchant_account' => getenv('HUBTEL_MERCHANT_ACCOUNT') ?: '',
        'api_url' => 'https://api.hubtel.com/v2',
        'ussd_api_url' => 'https://ussd.hubtel.com/api',
        'enabled' => true,
        'countries' => ['GH'],
        'payment_methods' => ['mobile_money', 'ussd']
    ],
    
    // Flutterwave Configuration (Multi-country support)
    'flutterwave' => [
        'client_id' => getenv('FLUTTERWAVE_CLIENT_ID') ?: '',
        'client_secret' => getenv('FLUTTERWAVE_CLIENT_SECRET') ?: '',
        'encryption_key' => getenv('FLUTTERWAVE_ENCRYPTION_KEY') ?: '',
        'webhook_secret' => getenv('FLUTTERWAVE_WEBHOOK_SECRET') ?: '',
        'api_url' => getenv('FLUTTERWAVE_API_URL') ?: 'https://api.flutterwave.com',
        'sandbox' => getenv('FLUTTERWAVE_SANDBOX') === 'true',
        'enabled' => true,
        'countries' => ['GH', 'NG', 'KE', 'UG', 'RW', 'TZ', 'ZM', 'ZA'],
        'payment_methods' => ['mobile_money', 'card', 'bank_transfer', 'ussd'],
        
        // Currency mapping
        'currencies' => [
            'GH' => 'GHS',
            'NG' => 'NGN',
            'KE' => 'KES',
            'UG' => 'UGX',
            'RW' => 'RWF',
            'TZ' => 'TZS',
            'ZM' => 'ZMW',
            'ZA' => 'ZAR'
        ],
        
        // Mobile money networks per country
        'networks' => [
            'GH' => ['MTN', 'VODAFONE', 'AIRTELTIGO'],
            'NG' => ['MTN', 'AIRTEL', 'GLO', '9MOBILE'],
            'KE' => ['MPESA', 'AIRTEL'],
            'UG' => ['MTN', 'AIRTEL'],
            'RW' => ['MTN', 'AIRTEL'],
            'TZ' => ['MPESA', 'TIGO', 'AIRTEL'],
            'ZM' => ['MTN', 'AIRTEL'],
            'ZA' => ['VODACOM']
        ]
    ],
    
    // Gateway Priority (order of fallback)
    'gateway_priority' => [
        'GH' => ['hubtel', 'paystack', 'flutterwave'],  // Ghana: Hubtel first
        'NG' => ['flutterwave', 'paystack'],             // Nigeria: Flutterwave first
        'default' => ['flutterwave']                     // Other countries: Flutterwave only
    ],
    
    // Webhook URLs
    'webhook_urls' => [
        'paystack' => APP_URL . '/api/payment/webhook/paystack',
        'hubtel' => APP_URL . '/api/payment/webhook/hubtel',
        'flutterwave' => APP_URL . '/api/payment/webhook/flutterwave'
    ],
    
    // General Settings
    'settings' => [
        'default_currency' => 'GHS',
        'timeout' => 30, // seconds
        'retry_attempts' => 3,
        'enable_logging' => true,
        'log_file' => __DIR__ . '/../logs/payment.log'
    ]
];
