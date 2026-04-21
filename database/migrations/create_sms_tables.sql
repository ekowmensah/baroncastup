-- SMS Gateways Table
CREATE TABLE IF NOT EXISTS `sms_gateways` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `type` enum('mnotify', 'hubtel') NOT NULL,
    `api_key` varchar(255) NOT NULL,
    `client_id` varchar(255) DEFAULT NULL,
    `client_secret` varchar(255) DEFAULT NULL,
    `sender_id` varchar(50) NOT NULL,
    `base_url` varchar(255) DEFAULT NULL,
    `test_phone` varchar(20) DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `priority` int(11) DEFAULT 1,
    `config` json DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_type_active` (`type`, `is_active`),
    KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Logs Table
CREATE TABLE IF NOT EXISTS `sms_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `phone` varchar(20) NOT NULL,
    `message` text NOT NULL,
    `gateway_id` int(11) DEFAULT NULL,
    `gateway_type` varchar(20) NOT NULL,
    `status` enum('pending', 'sent', 'failed', 'delivered', 'undelivered') DEFAULT 'pending',
    `response` json DEFAULT NULL,
    `vote_id` int(11) DEFAULT NULL,
    `transaction_id` int(11) DEFAULT NULL,
    `retry_count` int(11) DEFAULT 0,
    `last_retry_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_phone` (`phone`),
    KEY `idx_status` (`status`),
    KEY `idx_gateway` (`gateway_id`),
    KEY `idx_vote` (`vote_id`),
    KEY `idx_transaction` (`transaction_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`gateway_id`) REFERENCES `sms_gateways`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`vote_id`) REFERENCES `votes`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Templates Table (for future customization)
CREATE TABLE IF NOT EXISTS `sms_templates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `type` enum('vote_confirmation', 'payment_receipt', 'event_reminder', 'custom') NOT NULL,
    `template` text NOT NULL,
    `variables` json DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `tenant_id` int(11) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_type_active` (`type`, `is_active`),
    KEY `idx_tenant` (`tenant_id`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default SMS templates
INSERT INTO `sms_templates` (`name`, `type`, `template`, `variables`, `is_active`) VALUES
('Default Vote Confirmation', 'vote_confirmation', 
'Thank you for voting!\n\nNominee: {nominee_name}\nEvent: {event_name}\nCategory: {category_name}\nVotes: {vote_count}\nAmount: {amount}\nReceipt: {receipt_number}\n\nThank you for your participation!',
'["nominee_name", "event_name", "category_name", "vote_count", "amount", "receipt_number"]',
1),

('Payment Receipt', 'payment_receipt',
'Payment Successful!\n\nAmount: {amount}\nTransaction ID: {transaction_id}\nDate: {date}\nMethod: {payment_method}\n\nThank you!',
'["amount", "transaction_id", "date", "payment_method"]',
1),

('Event Reminder', 'event_reminder',
'Reminder: {event_name} voting is now live!\n\nVote for your favorite nominees now.\nEvent ends: {end_date}\n\nVote now!',
'["event_name", "end_date"]',
1);

-- Insert sample SMS gateways (commented out - to be configured by admin)
-- INSERT INTO `sms_gateways` (`name`, `type`, `api_key`, `sender_id`, `is_active`, `priority`) VALUES
-- ('mNotify Primary', 'mnotify', 'your_mnotify_api_key', 'SmartCast', 1, 1),
-- ('Hubtel Backup', 'hubtel', 'your_hubtel_api_key', 'SmartCast', 0, 2);

-- Add indexes for better performance
CREATE INDEX `idx_sms_logs_phone_status` ON `sms_logs` (`phone`, `status`);
CREATE INDEX `idx_sms_logs_created_status` ON `sms_logs` (`created_at`, `status`);
CREATE INDEX `idx_sms_gateways_active_priority` ON `sms_gateways` (`is_active`, `priority`);
