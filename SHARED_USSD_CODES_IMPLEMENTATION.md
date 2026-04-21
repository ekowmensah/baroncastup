# Shared USSD Codes Implementation

## Overview
Multiple tenants can now share the same USSD sub code. Users dial the shared code and directly enter a nominee shortcode to vote - the system automatically determines which tenant/event the nominee belongs to.

## Simplified User Flow

```
User dials: *711*734#
↓
System: "Welcome to SmartCast Voting!
         Enter nominee code to vote:"
↓
User enters: ABC123
↓
System: Finds nominee across ALL tenants
↓
System: Shows vote bundles and processes payment
↓
Vote recorded for correct tenant/event
```

## Key Features

### 1. **Shared USSD Codes**
- Multiple tenants can use the same USSD sub code (e.g., *711*734#)
- No tenant selection menu needed
- Automatic tenant detection based on nominee shortcode

### 2. **Direct Nominee Entry**
- Users immediately enter nominee shortcode after welcome message
- System searches across all tenants to find the nominee
- Tenant is automatically determined from the nominee's event

### 3. **Cross-Tenant Nominee Lookup**
- Nominee shortcodes are unique across the entire platform
- System finds nominee regardless of which tenant owns the USSD code
- Proper tenant tracking for transactions and revenue

## Implementation Details

### Database Changes

**Migration File:** `migrations/add_shared_ussd_codes.sql`

```sql
-- Remove UNIQUE constraint from ussd_code
ALTER TABLE tenants DROP INDEX ussd_code;

-- Add non-unique index for faster lookups
CREATE INDEX idx_ussd_code ON tenants(ussd_code);
```

### Code Changes

#### 1. UssdController.php
- **handleNewSession()**: Simplified to go directly to shortcode entry state
- **getTenantFromServiceCode()**: Returns any enabled tenant with the USSD code
- Removed complex menu navigation

#### 2. UssdSession.php
- **handleEnterShortcodeState()**: Searches for nominees across ALL tenants
- Automatically sets tenant_id from the nominee's event
- No tenant filtering on shortcode lookup

### Configuration

**Example: Assign same USSD code to multiple tenants**

```sql
-- Tenant 1 uses code 734
UPDATE tenants SET 
    ussd_code = '734',
    ussd_enabled = 1,
    ussd_welcome_message = 'Welcome! Enter nominee code to vote.'
WHERE id = 1;

-- Tenant 2 also uses code 734
UPDATE tenants SET 
    ussd_code = '734',
    ussd_enabled = 1,
    ussd_welcome_message = 'Welcome! Enter nominee code to vote.'
WHERE id = 2;

-- Tenant 3 also uses code 734
UPDATE tenants SET 
    ussd_code = '734',
    ussd_enabled = 1,
    ussd_welcome_message = 'Welcome! Enter nominee code to vote.'
WHERE id = 3;
```

## Usage Examples

### Example 1: Three Organizations Share *711*734#

**Setup:**
- SmartCast Events (Tenant ID: 1) - ussd_code = '734'
- EventPro Ghana (Tenant ID: 2) - ussd_code = '734'
- VoteHub Africa (Tenant ID: 3) - ussd_code = '734'

**User Experience:**
1. User dials `*711*734#`
2. Sees: "Welcome! Enter nominee code to vote:"
3. Enters nominee code: `SMART001`
4. System finds nominee belongs to SmartCast Events
5. Transaction recorded with tenant_id = 1
6. Revenue attributed to SmartCast Events

### Example 2: Single Organization Uses Code

**Setup:**
- Only SmartCast (Tenant ID: 22) - ussd_code = '734'

**User Experience:**
1. User dials `*711*734#`
2. Sees: "Welcome to SmartCast Voting! Enter nominee code to vote:"
3. Enters nominee code: `ABC123`
4. Votes processed normally

## Benefits

### 1. **Cost Savings**
- No need to purchase multiple USSD sub codes from Hubtel
- One code can serve unlimited tenants

### 2. **Simplified User Experience**
- No confusing tenant selection menus
- Direct path: Dial → Enter code → Vote
- Faster voting process

### 3. **Flexible Deployment**
- Easy to add new tenants to existing USSD code
- No code changes needed to add tenants
- Just update database configuration

### 4. **Proper Revenue Tracking**
- Each vote correctly attributed to the right tenant
- Transaction records include proper tenant_id
- Revenue sharing works correctly

## Technical Flow

### Session Creation
```php
// User dials *711*734#
// System creates session with STATE_ENTER_SHORTCODE
$this->ussdSession->createSession(
    $sessionId,
    $phoneNumber,
    UssdSession::STATE_ENTER_SHORTCODE,
    ['ussd_code' => '734']
);
```

### Nominee Lookup
```php
// User enters nominee code
// System searches across ALL tenants
$result = $contestantCategoryModel->findByShortCode($shortCode);

// Get event to determine tenant
$event = $eventModel->find($result['event_id']);

// Store tenant_id for transaction
$this->updateSessionColumns($sessionId, [
    'tenant_id' => $event['tenant_id']
]);
```

### Transaction Recording
```php
// Vote transaction includes correct tenant_id
$transactionData = [
    'tenant_id' => $event['tenant_id'],  // From nominee's event
    'event_id' => $event['id'],
    'contestant_id' => $contestant['id'],
    // ... other fields
];
```

## Deployment Steps

### 1. Run Database Migration
```bash
mysql -u username -p database_name < migrations/add_shared_ussd_codes.sql
```

### 2. Configure Tenants
```sql
-- Assign USSD code to tenants
UPDATE tenants SET 
    ussd_code = '734',
    ussd_enabled = 1,
    ussd_welcome_message = 'Welcome! Enter nominee code to vote.'
WHERE id IN (1, 2, 3);
```

### 3. Verify Configuration
```sql
-- Check which tenants share codes
SELECT ussd_code, COUNT(*) as tenant_count, GROUP_CONCAT(name) as tenants
FROM tenants 
WHERE ussd_enabled = 1 AND ussd_code IS NOT NULL
GROUP BY ussd_code;
```

### 4. Test the Flow
1. Dial the USSD code: `*711*734#`
2. Enter a nominee shortcode from any tenant
3. Verify vote is recorded with correct tenant_id
4. Check transaction table for proper tenant attribution

## Monitoring

### Check Shared Codes
```sql
SELECT ussd_code, COUNT(*) as tenant_count, GROUP_CONCAT(name) as tenants
FROM tenants 
WHERE ussd_enabled = 1 AND ussd_code IS NOT NULL
GROUP BY ussd_code
HAVING tenant_count > 1;
```

### Verify Transactions
```sql
-- Check recent USSD transactions
SELECT t.id, t.tenant_id, ten.name as tenant_name, 
       e.title as event, c.name as contestant, t.amount
FROM transactions t
JOIN tenants ten ON t.tenant_id = ten.id
JOIN events e ON t.event_id = e.id
JOIN contestants c ON t.contestant_id = c.id
WHERE t.provider = 'hubtel_ussd'
ORDER BY t.created_at DESC
LIMIT 20;
```

## Troubleshooting

### Issue: "Nominee code not found"
**Cause:** Nominee shortcode doesn't exist or is inactive
**Solution:** Verify nominee has a shortcode assigned and is active

### Issue: "Event is not active for voting"
**Cause:** Nominee's event status is not 'active'
**Solution:** Activate the event in the organizer dashboard

### Issue: Wrong tenant gets the revenue
**Cause:** tenant_id not properly set in transaction
**Solution:** Check UssdSession.php line 340-342 for tenant_id assignment

## Files Modified

1. **migrations/add_shared_ussd_codes.sql** - Database migration
2. **src/Controllers/UssdController.php** - Simplified session handling
3. **src/Models/UssdSession.php** - Cross-tenant nominee lookup

## Backward Compatibility

✅ **Fully backward compatible**
- Existing single-tenant USSD codes continue to work
- No changes needed to existing configurations
- Only affects new shared code assignments

## Future Enhancements

### Possible Additions:
1. **Custom welcome messages per tenant** - Show different messages based on tenant
2. **Usage analytics** - Track which tenant's nominees get more votes via shared code
3. **Load balancing** - Distribute USSD load across multiple codes
4. **Tenant branding** - Show tenant name when nominee is found

## Support

For issues or questions:
- Check error logs: `/var/log/apache2/error.log` or XAMPP logs
- Review USSD session data in `ussd_sessions` table
- Verify Hubtel dashboard configuration matches code setup
