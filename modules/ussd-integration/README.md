# USSD Integration Module

A complete USSD menu system with multi-tenant support, session management, and payment integration for Hubtel Programmable Services API.

## Features

### Core Features
- **Multi-tenant USSD codes** - Support multiple tenants on single USSD code
- **Dynamic menu generation** - Build menus from database
- **Session management** - Track user sessions and state
- **Input validation** - Validate user inputs
- **Pagination support** - Handle long lists
- **Shortcode support** - Direct access via codes

### Payment Integration
- **Hubtel USSD Payments** - AddToCart integration
- **Service Fulfillment** - Handle payment callbacks
- **Transaction tracking** - Complete payment flow
- **Order management** - Track orders and payments

### Advanced Features
- **Shared USSD codes** - Multiple tenants on one code (e.g., *711*734#, *711*735#)
- **Custom vote amounts** - Users can enter custom quantities (1-10,000)
- **Bundle support** - Predefined packages
- **Event selection** - Browse and select from multiple events
- **Contestant selection** - Choose from contestants/nominees

## Directory Structure

```
ussd-integration/
├── README.md                          # This file
├── INSTALLATION.md                    # Installation guide
├── INTEGRATION_GUIDE.md               # Integration instructions
├── composer.json                      # Composer dependencies (optional)
├── config/
│   └── ussd_config.example.php       # Configuration template
├── src/
│   ├── Controllers/
│   │   ├── UssdController.php        # Main USSD handler
│   │   └── UssdManagementController.php # USSD management
│   ├── Models/
│   │   └── UssdSession.php           # Session model
│   ├── Helpers/
│   │   └── UssdHelper.php            # Helper functions
│   └── Services/
│       └── UssdMenuService.php       # Menu generation
├── migrations/
│   ├── ussd_tables.sql               # Database schema
│   ├── add_multi_tenant_ussd.sql     # Multi-tenant support
│   └── add_shared_ussd_codes.sql     # Shared codes support
├── examples/
│   ├── basic_menu.php                # Basic menu example
│   ├── with_payment.php              # Payment integration
│   ├── multi_tenant.php              # Multi-tenant example
│   └── custom_flow.php               # Custom flow example
└── docs/
    ├── HUBTEL_SETUP.md               # Hubtel dashboard setup
    ├── MENU_STRUCTURE.md             # Menu design guide
    └── PAYMENT_FLOW.md               # Payment integration guide
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- JSON extension
- cURL extension (for API calls)
- Hubtel USSD account (for production)

## Quick Start

### 1. Installation

```bash
# Copy module to your project
cp -r ussd-integration /path/to/your/project/

# Import database schema
mysql -u your_user -p your_database < migrations/ussd_tables.sql
mysql -u your_user -p your_database < migrations/add_multi_tenant_ussd.sql
mysql -u your_user -p your_database < migrations/add_shared_ussd_codes.sql
```

### 2. Configuration

```php
// config/ussd_config.php
return [
    'base_code' => '711',              // Base USSD code (e.g., *711#)
    'tenant_code_length' => 3,         // Length of tenant code (e.g., 734)
    'session_timeout' => 180,          // Session timeout in seconds
    'max_menu_items' => 9,             // Max items per page
    'default_language' => 'en',        // Default language
    
    // Hubtel configuration
    'hubtel' => [
        'callback_url' => 'https://yourdomain.com/api/ussd/callback',
        'fulfillment_callback' => 'https://gs-callback.hubtel.com/callback'
    ]
];
```

### 3. Basic Usage

```php
use UssdIntegration\Controllers\UssdController;

// Initialize controller
$controller = new UssdController();

// Handle incoming USSD request
$controller->handleRequest();
```

## USSD Flow

### Basic Menu Flow
```
*711*734# (User dials)
    ↓
Welcome to SmartCast
1. Active Events
2. Vote by Code
    ↓
User selects 1
    ↓
Active Events:
1. Event Name 1
2. Event Name 2
    ↓
User selects event
    ↓
Select Contestant:
1. Contestant 1
2. Contestant 2
    ↓
User selects contestant
    ↓
Select Vote Package:
1. 1 Vote - GHS 0.50
2. 5 Votes - GHS 2.25
3. Custom votes
    ↓
Payment initiated
    ↓
Vote recorded
```

### Payment Flow (with Hubtel)
```
User selects vote package
    ↓
System creates pending transaction
    ↓
Returns AddToCart response to Hubtel
    ↓
Hubtel prompts user for payment
    ↓
User approves via USSD (*170#)
    ↓
Hubtel sends Service Fulfillment
    ↓
System verifies payment
    ↓
Records votes
    ↓
Sends callback to Hubtel
```

## Request/Response Format

### Incoming Request (from Hubtel)
```json
{
    "SessionId": "abc123",
    "ServiceCode": "711*734",
    "Mobile": "233545644749",
    "Message": "1*2*3",
    "Type": "Initiation"
}
```

### Response Types

#### Continue Session
```json
{
    "Type": "Response",
    "Message": "Select option:\n1. Option 1\n2. Option 2",
    "ClientState": "",
    "MaskNextRoute": false,
    "Label": null,
    "DataType": "display",
    "FieldType": null,
    "Item": []
}
```

#### End Session
```json
{
    "Type": "Release",
    "Message": "Thank you for voting!",
    "ClientState": "",
    "MaskNextRoute": false,
    "Label": null,
    "DataType": "display",
    "FieldType": null,
    "Item": []
}
```

#### AddToCart (Payment)
```json
{
    "SessionId": "abc123",
    "Type": "AddToCart",
    "Message": "Please wait for payment prompt",
    "Label": "Payment",
    "DataType": "display",
    "FieldType": "text",
    "Item": {
        "ItemName": "Vote for Contestant",
        "Qty": 5,
        "Price": 2.25
    }
}
```

## Multi-Tenant Support

### Shared USSD Code
Multiple tenants can share one USSD code:
- Tenant A: `*711*734#`
- Tenant B: `*711*735#`
- Tenant C: `*711*736#`

### Tenant Code Extraction
```php
// From: *711*734#
// Extracts: 734

$tenantCode = UssdHelper::extractTenantCode($serviceCode);
$tenant = $tenantModel->findByUssdCode($tenantCode);
```

## Session Management

### Session Data Structure
```php
[
    'session_id' => 'abc123',
    'phone' => '233545644749',
    'tenant_id' => 22,
    'current_step' => 'select_contestant',
    'selected_event' => 45,
    'selected_contestant' => 123,
    'selected_bundle' => 3,
    'transaction_id' => 456,
    'created_at' => '2024-01-01 12:00:00'
]
```

### Session Methods
```php
// Get session data
$data = $ussdSession->getSessionData($sessionId, 'selected_event');

// Set session data
$ussdSession->setSessionData($sessionId, 'selected_event', 45);

// Clear session
$ussdSession->clearSession($sessionId);
```

## Payment Integration

### AddToCart Response
```php
public function ussdAddToCartResponse($sessionId, $item, $message = null)
{
    return [
        'SessionId' => $sessionId,
        'Type' => 'AddToCart',
        'Message' => $message ?? 'Please wait for payment prompt',
        'Label' => 'Payment',
        'DataType' => 'display',
        'FieldType' => 'text',
        'Item' => [
            'ItemName' => $item['name'],
            'Qty' => $item['quantity'],
            'Price' => $item['price']
        ]
    ];
}
```

### Service Fulfillment Handler
```php
public function handleServiceFulfillment($input)
{
    $orderId = $input['OrderId'];
    $sessionId = $input['SessionId'];
    $orderInfo = $input['OrderInfo'];
    
    if ($orderInfo['Status'] === 'Paid' && 
        $orderInfo['Payment']['IsSuccessful']) {
        
        // Process the order
        $this->processVoteFulfillment($sessionId, $orderId);
        
        // Send callback to Hubtel
        $this->sendFulfillmentCallback($sessionId, $orderId, 'success');
    }
}
```

## Menu Design

### Menu Structure
```php
// Main menu
$menu = [
    'title' => 'Welcome to SmartCast',
    'options' => [
        ['key' => '1', 'text' => 'Active Events'],
        ['key' => '2', 'text' => 'Vote by Code'],
        ['key' => '3', 'text' => 'Help']
    ]
];

// Generate menu text
$menuText = UssdHelper::generateMenu($menu);
```

### Pagination
```php
// For long lists
$items = [...]; // Many items
$page = 1;
$perPage = 8;

$paginatedMenu = UssdHelper::paginateMenu($items, $page, $perPage);
```

## Hubtel Dashboard Setup

### 1. Create USSD Application
- Login to Hubtel dashboard
- Navigate to USSD > Applications
- Create new application

### 2. Configure Service Code
- Service Code: `*711*734#` (exact format with * and #)
- Application Type: Programmable Services
- Status: Active

### 3. Set Callback URLs
- **Service Interaction URL:** `https://yourdomain.com/api/ussd/callback`
- **Service Fulfillment URL:** `https://yourdomain.com/api/ussd/callback`
- Both can use the same URL (system detects type)

### 4. Configure Payment
- Enable payment collection
- Set merchant account
- Configure payment channels (MTN, Vodafone, AirtelTigo)

## Testing

### Local Testing
```php
// Simulate USSD request
$testData = [
    'SessionId' => 'test_' . time(),
    'ServiceCode' => '711*734',
    'Mobile' => '233545644749',
    'Message' => '',
    'Type' => 'Initiation'
];

$controller = new UssdController();
$response = $controller->handleRequest($testData);
```

### Test Scenarios
1. **Initial dial** - Empty message
2. **Menu navigation** - Message: "1", "1*2", etc.
3. **Custom input** - Message: "1*2*100" (custom votes)
4. **Payment flow** - Complete payment cycle
5. **Error handling** - Invalid inputs

## Error Handling

### Common Errors
```php
// Invalid input
if (!is_numeric($input) || $input < 1 || $input > count($options)) {
    return $this->ussdResponse('Invalid option. Please try again.');
}

// Session expired
if (!$session) {
    return $this->ussdRelease('Session expired. Please dial again.');
}

// Payment failed
if ($payment['status'] !== 'success') {
    return $this->ussdRelease('Payment failed. Please try again.');
}
```

## Production Deployment

### Checklist
- [ ] Configure Hubtel USSD application
- [ ] Set correct callback URLs
- [ ] Enable HTTPS for callbacks
- [ ] Test payment flow end-to-end
- [ ] Configure merchant account
- [ ] Set up error logging
- [ ] Test session timeout
- [ ] Verify tenant codes
- [ ] Test multi-tenant support
- [ ] Configure SMS notifications (optional)

### Monitoring
- Log all USSD requests
- Track session metrics
- Monitor payment success rate
- Alert on errors

## Advanced Features

### Custom Vote Amounts
```php
// Allow users to enter custom vote count
if ($input === 'custom') {
    return $this->ussdResponse('Enter vote count (1-10000):');
}

// Validate custom amount
if ($voteCount < 1 || $voteCount > 10000) {
    return $this->ussdResponse('Invalid amount. Enter 1-10000:');
}
```

### Shortcode Access
```php
// Direct access via shortcode
// User dials: *711*734*CODE123#
$shortcode = UssdHelper::extractShortcode($message);
if ($shortcode) {
    $contestant = $this->findByShortcode($shortcode);
    // Skip to contestant selection
}
```

## API Reference

### UssdController Methods
- `handleRequest()` - Main request handler
- `ussdResponse($message)` - Continue session
- `ussdRelease($message)` - End session
- `ussdAddToCartResponse()` - Payment prompt
- `handleServiceFulfillment()` - Process payment

### UssdSession Methods
- `createSession($data)` - Create new session
- `getSession($sessionId)` - Get session
- `setSessionData($key, $value)` - Store data
- `getSessionData($key)` - Retrieve data
- `clearSession()` - End session

### UssdHelper Methods
- `extractTenantCode($serviceCode)` - Get tenant code
- `generateMenu($menu)` - Create menu text
- `paginateMenu($items, $page, $perPage)` - Paginate
- `formatPhone($phone)` - Normalize phone

## Support

For Hubtel-specific issues:
- Documentation: https://developers.hubtel.com
- Support: support@hubtel.com

## License

Free to use in your projects.

## Changelog

### Version 1.0.0 (2024)
- Initial release
- Multi-tenant USSD support
- Hubtel Programmable Services integration
- Payment integration (AddToCart)
- Service Fulfillment handling
- Shared USSD codes support
- Custom vote amounts
- Session management
