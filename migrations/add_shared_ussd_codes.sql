-- Shared USSD Codes Implementation
-- Migration: Allow multiple tenants to share the same USSD sub code
-- Example: Multiple organizations can use *711*734#
--
-- SIMPLIFIED FLOW:
-- User dials *711*734# → Welcome message → Enter nominee shortcode → Vote
-- System automatically finds which tenant/event the nominee belongs to

-- ============================================
-- Step 1: Modify Tenants Table
-- ============================================

-- Remove UNIQUE constraint from ussd_code to allow sharing
-- Check if 'ussd_code' index exists and drop it
DROP INDEX IF EXISTS ussd_code ON tenants;

-- Check if 'idx_ussd_code' index exists and drop it
DROP INDEX IF EXISTS idx_ussd_code ON tenants;

-- Add index without UNIQUE constraint for faster lookups
CREATE INDEX idx_ussd_code ON tenants(ussd_code);

-- ============================================
-- Step 2: Sample Data (Optional - for testing)
-- ============================================

-- Example: Configure multiple tenants with same USSD code
-- UPDATE tenants SET 
--     ussd_code = '734',
--     ussd_enabled = 1,
--     ussd_welcome_message = 'Welcome! Enter nominee code to vote.'
-- WHERE id = 1;

-- UPDATE tenants SET 
--     ussd_code = '734',
--     ussd_enabled = 1,
--     ussd_welcome_message = 'Welcome! Enter nominee code to vote.'
-- WHERE id = 2;

-- ============================================
-- Verification Query
-- ============================================

-- Check tenants sharing USSD codes:
-- SELECT ussd_code, COUNT(*) as tenant_count, GROUP_CONCAT(name) as tenants
-- FROM tenants 
-- WHERE ussd_enabled = 1 AND ussd_code IS NOT NULL
-- GROUP BY ussd_code
-- HAVING tenant_count > 1;

-- ============================================
-- Rollback Script (if needed)
-- ============================================

-- To rollback these changes, run:
-- DROP INDEX idx_ussd_code ON tenants;
-- ALTER TABLE tenants ADD UNIQUE INDEX ussd_code (ussd_code);
