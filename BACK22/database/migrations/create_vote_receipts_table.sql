-- Create vote_receipts table if it doesn't exist
CREATE TABLE IF NOT EXISTS `vote_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `short_code` varchar(8) NOT NULL,
  `public_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_transaction` (`transaction_id`),
  UNIQUE KEY `unique_short_code` (`short_code`),
  KEY `idx_short_code` (`short_code`),
  KEY `idx_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
