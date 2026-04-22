-- Simple Revenue Shares Migration for SmartCast
-- This script populates revenue_shares for existing successful transactions
-- Based on the actual data in smartcast.sql

-- Step 1: Show current state
SELECT 'BEFORE MIGRATION - Current successful transactions:' as status;
SELECT 
    t.id,
    t.tenant_id,
    t.amount,
    t.created_at,
    'No revenue share' as current_status
FROM transactions t
WHERE t.status = 'success'
ORDER BY t.id;

-- Step 2: Create revenue shares for existing successful transactions
-- Transaction ID 37: Tenant 3, Amount 1.00 (should use global 12% rule)
INSERT INTO revenue_shares (transaction_id, tenant_id, amount, fee_rule_id, created_at)
VALUES (37, 3, 0.12, 3, '2025-10-09 12:06:08');

-- Transaction ID 38: Tenant 2, Amount 35.00 (should use tenant 2's 15% rule)  
INSERT INTO revenue_shares (transaction_id, tenant_id, amount, fee_rule_id, created_at)
VALUES (38, 2, 5.25, 2, '2025-10-09 12:09:44');

-- Transaction ID 39: Tenant 2, Amount 10.00 (should use tenant 2's 15% rule)
INSERT INTO revenue_shares (transaction_id, tenant_id, amount, fee_rule_id, created_at)
VALUES (39, 2, 1.50, 2, '2025-10-09 12:11:26');

-- Step 3: Update tenant balances based on the revenue shares
-- Update tenant 2's balance (transactions 38 + 39)
UPDATE tenant_balances 
SET 
    available = (35.00 - 5.25) + (10.00 - 1.50), -- 28.75 + 8.50 = 37.25
    total_earned = (35.00 - 5.25) + (10.00 - 1.50), -- 37.25
    updated_at = NOW()
WHERE tenant_id = 2;

-- Update tenant 3's balance (transaction 37) - need to create record first
INSERT INTO tenant_balances (tenant_id, available, pending, total_earned, total_paid, created_at, updated_at)
VALUES (3, 0.88, 0.00, 0.88, 0.00, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    available = 0.88,
    total_earned = 0.88,
    updated_at = NOW();

-- Step 4: Show results after migration
SELECT 'AFTER MIGRATION - Revenue shares created:' as status;
SELECT 
    rs.id,
    rs.transaction_id,
    rs.tenant_id,
    rs.amount as platform_fee,
    t.amount as total_amount,
    (t.amount - rs.amount) as tenant_earnings,
    ROUND((rs.amount / t.amount) * 100, 2) as fee_percentage,
    fr.percentage_rate as rule_percentage
FROM revenue_shares rs
INNER JOIN transactions t ON rs.transaction_id = t.id
INNER JOIN fee_rules fr ON rs.fee_rule_id = fr.id
ORDER BY rs.transaction_id;

SELECT 'Updated tenant balances:' as status;
SELECT 
    tb.tenant_id,
    t.name as tenant_name,
    tb.available,
    tb.total_earned,
    tb.total_paid
FROM tenant_balances tb
INNER JOIN tenants t ON tb.tenant_id = t.id
WHERE tb.tenant_id IN (2, 3);

-- Step 5: Verification queries
SELECT 'Revenue breakdown by tenant:' as status;
SELECT 
    rs.tenant_id,
    ten.name as tenant_name,
    COUNT(rs.id) as transaction_count,
    SUM(t.amount) as gross_revenue,
    SUM(rs.amount) as platform_fees,
    SUM(t.amount - rs.amount) as tenant_earnings
FROM revenue_shares rs
INNER JOIN transactions t ON rs.transaction_id = t.id
INNER JOIN tenants ten ON rs.tenant_id = ten.id
GROUP BY rs.tenant_id, ten.name;

-- Step 6: Show total platform revenue
SELECT 'Total platform revenue:' as status;
SELECT 
    SUM(rs.amount) as total_platform_fees,
    COUNT(rs.id) as total_transactions
FROM revenue_shares rs
INNER JOIN transactions t ON rs.transaction_id = t.id
WHERE t.status = 'success';

SELECT 'Migration completed successfully!' as result;
