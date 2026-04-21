-- Hubtel Integration Database Migration
-- Created: 2024
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
  INDEX `idx_session_token` (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. Create payment_gateway_logs table
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
-- 3. Create payment_transactions table (if not exists)
-- ============================================
CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `gateway_reference` VARCHAR(100) NULL,
  `external_transaction_id` VARCHAR(100) NULL,
  `gateway_provider` VARCHAR(50) NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'GHS',
  `status` ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending',
  `phone_number` VARCHAR(20) NULL,
  `email` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `metadata` JSON NULL,
  `gateway_response` TEXT NULL,
  `webhook_verified` TINYINT(1) DEFAULT 0,
  `otp_verified` TINYINT(1) DEFAULT 0,
  `otp_verification_id` INT(11) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_reference` (`reference`),
  INDEX `idx_gateway_reference` (`gateway_reference`),
  INDEX `idx_external_txn` (`external_transaction_id`),
  INDEX `idx_gateway_provider` (`gateway_provider`),
  INDEX `idx_status_gateway` (`status`, `gateway_provider`),
  INDEX `idx_created_status` (`created_at`, `status`),
  FOREIGN KEY (`otp_verification_id`) REFERENCES `payment_otp_verifications`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. Create otp_requests table (if not exists)
-- ============================================
CREATE TABLE IF NOT EXISTS `otp_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `phone_number` VARCHAR(20) NOT NULL,
  `otp_code` VARCHAR(255) NOT NULL COMMENT 'Hashed OTP',
  `purpose` VARCHAR(50) NOT NULL COMMENT 'payment, login, verification',
  `consumed` TINYINT(1) DEFAULT 0,
  `attempts` INT(11) DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_phone_purpose` (`phone_number`, `purpose`),
  INDEX `idx_consumed_expires` (`consumed`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. Create payment_gateways table (if not exists)
-- ============================================
CREATE TABLE IF NOT EXISTS `payment_gateways` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `provider` VARCHAR(50) NOT NULL UNIQUE,
  `config` JSON NOT NULL,
  `supported_methods` JSON NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `priority` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_provider` (`provider`),
  INDEX `idx_active_priority` (`is_active`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. Insert default gateway configurations
-- ============================================
INSERT INTO `payment_gateways` (`name`, `provider`, `config`, `supported_methods`, `is_active`, `priority`)
VALUES 
(
  'Hubtel Direct Receive Money',
  'hubtel',
  JSON_OBJECT(
    'client_id', '',
    'client_secret', '',
    'merchant_account', '',
    'base_url', 'https://rmp.hubtel.com',
    'status_check_url', 'https://api-txnstatus.hubtel.com',
    'currency', 'GHS',
    'ip_whitelist', JSON_ARRAY()
  ),
  JSON_ARRAY('mobile_money'),
  0,
  2
),
(
  'Paystack Payment Gateway',
  'paystack',
  JSON_OBJECT(
    'secret_key', '',
    'public_key', '',
    'webhook_secret', '',
    'base_url', 'https://api.paystack.co',
    'currency', 'GHS'
  ),
  JSON_ARRAY('mobile_money', 'card', 'bank_transfer'),
  0,
  1
)
ON DUPLICATE KEY UPDATE
  config = VALUES(config),
  updated_at = NOW();

-- ============================================
-- 7. Create view for payment analytics
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
-- Migration Complete
-- ============================================
-- Next Steps:
-- 1. Update Hubtel credentials in payment_gateways table
-- 2. Update Paystack credentials in payment_gateways table
-- 3. Add your server IP to ip_whitelist array (Hubtel)
-- 4. Test payment flow with test credentials
-- 5. Enable gateways (set is_active = 1)
-- 6. Monitor payment_gateway_logs for issues
