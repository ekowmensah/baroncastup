-- Migration: Rename contestants table to nominees
-- Date: 2025-10-08
-- Description: Rename contestants to nominees for better semantic meaning

-- Step 1: Rename contestants table to nominees
RENAME TABLE `contestants` TO `nominees`;

-- Step 2: Rename contestant_categories table to nominee_categories  
RENAME TABLE `contestant_categories` TO `nominee_categories`;

-- Step 3: Update foreign key column names in nominee_categories
ALTER TABLE `nominee_categories` 
CHANGE COLUMN `contestant_id` `nominee_id` int(11) NOT NULL;

-- Step 4: Update any other tables that reference contestants
-- Update votes table foreign key
ALTER TABLE `votes` 
CHANGE COLUMN `contestant_id` `nominee_id` int(11) DEFAULT NULL;

-- Step 5: Update leaderboard_cache table if it exists
-- ALTER TABLE `leaderboard_cache` 
-- CHANGE COLUMN `contestant_id` `nominee_id` int(11) NOT NULL;

-- Step 6: Add indexes for performance
ALTER TABLE `nominee_categories`
ADD INDEX `idx_nominee_category` (`nominee_id`, `category_id`),
ADD INDEX `idx_short_code` (`short_code`);

-- Step 7: Add unique constraint for short_code per category
ALTER TABLE `nominee_categories`
ADD UNIQUE KEY `unique_short_code_per_category` (`category_id`, `short_code`);
