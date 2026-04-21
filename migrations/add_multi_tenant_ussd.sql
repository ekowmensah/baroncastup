-- Multi-Tenant USSD Implementation
-- Migration: Add USSD configuration to tenants and sessions

-- ============================================
-- Step 1: Update Tenants Table
-- ============================================

-- Add USSD configuration columns
ALTER TABLE tenants 
ADD COLUMN ussd_code VARCHAR(10) UNIQUE COMMENT 'USSD suffix code (e.g., 01, 02, 03)',
ADD COLUMN ussd_enabled TINYINT(1) DEFAULT 0 COMMENT 'Enable USSD voting for tenant',
ADD COLUMN ussd_welcome_message TEXT COMMENT 'Custom USSD welcome message';

-- Add index for faster lookups
CREATE INDEX idx_ussd_code ON tenants(ussd_code);
CREATE INDEX idx_ussd_enabled ON tenants(ussd_enabled);

-- ============================================
-- Step 2: Update USSD Sessions Table
-- ============================================

-- Add tenant context columns
ALTER TABLE ussd_sessions 
ADD COLUMN tenant_id INT(11) COMMENT 'Tenant ID for this session',
ADD COLUMN service_code VARCHAR(20) COMMENT 'Full USSD code dialed (e.g., *920*01#)';

-- Add foreign key constraint
ALTER TABLE ussd_sessions 
ADD CONSTRAINT fk_ussd_tenant 
FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Add indexes
CREATE INDEX idx_ussd_tenant ON ussd_sessions(tenant_id);
CREATE INDEX idx_service_code ON ussd_sessions(service_code);

-- ============================================
-- Step 3: Sample Data (Optional - for testing)
-- ============================================

-- Example: Configure first tenant with USSD code 01
-- UPDATE tenants SET 
--     ussd_code = '01',
--     ussd_enabled = 1,
--     ussd_welcome_message = 'Welcome to SmartCast Voting!'
-- WHERE id = 1;

-- ============================================
-- Rollback Script (if needed)
-- ============================================

-- To rollback these changes, run:
-- ALTER TABLE ussd_sessions DROP FOREIGN KEY fk_ussd_tenant;
-- ALTER TABLE ussd_sessions DROP COLUMN tenant_id;
-- ALTER TABLE ussd_sessions DROP COLUMN service_code;
-- DROP INDEX idx_ussd_tenant ON ussd_sessions;
-- DROP INDEX idx_service_code ON ussd_sessions;
-- 
-- ALTER TABLE tenants DROP COLUMN ussd_code;
-- ALTER TABLE tenants DROP COLUMN ussd_enabled;
-- ALTER TABLE tenants DROP COLUMN ussd_welcome_message;
-- DROP INDEX idx_ussd_code ON tenants;
-- DROP INDEX idx_ussd_enabled ON tenants;
