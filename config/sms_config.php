<?php
/**
 * SMS Configuration
 * 
 * Configure your SMS gateways and settings here
 */

// Load env helper if not already loaded
if (!function_exists('env')) {
    require_once __DIR__ . '/../src/Helpers/env.php';
}

return [
    
    // Default SMS settings
    'default' => [
        'enabled' => true,
        'send_vote_confirmation' => true,
        'send_payment_receipt' => true,
        'send_event_reminders' => false,
        'max_retry_attempts' => 3,
        'retry_delay_seconds' => 5,
    ],
    
    // SMS Templates
    'templates' => [
        'vote_confirmation' => [
            'template' => "Thank you for voting!\n\nNominee: {nominee_name}\nEvent: {event_name}\nCategory: {category_name}\nVotes: {vote_count}\nAmount: {amount}\nReceipt: {receipt_number}\n\nThank you for your participation!",
            'variables' => ['nominee_name', 'event_name', 'category_name', 'vote_count', 'amount', 'receipt_number']
        ],
        
        'payment_receipt' => [
            'template' => "Payment Successful!\n\nAmount: {amount}\nTransaction ID: {transaction_id}\nDate: {date}\nMethod: {payment_method}\n\nThank you!",
            'variables' => ['amount', 'transaction_id', 'date', 'payment_method']
        ],
        
        'event_reminder' => [
            'template' => "Reminder: {event_name} voting is now live!\n\nVote for your favorite nominees now.\nEvent ends: {end_date}\n\nVote now!",
            'variables' => ['event_name', 'end_date']
        ],
        
        'custom' => [
            'template' => "Hello {name}! {message}",
            'variables' => ['name', 'message']
        ]
    ],
    
    // Gateway configurations (add your actual credentials)
    'gateways' => [
        
        // mNotify Configuration
        'mnotify' => [
            'name' => 'mNotify Primary',
            'type' => 'mnotify',
            'api_key' => env('MNOTIFY_API_KEY', 'your_mnotify_api_key_here'),
            'sender_id' => env('MNOTIFY_SENDER_ID', 'SmartCast'),
            'base_url' => 'https://api.mnotify.com/api/sms/quick',
            'is_active' => true,
            'priority' => 1,
            'test_phone' => env('SMS_TEST_PHONE', '233200000000'),
        ],
        
        // Hubtel Configuration
        'hubtel' => [
            'name' => 'Hubtel Backup',
            'type' => 'hubtel',
            'client_id' => env('HUBTEL_CLIENT_ID', 'your_hubtel_client_id_here'),
            'client_secret' => env('HUBTEL_CLIENT_SECRET', 'your_hubtel_client_secret_here'),
            'api_key' => env('HUBTEL_API_KEY', 'your_hubtel_api_key_here'),
            'sender_id' => env('HUBTEL_SENDER_ID', 'SmartCast'),
            'base_url' => 'https://smsc.hubtel.com/v1/messages/send',
            'is_active' => false,
            'priority' => 2,
            'test_phone' => env('SMS_TEST_PHONE', '233200000000'),
        ],
    ],
    
    // Phone number formatting
    'phone_formatting' => [
        'country_code' => '233', // Ghana
        'remove_leading_zero' => true,
        'min_length' => 9,
        'max_length' => 12,
    ],
    
    // Logging settings
    'logging' => [
        'enabled' => true,
        'log_successful' => true,
        'log_failed' => true,
        'cleanup_days' => 90, // Delete logs older than 90 days
    ],
    
    // Rate limiting
    'rate_limiting' => [
        'enabled' => true,
        'max_per_minute' => 60,
        'max_per_hour' => 1000,
        'delay_between_messages' => 100, // milliseconds
    ],
    
    // Webhook settings
    'webhooks' => [
        'enabled' => true,
        'endpoint' => '/api/sms/webhook',
        'secret_key' => env('SMS_WEBHOOK_SECRET', 'your_webhook_secret_here'),
        'verify_signature' => true,
    ],
    
    // Failover settings
    'failover' => [
        'enabled' => true,
        'max_failures_before_switch' => 3,
        'failure_window_minutes' => 10,
        'auto_reactivate_after_minutes' => 60,
    ],
    
    // Development settings
    'development' => [
        'simulate_sending' => env('SMS_SIMULATE', false),
        'log_to_file' => true,
        'test_mode' => env('APP_ENV', 'production') !== 'production',
        'allowed_test_numbers' => [
            '233200000000',
            '233500000000',
        ],
    ],
];
