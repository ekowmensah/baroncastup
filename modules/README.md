# SmartCast Standalone Modules

This directory contains reusable modules extracted from the SmartCast platform that can be used in future projects.

## Available Modules

### 1. Payment Gateway Module
Complete payment integration for Hubtel and Paystack mobile money payments.

**Location:** `payment-gateway/`

**Features:**
- Hubtel Direct Receive Money API
- Paystack Mobile Money & Card Payments
- OTP verification system
- Webhook processing
- Transaction management
- Multi-gateway support with failover

**Use Cases:**
- E-commerce platforms
- Voting/polling systems
- Subscription services
- Donation platforms
- Any application requiring mobile money payments

---

### 2. USSD Integration Module
Complete USSD menu system with multi-tenant support and payment integration.

**Location:** `ussd-integration/`

**Features:**
- Multi-tenant USSD code management
- Dynamic menu generation
- Session management
- Payment integration (Hubtel USSD)
- Service fulfillment handling
- Shared USSD codes support

**Use Cases:**
- USSD-based voting systems
- Mobile banking applications
- Service delivery platforms
- Information systems
- Survey/polling platforms

---

## Quick Start

Each module contains:
- `README.md` - Detailed documentation
- `INSTALLATION.md` - Installation guide
- `INTEGRATION_GUIDE.md` - Integration instructions
- `src/` - Source code
- `config/` - Configuration templates
- `migrations/` - Database schemas
- `examples/` - Usage examples

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (optional, for dependency management)
- cURL extension enabled

## License

These modules are extracted from SmartCast and can be freely used in your projects.

## Support

For questions or issues, refer to the individual module documentation.
