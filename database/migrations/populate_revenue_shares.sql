-- Migration Script: Populate Revenue Shares for Existing Transactions
-- This script will create revenue_shares records for all existing successful transactions
-- Based on the fee rules already configured in the system

-- First, let's see what we're working with
SELECT 'Current successful transactions without revenue shares:' as info;
SELECT COUNT(*) as transaction_count 
FROM transactions t 
LEFT JOIN revenue_shares rs ON t.id = rs.transaction_id 
WHERE t.status = 'success' AND rs.id IS NULL;

SELECT 'Fee rules in system:' as info;
SELECT * FROM fee_rules WHERE active = 1;

-- Create a temporary function to get applicable fee rule for a tenant
DELIMITER //
CREATE FUNCTION GetApplicableFeeRule(tenant_id INT, event_id INT) 
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE rule_id INT DEFAULT NULL;
    
    -- First try to find event-specific rule
    SELECT id INTO rule_id 
    FROM fee_rules 
    WHERE event_id = event_id AND active = 1 
    LIMIT 1;
    
    -- If no event-specific rule, try tenant-specific rule
    IF rule_id IS NULL THEN
        SELECT id INTO rule_id 
        FROM fee_rules 
        WHERE tenant_id = tenant_id AND event_id IS NULL AND active = 1 
        LIMIT 1;
    END IF;
    
    -- If no tenant-specific rule, use global rule
    IF rule_id IS NULL THEN
        SELECT id INTO rule_id 
        FROM fee_rules 
        WHERE tenant_id IS NULL AND event_id IS NULL AND active = 1 
        LIMIT 1;
    END IF;
    
    RETURN rule_id;
END//
DELIMITER ;

-- Create a temporary function to calculate fee amount
DELIMITER //
CREATE FUNCTION CalculateFeeAmount(amount DECIMAL(10,2), rule_id INT) 
RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE fee_amount DECIMAL(10,2) DEFAULT 0.00;
    DECLARE rule_type VARCHAR(20);
    DECLARE percentage_rate DECIMAL(5,2);
    DECLARE fixed_amount DECIMAL(10,2);
    
    -- Get rule details
    SELECT fr.rule_type, fr.percentage_rate, fr.fixed_amount 
    INTO rule_type, percentage_rate, fixed_amount
    FROM fee_rules fr 
    WHERE fr.id = rule_id;
    
    -- Calculate fee based on rule type
    IF rule_type = 'percentage' THEN
        SET fee_amount = amount * (percentage_rate / 100);
    ELSEIF rule_type = 'fixed' THEN
        SET fee_amount = fixed_amount;
    ELSEIF rule_type = 'blend' THEN
        SET fee_amount = (amount * (percentage_rate / 100)) + fixed_amount;
    END IF;
    
    RETURN fee_amount;
END//
DELIMITER ;

-- Now populate revenue_shares for existing successful transactions
INSERT INTO revenue_shares (transaction_id, tenant_id, amount, fee_rule_id, created_at)
SELECT 
    t.id as transaction_id,
    t.tenant_id,
    CalculateFeeAmount(t.amount, GetApplicableFeeRule(t.tenant_id, t.event_id)) as amount,
    GetApplicableFeeRule(t.tenant_id, t.event_id) as fee_rule_id,
    t.created_at
FROM transactions t
LEFT JOIN revenue_shares rs ON t.id = rs.transaction_id
WHERE t.status = 'success' 
AND rs.id IS NULL  -- Only process transactions without existing revenue shares
AND t.amount > 0;  -- Only process transactions with positive amounts

-- Update tenant_balances based on the revenue shares we just created
-- First, let's calculate what each tenant should have earned
UPDATE tenant_balances tb
SET 
    total_earned = (
        SELECT COALESCE(SUM(t.amount - rs.amount), 0)
        FROM transactions t
        INNER JOIN revenue_shares rs ON t.id = rs.transaction_id
        WHERE t.tenant_id = tb.tenant_id 
        AND t.status = 'success'
    ),
    available = (
        SELECT COALESCE(SUM(t.amount - rs.amount), 0)
        FROM transactions t
        INNER JOIN revenue_shares rs ON t.id = rs.transaction_id
        WHERE t.tenant_id = tb.tenant_id 
        AND t.status = 'success'
    ) - tb.total_paid;

-- Insert tenant_balances for tenants that don't have records yet
INSERT INTO tenant_balances (tenant_id, available, pending, total_earned, total_paid)
SELECT 
    t.tenant_id,
    COALESCE(SUM(t.amount - rs.amount), 0) as available,
    0.00 as pending,
    COALESCE(SUM(t.amount - rs.amount), 0) as total_earned,
    0.00 as total_paid
FROM transactions t
INNER JOIN revenue_shares rs ON t.id = rs.transaction_id
LEFT JOIN tenant_balances tb ON t.tenant_id = tb.tenant_id
WHERE t.status = 'success'
AND tb.id IS NULL
GROUP BY t.tenant_id;

-- Clean up temporary functions
DROP FUNCTION IF EXISTS GetApplicableFeeRule;
DROP FUNCTION IF EXISTS CalculateFeeAmount;

-- Show results
SELECT 'Revenue shares created:' as info;
SELECT COUNT(*) as revenue_shares_count FROM revenue_shares;

SELECT 'Revenue breakdown by tenant:' as info;
SELECT 
    ten.name as tenant_name,
    COUNT(rs.id) as transactions_count,
    SUM(t.amount) as gross_revenue,
    SUM(rs.amount) as platform_fees,
    SUM(t.amount - rs.amount) as tenant_earnings,
    ROUND(AVG((rs.amount / t.amount) * 100), 2) as avg_fee_percentage
FROM revenue_shares rs
INNER JOIN transactions t ON rs.transaction_id = t.id
INNER JOIN tenants ten ON rs.tenant_id = ten.id
WHERE t.status = 'success'
GROUP BY rs.tenant_id, ten.name
ORDER BY tenant_earnings DESC;

SELECT 'Updated tenant balances:' as info;
SELECT 
    t.name as tenant_name,
    tb.available,
    tb.total_earned,
    tb.total_paid
FROM tenant_balances tb
INNER JOIN tenants t ON tb.tenant_id = t.id
WHERE t.active = 1;

-- Verify data integrity
SELECT 'Data integrity check:' as info;
SELECT 
    'Transactions without revenue shares' as check_type,
    COUNT(*) as count
FROM transactions t
LEFT JOIN revenue_shares rs ON t.id = rs.transaction_id
WHERE t.status = 'success' AND rs.id IS NULL

UNION ALL

SELECT 
    'Revenue shares without transactions' as check_type,
    COUNT(*) as count
FROM revenue_shares rs
LEFT JOIN transactions t ON rs.transaction_id = t.id
WHERE t.id IS NULL;

-- Show specific transaction details for verification
SELECT 'Sample revenue distribution details:' as info;
SELECT 
    t.id as transaction_id,
    t.amount as gross_amount,
    rs.amount as platform_fee,
    (t.amount - rs.amount) as tenant_earnings,
    ROUND((rs.amount / t.amount) * 100, 2) as fee_percentage,
    fr.rule_type,
    fr.percentage_rate,
    ten.name as tenant_name
FROM transactions t
INNER JOIN revenue_shares rs ON t.id = rs.transaction_id
INNER JOIN fee_rules fr ON rs.fee_rule_id = fr.id
INNER JOIN tenants ten ON t.tenant_id = ten.id
WHERE t.status = 'success'
ORDER BY t.created_at DESC
LIMIT 10;

-- Migration completed successfully!
SELECT 'Migration completed! Revenue shares have been populated for all existing successful transactions.' as result;
