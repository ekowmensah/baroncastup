# SMS Gateway Setup Guide

This guide will help you set up SMS notifications for the SmartCast voting system using mNotify and Hubtel gateways.

## 🚀 Quick Setup

### 1. Database Setup

Run the SQL migration to create SMS tables:

```sql
-- Run this in your MySQL database
SOURCE database/migrations/create_sms_tables.sql;
```

### 2. Configure Environment Variables

Add these to your `.env` file or environment:

```env
# mNotify Configuration
MNOTIFY_API_KEY=your_mnotify_api_key_here
MNOTIFY_SENDER_ID=SmartCast

# Hubtel Configuration  
HUBTEL_CLIENT_ID=your_hubtel_client_id_here
HUBTEL_CLIENT_SECRET=your_hubtel_client_secret_here
HUBTEL_API_KEY=your_hubtel_api_key_here
HUBTEL_SENDER_ID=SmartCast

# General SMS Settings
SMS_TEST_PHONE=233200000000
SMS_WEBHOOK_SECRET=your_webhook_secret_here
SMS_SIMULATE=false
```

### 3. Add SMS Gateways

#### Option A: Via SuperAdmin Interface
1. Go to `/superadmin/sms/gateways`
2. Click "Add Gateway"
3. Fill in your gateway credentials
4. Test the gateway

#### Option B: Via Database
```sql
-- Add mNotify Gateway
INSERT INTO sms_gateways (name, type, api_key, sender_id, is_active, priority) 
VALUES ('mNotify Primary', 'mnotify', 'your_api_key', 'SmartCast', 1, 1);

-- Add Hubtel Gateway  
INSERT INTO sms_gateways (name, type, client_id, client_secret, api_key, sender_id, is_active, priority)
VALUES ('Hubtel Backup', 'hubtel', 'client_id', 'client_secret', 'api_key', 'SmartCast', 1, 2);
```

### 4. Integration with Vote Processing

Add SMS sending to your vote completion process:

```php
use SmartCast\Services\VoteCompletionService;

// After successful payment/vote
$completionService = new VoteCompletionService();
$result = $completionService->processVoteCompletion($transactionId, [
    'phone' => $voterPhone
]);
```

## 📱 Gateway Setup

### mNotify Setup

1. **Get API Credentials:**
   - Visit [mNotify.com](https://mnotify.com)
   - Create account and get API key
   - Register your sender ID

2. **Configuration:**
   ```php
   'mnotify' => [
       'api_key' => 'your_mnotify_api_key',
       'sender_id' => 'YourBrand', // Max 11 characters
   ]
   ```

### Hubtel Setup

1. **Get API Credentials:**
   - Visit [Hubtel.com](https://hubtel.com)
   - Create developer account
   - Get Client ID, Client Secret, and API Key
   - Register sender ID

2. **Configuration:**
   ```php
   'hubtel' => [
       'client_id' => 'your_client_id',
       'client_secret' => 'your_client_secret', 
       'api_key' => 'your_api_key',
       'sender_id' => 'YourBrand',
   ]
   ```

## 🔧 Usage Examples

### 1. Send Vote Confirmation SMS

```php
use SmartCast\Services\SmsService;

$smsService = new SmsService();

$voteData = [
    'phone' => '233200000000',
    'nominee_name' => 'John Doe',
    'event_name' => 'Best Artist 2024',
    'category_name' => 'Male Artist',
    'vote_count' => 5,
    'amount' => 5.00,
    'receipt_number' => 'SC241014000123ABC'
];

$result = $smsService->sendVoteConfirmationSms($voteData);
```

### 2. Manual SMS Sending

```php
$smsService = new SmsService();
$gateway = $smsService->getActiveGateway();

$result = $smsService->sendSms($gateway, '233200000000', 'Your custom message here');
```

### 3. Bulk SMS

```php
$phones = ['233200000000', '233500000000', '233240000000'];
$message = 'Voting is now live! Cast your votes now.';

$results = $smsService->sendBulkSms($phones, $message);
```

## 📊 SMS Templates

Default templates are included, but you can customize them:

### Vote Confirmation Template
```
Thank you for voting!

Nominee: {nominee_name}
Event: {event_name}  
Category: {category_name}
Votes: {vote_count}
Amount: {amount}
Receipt: {receipt_number}

Thank you for your participation!
```

### Available Variables
- `{nominee_name}` - Contestant name
- `{event_name}` - Event name
- `{category_name}` - Category name  
- `{vote_count}` - Number of votes
- `{amount}` - Amount paid
- `{receipt_number}` - Receipt/transaction reference
- `{date}` - Current date/time

## 🔍 Testing

### Test Gateway Connection
```php
$smsService = new SmsService();
$result = $smsService->testGateway($gatewayId, '233200000000');
```

### Test via SuperAdmin Interface
1. Go to `/superadmin/sms/gateways`
2. Click test button for your gateway
3. Enter test phone number
4. Check if SMS is received

## 📈 Monitoring

### View SMS Statistics
- Total SMS sent
- Success rate by gateway
- Failed messages
- Daily/monthly reports

### Access via SuperAdmin Panel
- `/superadmin/sms/statistics` - View statistics
- `/superadmin/sms/logs` - View SMS logs
- `/superadmin/sms/gateways` - Manage gateways

## 🚨 Troubleshooting

### Common Issues

1. **SMS Not Sending**
   - Check gateway credentials
   - Verify phone number format
   - Check gateway balance/credits
   - Review error logs

2. **Invalid Phone Numbers**
   - Ensure international format (233XXXXXXXXX)
   - Remove spaces and special characters
   - Verify country code

3. **Gateway Errors**
   - Check API credentials
   - Verify sender ID registration
   - Test with different gateway

### Error Logs
Check logs in:
- `sms_logs` table in database
- Application error logs
- Gateway response logs

## 🔒 Security

### Best Practices
1. Store API keys securely (environment variables)
2. Use HTTPS for all API calls
3. Validate webhook signatures
4. Implement rate limiting
5. Monitor for suspicious activity

### Webhook Security
```php
// Verify webhook signature
$signature = $_SERVER['HTTP_X_SMS_SIGNATURE'] ?? '';
$payload = file_get_contents('php://input');
$expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

if (!hash_equals($signature, $expectedSignature)) {
    http_response_code(401);
    exit('Invalid signature');
}
```

## 📞 Support

### Gateway Support
- **mNotify:** [support@mnotify.com](mailto:support@mnotify.com)
- **Hubtel:** [developers@hubtel.com](mailto:developers@hubtel.com)

### Documentation
- mNotify API: [https://mnotify.com/api-docs](https://mnotify.com/api-docs)
- Hubtel API: [https://developers.hubtel.com](https://developers.hubtel.com)

## 🎯 Next Steps

1. Set up your gateway credentials
2. Run database migrations
3. Test SMS sending
4. Integrate with vote processing
5. Monitor SMS delivery
6. Set up backup gateways

Happy SMS sending! 📱✨
