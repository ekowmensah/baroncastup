-- Add category support to voting system
-- This enables category-specific vote counting

-- Add category_id to transactions table
ALTER TABLE `transactions` 
ADD COLUMN `category_id` INT(11) NULL 
AFTER `contestant_id`,
ADD INDEX `idx_transactions_category` (`category_id`);

-- Add category_id to votes table
ALTER TABLE `votes` 
ADD COLUMN `category_id` INT(11) NULL 
AFTER `contestant_id`,
ADD INDEX `idx_votes_category` (`category_id`);

-- Add category_id to vote_ledger table
ALTER TABLE `vote_ledger` 
ADD COLUMN `category_id` INT(11) NULL 
AFTER `contestant_id`,
ADD INDEX `idx_vote_ledger_category` (`category_id`);

-- Add foreign key constraints (optional, for data integrity)
ALTER TABLE `transactions` 
ADD CONSTRAINT `fk_transactions_category` 
FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `votes` 
ADD CONSTRAINT `fk_votes_category` 
FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `vote_ledger` 
ADD CONSTRAINT `fk_vote_ledger_category` 
FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Create index for category-specific vote counting
CREATE INDEX `idx_votes_event_category_contestant` 
ON `votes` (`event_id`, `category_id`, `contestant_id`);

-- Create index for category-specific transaction queries
CREATE INDEX `idx_transactions_event_category` 
ON `transactions` (`event_id`, `category_id`);
