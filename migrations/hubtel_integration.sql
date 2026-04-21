-- Hubtel Integration Database Migration
-- Created: 2025-10-21
-- Description: Add support for Hubtel payment gateway with OTP verification

-- ============================================
-- 1. Create payment_otp_verifications table
-- ============================================
CREATE TABLE IF NOT EXISTS `payment_otp_verifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `phone_number` VARCHAR(20) NOT NULL,
  `otp_request_id` INT(11) NULL,
  `verified_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `session_token` VARCHAR(64) NOT NULL,
  `used_for_payment` TINYINT(1) DEFAULT 0,
  `payment_transaction_id` INT(11) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_phone_session` (`phone_number`, `session_token`),
  INDEX `idx_expires` (`expires_at`),
  INDEX `idx_session_token` (`session_token`),
  FOREIGN KEY (`otp_request_id`) REFERENCES `otp_requests`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. Extend payment_transactions table
-- ============================================
ALTER TABLE `payment_transactions`
ADD COLUMN `otp_verified` TINYINT(1) DEFAULT 0 AFTER `webhook_verified`,
ADD COLUMN `otp_verification_id` INT(11) NULL AFTER `otp_verified`,
ADD COLUMN `gateway_provider` VARCHAR(50) NULL AFTER `gateway_id`,
ADD COLUMN `external_transaction_id` VARCHAR(100) NULL AFTER `gateway_reference`,
ADD INDEX `idx_external_txn` (`external_transaction_id`),
ADD INDEX `idx_gateway_provider` (`gateway_provider`);

-- ============================================
-- 3. Update Hubtel gateway configuration
-- ============================================
UPDATE `payment_gateways` 
SET 
  `config` = JSON_SET(
    `config`,
    '$.client_id', '',
    '$.client_secret', '',
    '$.merchant_account', '',
    '$.base_url', 'https://rmp.hubtel.com',
    '$.status_check_url', 'https://api-txnstatus.hubtel.com',
    '$.currency', 'GHS',
    '$.ip_whitelist', JSON_ARRAY()
  ),
  `supported_methods` = '["mobile_money"]',
  `priority` = 2,
  `updated_at` = NOW()
WHERE `provider` = 'hubtel';

-- ============================================
-- 4. Create payment_gateway_logs table (optional)
-- ============================================
CREATE TABLE IF NOT EXISTS `payment_gateway_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `gateway_provider` VARCHAR(50) NOT NULL,
  `transaction_reference` VARCHAR(100) NULL,
  `request_type` VARCHAR(50) NOT NULL COMMENT 'initialize, verify, webhook, status_check',
  `request_data` TEXT NULL,
  `response_data` TEXT NULL,
  `response_code` VARCHAR(20) NULL,
  `http_status` INT(11) NULL,
  `error_message` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_gateway_reference` (`gateway_provider`, `transaction_reference`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. Add foreign key for otp_verification_id
-- ============================================
ALTER TABLE `payment_transactions`
ADD CONSTRAINT `fk_payment_otp_verification` 
FOREIGN KEY (`otp_verification_id`) 
REFERENCES `payment_otp_verifications`(`id`) 
ON DELETE SET NULL;

-- ============================================
-- 6. Create indexes for performance
-- ============================================
ALTER TABLE `payment_transactions`
ADD INDEX `idx_status_gateway` (`status`, `gateway_provider`),
ADD INDEX `idx_created_status` (`created_at`, `status`);

ALTER TABLE `otp_requests`
ADD INDEX `idx_consumed_expires` (`consumed`, `expires_at`);

-- ============================================
-- 7. Insert sample configuration (for testing)
-- ============================================
-- Note: Update these values with your actual Hubtel credentials
-- INSERT INTO `payment_gateways` (`name`, `provider`, `config`, `supported_methods`, `is_active`, `priority`)
-- VALUES (
--   'Hubtel Direct Receive Money',
--   'hubtel',
--   JSON_OBJECT(
--     'client_id', 'YOUR_CLIENT_ID',
--     'client_secret', 'YOUR_CLIENT_SECRET',
--     'merchant_account', 'YOUR_POS_SALES_ID',
--     'base_url', 'https://rmp.hubtel.com',
--     'status_check_url', 'https://api-txnstatus.hubtel.com',
--     'currency', 'GHS',
--     'ip_whitelist', JSON_ARRAY('YOUR_SERVER_IP')
--   ),
--   JSON_ARRAY('mobile_money'),
--   0,
--   2
-- ) ON DUPLICATE KEY UPDATE
--   config = VALUES(config),
--   updated_at = NOW();

-- ============================================
-- 8. Create view for payment analytics
-- ============================================
CREATE OR REPLACE VIEW `v_payment_gateway_stats` AS
SELECT 
    gateway_provider,
    DATE(created_at) as payment_date,
    COUNT(*) as total_transactions,
    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_transactions,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_transactions,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_transactions,
    SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_revenue,
    AVG(CASE WHEN status = 'success' THEN amount ELSE NULL END) as avg_transaction_amount,
    SUM(CASE WHEN otp_verified = 1 THEN 1 ELSE 0 END) as otp_verified_count
FROM payment_transactions
WHERE gateway_provider IS NOT NULL
GROUP BY gateway_provider, DATE(created_at);

-- ============================================
-- 9. Cleanup old OTP verifications (scheduled task)
-- ============================================
-- Run this periodically to clean up expired verifications
-- DELETE FROM payment_otp_verifications 
-- WHERE expires_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- ============================================
-- 10. Grant permissions (if needed)
-- ============================================
-- GRANT SELECT, INSERT, UPDATE ON payment_otp_verifications TO 'your_db_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE ON payment_gateway_logs TO 'your_db_user'@'localhost';

-- ============================================
-- Migration Complete
-- ============================================
-- Next Steps:
-- 1. Update Hubtel credentials in payment_gateways table
-- 2. Add your server IP to ip_whitelist array
-- 3. Test OTP flow with test phone numbers
-- 4. Enable Hubtel gateway (set is_active = 1)
-- 5. Monitor payment_gateway_logs for issues
