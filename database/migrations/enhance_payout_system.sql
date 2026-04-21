-- Enhanced Payout System Migration
-- Run this script to add missing tables and columns for complete payout functionality

-- 1. Create payout_methods table
CREATE TABLE `payout_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `method_type` enum('bank_transfer','mobile_money','paypal','stripe') NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `account_details` JSON NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_data` JSON DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  INDEX `idx_tenant_default` (`tenant_id`, `is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Create payout_schedules table
CREATE TABLE `payout_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `frequency` enum('manual','daily','weekly','monthly') DEFAULT 'monthly',
  `minimum_amount` decimal(10,2) DEFAULT 10.00,
  `auto_payout_enabled` tinyint(1) DEFAULT 0,
  `instant_payout_threshold` decimal(10,2) DEFAULT 1000.00,
  `next_payout_date` date DEFAULT NULL,
  `payout_day` int(2) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_schedule` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Create platform_revenue table
CREATE TABLE `platform_revenue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) DEFAULT NULL,
  `revenue_type` enum('platform_fee','processing_fee','subscription_fee','other') DEFAULT 'platform_fee',
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `metadata` JSON DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE SET NULL,
  INDEX `idx_type_date` (`revenue_type`, `created_at`),
  INDEX `idx_amount_date` (`amount`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Create revenue_transactions table
CREATE TABLE `revenue_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `gross_amount` decimal(10,2) NOT NULL,
  `platform_fee` decimal(10,2) NOT NULL,
  `processing_fee` decimal(10,2) DEFAULT 0.00,
  `referrer_commission` decimal(10,2) DEFAULT 0.00,
  `net_tenant_amount` decimal(10,2) NOT NULL,
  `fee_rule_snapshot` JSON DEFAULT NULL,
  `distribution_status` enum('pending','completed','failed') DEFAULT 'completed',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  INDEX `idx_tenant_date` (`tenant_id`, `created_at`),
  INDEX `idx_event_date` (`event_id`, `created_at`),
  INDEX `idx_distribution_status` (`distribution_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Enhance tenant_balances table
ALTER TABLE `tenant_balances` 
ADD COLUMN `on_hold` decimal(10,2) DEFAULT 0.00 AFTER `pending`,
ADD COLUMN `last_payout_at` timestamp NULL AFTER `total_paid`,
ADD COLUMN `last_payout_amount` decimal(10,2) DEFAULT 0.00 AFTER `last_payout_at`,
ADD COLUMN `payout_count` int(11) DEFAULT 0 AFTER `last_payout_amount`;

-- 6. Enhance payouts table
ALTER TABLE `payouts`
ADD COLUMN `payout_method_id` int(11) DEFAULT NULL AFTER `payout_method`,
ADD COLUMN `processing_fee` decimal(10,2) DEFAULT 0.00 AFTER `amount`,
ADD COLUMN `net_amount` decimal(10,2) DEFAULT 0.00 AFTER `processing_fee`,
ADD COLUMN `initiated_by` int(11) DEFAULT NULL AFTER `tenant_id`,
ADD COLUMN `approved_by` int(11) DEFAULT NULL AFTER `initiated_by`,
ADD COLUMN `approved_at` timestamp NULL AFTER `approved_by`,
ADD COLUMN `payout_type` enum('manual','automatic','instant') DEFAULT 'manual' AFTER `payout_method`;

-- Add foreign keys for payouts table
ALTER TABLE `payouts`
ADD FOREIGN KEY (`payout_method_id`) REFERENCES `payout_methods`(`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`initiated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- 7. Enhance revenue_shares table
ALTER TABLE `revenue_shares`
ADD COLUMN `revenue_type` enum('platform_fee','tenant_share','referrer_commission','processing_fee') DEFAULT 'platform_fee' AFTER `amount`,
ADD COLUMN `percentage_applied` decimal(5,2) DEFAULT NULL AFTER `revenue_type`,
ADD COLUMN `original_amount` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `percentage_applied`,
ADD COLUMN `description` varchar(255) DEFAULT NULL AFTER `original_amount`;

-- 8. Enhance webhook_endpoints table
ALTER TABLE `webhook_endpoints`
ADD COLUMN `event_types` JSON DEFAULT NULL AFTER `secret`,
ADD COLUMN `retry_count` int(2) DEFAULT 3 AFTER `event_types`,
ADD COLUMN `timeout_seconds` int(3) DEFAULT 10 AFTER `retry_count`;

-- 9. Insert default payout schedules for existing tenants
INSERT INTO `payout_schedules` (`tenant_id`, `frequency`, `minimum_amount`, `auto_payout_enabled`)
SELECT `id`, 'monthly', 10.00, 0 FROM `tenants` WHERE `active` = 1;

-- 10. Insert sample payout methods for existing tenants (optional)
INSERT INTO `payout_methods` (`tenant_id`, `method_type`, `method_name`, `account_details`, `is_default`, `is_verified`)
SELECT `id`, 'bank_transfer', 'Default Bank Account', 
JSON_OBJECT('account_number', '****1234', 'bank_name', 'Sample Bank', 'account_name', `name`),
1, 0 FROM `tenants` WHERE `active` = 1;

-- Migration completed successfully
-- Run this script to enable complete payout functionality
