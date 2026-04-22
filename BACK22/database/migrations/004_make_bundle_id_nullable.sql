-- Make bundle_id nullable in transactions table to support custom votes
-- Custom votes don't have a bundle_id since the vote count is user-defined

-- Drop the foreign key constraint first
ALTER TABLE `transactions` 
DROP FOREIGN KEY `transactions_ibfk_4`;

-- Modify bundle_id to allow NULL
ALTER TABLE `transactions` 
MODIFY COLUMN `bundle_id` int(11) DEFAULT NULL COMMENT 'NULL for custom votes';

-- Re-add the foreign key constraint with NULL support
ALTER TABLE `transactions` 
ADD CONSTRAINT `transactions_ibfk_4` 
FOREIGN KEY (`bundle_id`) REFERENCES `vote_bundles` (`id`) 
ON DELETE CASCADE;
