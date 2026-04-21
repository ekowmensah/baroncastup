# SMS Gateway Debugging Guide

## Quick Test

1. **Run the test script:**
   ```bash
   php test_sms.php
   ```

2. **Check your gateway configuration:**
   - Go to `/superadmin/sms/gateways`
   - Verify your API credentials
   - Test the gateway using the "Test" button

## Common Issues & Solutions

### mNotify Issues

#### Issue: "Invalid API Key" or 401 Unauthorized
**Solution:**
- Verify your API key at https://apps.mnotify.net/api/api
- Ensure the API key is active and has SMS credits
- Check that the API key is correctly entered (no extra spaces)

#### Issue: "Invalid Sender ID"
**Solution:**
- Sender ID must be 11 characters or less
- Use only alphanumeric characters
- Register your Sender ID with mNotify if required

#### Issue: "Insufficient Balance"
**Solution:**
- Check your mNotify account balance
- Top up your account if needed

### Hubtel Issues

#### Issue: "Authentication Failed"
**Solution:**
- Verify your Client ID and Client Secret
- Get credentials from: Hubtel Dashboard > Messaging > Programmable SMS
- Ensure credentials are for the correct environment (sandbox vs live)

#### Issue: "Invalid Sender ID"
**Solution:**
- Register your Sender ID with Hubtel
- Use approved Sender IDs only
- Default sender IDs: Your phone number or approved shortcode

### General Issues

#### Issue: "Phone number format error"
**Solution:**
- Use international format: 233XXXXXXXXX (for Ghana)
- Remove spaces, dashes, or other characters
- Ensure 12 digits total for Ghana numbers

#### Issue: "Gateway timeout"
**Solution:**
- Check internet connection
- Verify gateway URLs are accessible
- Increase timeout in SmsService if needed

## API Endpoints

### mNotify
- **URL:** `https://api.mnotify.com/api/sms/quick?key=YOUR_API_KEY`
- **Method:** POST
- **Headers:** `Content-Type: application/json`
- **Body:**
  ```json
  {
    "recipient": ["233200000000"],
    "sender": "YourSender",
    "message": "Your message",
    "is_schedule": false,
    "schedule_date": ""
  }
  ```

### Hubtel
- **URL:** `https://sms.hubtel.com/v1/messages/send`
- **Method:** POST
- **Headers:** 
  - `Content-Type: application/json`
  - `Authorization: Basic base64(clientId:clientSecret)`
- **Body:**
  ```json
  {
    "From": "YourSender",
    "To": "233200000000",
    "Content": "Your message",
    "ClientReference": "unique_ref",
    "RegisteredDelivery": true
  }
  ```

## Testing Steps

1. **Test API credentials manually:**
   ```bash
   # mNotify test
   curl -X POST "https://api.mnotify.com/api/sms/quick?key=YOUR_API_KEY" \
   -H "Content-Type: application/json" \
   -d '{"recipient":["233200000000"],"sender":"Test","message":"Test message","is_schedule":false,"schedule_date":""}'
   
   # Hubtel test
   curl -X POST "https://sms.hubtel.com/v1/messages/send" \
   -H "Content-Type: application/json" \
   -H "Authorization: Basic $(echo -n 'clientId:clientSecret' | base64)" \
   -d '{"From":"Test","To":"233200000000","Content":"Test message","ClientReference":"test123","RegisteredDelivery":true}'
   ```

2. **Check PHP error logs:**
   ```bash
   tail -f /xampp/logs/php_error_log
   ```

3. **Enable SMS debugging:**
   - Check `error_log()` calls in `SmsService.php`
   - Look for "SMS API Response" entries

## Success Response Examples

### mNotify Success Response
```json
{
  "status": "success",
  "message": "SMS sent successfully",
  "data": {
    "id": "12345",
    "recipient": "233200000000"
  }
}
```

### Hubtel Success Response
```json
{
  "Status": 0,
  "Message": "Message sent successfully",
  "MessageId": "abc123",
  "Rate": 1,
  "ClientReference": "test123"
}
```

## Troubleshooting Checklist

- [ ] API credentials are correct
- [ ] Gateway is marked as active
- [ ] Phone number is in correct format
- [ ] Sender ID is registered/approved
- [ ] Account has sufficient balance
- [ ] Internet connection is working
- [ ] No firewall blocking outbound requests
- [ ] PHP cURL extension is enabled
- [ ] SSL certificates are valid (or SSL verification disabled for testing)

## Getting Help

1. **Check gateway provider documentation:**
   - mNotify: https://readthedocs.mnotify.com/
   - Hubtel: Contact Hubtel support

2. **Contact provider support:**
   - mNotify: support@mnotify.com
   - Hubtel: developers@hubtel.com

3. **Check account status:**
   - Login to your provider dashboard
   - Verify account is active and funded
