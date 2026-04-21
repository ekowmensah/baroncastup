-- Add missing fields to fee_rules table
-- This migration adds name, description, min_amount, and max_amount fields

-- Add name field for rule identification
ALTER TABLE `fee_rules` 
ADD COLUMN `name` varchar(100) DEFAULT NULL COMMENT 'Human-readable name for the fee rule' 
AFTER `id`;

-- Add description field for rule details
ALTER TABLE `fee_rules` 
ADD COLUMN `description` text DEFAULT NULL COMMENT 'Detailed description of when this rule applies' 
AFTER `name`;

-- Add min_amount field for minimum fee limits
ALTER TABLE `fee_rules` 
ADD COLUMN `min_amount` decimal(10,2) DEFAULT NULL COMMENT 'Minimum fee amount (overrides calculated fee if lower)' 
AFTER `fixed_amount`;

-- Add max_amount field for maximum fee limits
ALTER TABLE `fee_rules` 
ADD COLUMN `max_amount` decimal(10,2) DEFAULT NULL COMMENT 'Maximum fee amount (caps calculated fee if higher)' 
AFTER `min_amount`;

-- Update existing rules with default names based on their configuration
UPDATE `fee_rules` SET 
    `name` = CASE 
        WHEN `tenant_id` IS NULL AND `event_id` IS NULL THEN 
            CONCAT('Global ', UPPER(`rule_type`), ' Rule - ', 
                CASE 
                    WHEN `rule_type` = 'percentage' THEN CONCAT(`percentage_rate`, '%')
                    WHEN `rule_type` = 'fixed' THEN CONCAT('$', `fixed_amount`)
                    ELSE 'Mixed'
                END
            )
        WHEN `tenant_id` IS NOT NULL AND `event_id` IS NULL THEN 
            CONCAT('Tenant ', `tenant_id`, ' ', UPPER(`rule_type`), ' Rule - ',
                CASE 
                    WHEN `rule_type` = 'percentage' THEN CONCAT(`percentage_rate`, '%')
                    WHEN `rule_type` = 'fixed' THEN CONCAT('$', `fixed_amount`)
                    ELSE 'Mixed'
                END
            )
        ELSE 
            CONCAT('Event ', `event_id`, ' ', UPPER(`rule_type`), ' Rule - ',
                CASE 
                    WHEN `rule_type` = 'percentage' THEN CONCAT(`percentage_rate`, '%')
                    WHEN `rule_type` = 'fixed' THEN CONCAT('$', `fixed_amount`)
                    ELSE 'Mixed'
                END
            )
    END,
    `description` = CASE 
        WHEN `tenant_id` IS NULL AND `event_id` IS NULL THEN 
            'Global fee rule that applies to all tenants unless they have specific rules'
        WHEN `tenant_id` IS NOT NULL AND `event_id` IS NULL THEN 
            CONCAT('Tenant-specific fee rule for tenant ID ', `tenant_id`)
        ELSE 
            CONCAT('Event-specific fee rule for event ID ', `event_id`)
    END
WHERE `name` IS NULL;

-- Show updated table structure
DESCRIBE `fee_rules`;

-- Show updated rules
SELECT 
    id,
    name,
    CASE 
        WHEN tenant_id IS NULL AND event_id IS NULL THEN 'Global'
        WHEN tenant_id IS NOT NULL AND event_id IS NULL THEN CONCAT('Tenant ', tenant_id)
        ELSE CONCAT('Event ', event_id)
    END as scope,
    rule_type,
    CASE 
        WHEN rule_type = 'percentage' THEN CONCAT(percentage_rate, '%')
        WHEN rule_type = 'fixed' THEN CONCAT('$', fixed_amount)
        ELSE 'Mixed'
    END as rate,
    active,
    created_at
FROM `fee_rules`
ORDER BY 
    CASE WHEN tenant_id IS NULL AND event_id IS NULL THEN 1 ELSE 0 END DESC,
    tenant_id ASC,
    event_id ASC;
