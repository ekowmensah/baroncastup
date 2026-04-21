# SmartCast Payout System Documentation

## Overview

The SmartCast Payout System is a comprehensive, production-ready solution for managing organizer payouts, revenue distribution, and financial operations. This system replaces the basic payout functionality with enterprise-grade features.

## 🚀 Quick Start

### 1. Run Migration
```bash
php migrations/run_payout_system_migration.php
```

### 2. Access Payout System
Navigate to: `/organizer/payouts`

## 📁 System Architecture

### Database Tables

#### Core Tables
- **`payout_methods`** - Organizer payout method configurations
- **`payout_schedules`** - Automatic payout scheduling settings
- **`payouts`** - Enhanced payout transaction records
- **`revenue_transactions`** - Revenue distribution tracking
- **`platform_revenue`** - Platform earnings and fees
- **`tenant_balances`** - Enhanced balance management

#### Enhanced Tables
- **`payouts`** - Added processing fees, net amounts, approval workflow
- **`tenant_balances`** - Added on-hold amounts, payout history
- **`revenue_shares`** - Enhanced with revenue types and descriptions

### Models

#### New Models
- **`PayoutMethod`** - Manage payout methods with validation
- **`PayoutSchedule`** - Handle automatic scheduling
- **`RevenueTransaction`** - Process revenue distribution

#### Enhanced Models
- **`Payout`** - Complete payout processing
- **`TenantBalance`** - Advanced balance operations

### Controllers
- **`PayoutController`** - Complete payout management interface

### Services
- **`PayoutService`** - Core business logic and processing

## 💰 Revenue Distribution

### Automatic Processing
Every successful payment triggers automatic revenue distribution:

```php
// Integrated into VoteController::processSuccessfulPayment()
$payoutService->processTransactionRevenue(
    $transactionId, $tenantId, $eventId, $grossAmount
);
```

### Fee Structure
- **Platform Fee**: 5% (configurable)
- **Processing Fees**:
  - Bank Transfer: 1.0% + GH₵0.50
  - Mobile Money: 1.5% + GH₵0.25
  - PayPal: 2.9% + GH₵0.30
  - Stripe: 2.9% + GH₵0.30

### Real-Time Updates
- Tenant balances updated immediately
- Platform revenue tracked automatically
- Fee calculations applied instantly

## 🎛️ Payout Methods

### Supported Methods

#### 1. Bank Transfer
```php
$accountDetails = [
    'account_number' => '1234567890',
    'bank_name' => 'Example Bank',
    'account_name' => 'John Doe',
    'bank_code' => 'EXBK',
    'routing_number' => '123456789'
];
```

#### 2. Mobile Money
```php
$accountDetails = [
    'phone_number' => '+1234567890',
    'provider' => 'MTN',
    'account_name' => 'John Doe'
];
```

#### 3. PayPal
```php
$accountDetails = [
    'email' => 'john@example.com'
];
```

#### 4. Stripe
```php
$accountDetails = [
    'account_id' => 'acct_1234567890'
];
```

### Method Management
- Add/edit/deactivate methods
- Set default method
- Verification system
- Security validation

## ⚙️ Automatic Payouts

### Schedule Types
- **Manual** - User-requested only
- **Daily** - Every day
- **Weekly** - Specific day of week
- **Monthly** - Specific day of month

### Configuration
```php
$schedule = [
    'frequency' => 'monthly',
    'minimum_amount' => 10.00,
    'auto_payout_enabled' => true,
    'instant_payout_threshold' => 1000.00,
    'payout_day' => 1 // 1st of month
];
```

### Processing
- Automatic eligibility checking
- Minimum threshold validation
- Verified method requirement
- Smart scheduling

## 📊 Dashboard Features

### Balance Overview
- Available balance
- Pending amounts
- Total earned
- Total paid out

### Quick Actions
- Request payout
- Manage methods
- Configure settings
- View history

### Analytics
- Revenue statistics
- Transaction metrics
- Performance tracking
- Fee analysis

## 🔒 Security Features

### Input Validation
- Method-specific validation
- Amount limits
- Balance verification
- Account detail validation

### Error Handling
- Graceful failure recovery
- Balance restoration on failure
- Comprehensive logging
- Retry mechanisms

### Audit Trail
- Complete transaction history
- Status tracking
- Error logging
- Performance monitoring

## 🌐 API Endpoints

### Balance API
```
GET /organizer/api/payouts/balance
```

### Fee Calculator
```
POST /organizer/api/payouts/calculate-fees
Body: { amount: 100.00, method_type: 'bank_transfer' }
```

## 📱 User Interface

### Dashboard (`/organizer/payouts`)
- Comprehensive overview
- Balance management
- Quick actions
- Recent activity

### Request Payout (`/organizer/payouts/request`)
- Amount selection
- Method selection
- Fee calculation
- Terms acceptance

### Method Management (`/organizer/payouts/methods`)
- Add/edit methods
- Set defaults
- Verification status
- Security settings

### Settings (`/organizer/payouts/settings`)
- Schedule configuration
- Minimum amounts
- Auto-payout settings
- Notification preferences

### History (`/organizer/payouts/history`)
- Complete payout history
- Status tracking
- Action buttons
- Export functionality

## 🔧 Configuration

### Environment Variables
```php
// Fee configuration
PLATFORM_FEE_PERCENTAGE=5.0
PROCESSING_FEE_BANK=1.0
PROCESSING_FEE_MOBILE=1.5
PROCESSING_FEE_PAYPAL=2.9
PROCESSING_FEE_STRIPE=2.9

// Minimum amounts
MIN_PAYOUT_AMOUNT=10.00
INSTANT_PAYOUT_THRESHOLD=1000.00
```

### Database Configuration
All tables use InnoDB engine with proper foreign key constraints and indexing for optimal performance.

## 🚀 Deployment

### Migration Steps
1. Run database migration
2. Update application routes
3. Test payout functionality
4. Configure payment providers
5. Set up monitoring

### Monitoring
- Transaction success rates
- Processing times
- Error rates
- Balance accuracy

## 🔄 Legacy System Migration

The old payout system (`/organizer/financial/payouts`) has been replaced with a redirect to the new comprehensive system. All existing data is preserved and compatible.

### Migration Benefits
- ✅ Enhanced functionality
- ✅ Better user experience
- ✅ Improved security
- ✅ Scalable architecture
- ✅ Production-ready features

## 📞 Support

For technical support or questions about the payout system:
- Check error logs in `/logs/`
- Review database transaction logs
- Monitor API response times
- Verify payment provider configurations

## 🎯 Future Enhancements

### Planned Features
- Multi-currency support
- Advanced reporting
- Webhook notifications
- Mobile app integration
- Third-party integrations

### Performance Optimizations
- Caching layer
- Background processing
- Batch operations
- Database optimization

---

**The SmartCast Payout System is now fully operational and ready for production use!** 🎉
