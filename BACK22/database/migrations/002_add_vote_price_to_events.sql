-- Add vote_price column to events table
-- This allows each event to have a custom voting price

ALTER TABLE `events` 
ADD COLUMN `vote_price` DECIMAL(10,2) DEFAULT 0.50 COMMENT 'Price per vote in GHS' 
AFTER `end_date`;

-- Update existing events to have default vote price
UPDATE `events` SET `vote_price` = 0.50 WHERE `vote_price` IS NULL;
