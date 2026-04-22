-- Fix vote quantity column to support larger vote counts
-- This addresses the issue where votes are capped at 100 due to column constraints

-- Check current column definition
DESCRIBE votes;

-- Update the quantity column to support larger numbers
ALTER TABLE votes MODIFY COLUMN quantity INT UNSIGNED NOT NULL DEFAULT 1;

-- Also update leaderboard_cache if it exists
ALTER TABLE leaderboard_cache MODIFY COLUMN total_votes INT UNSIGNED NOT NULL DEFAULT 0;

-- Verify the changes
DESCRIBE votes;
DESCRIBE leaderboard_cache;

-- Test query to see current vote quantities
SELECT id, transaction_id, contestant_id, quantity, created_at 
FROM votes 
ORDER BY created_at DESC 
LIMIT 10;
