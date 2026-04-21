-- Fix image paths from ghvotecast to smartcast
-- Run this SQL to update existing image paths

-- Update events table
UPDATE events 
SET featured_image = REPLACE(featured_image, '/ghvotecast/', '/smartcast/')
WHERE featured_image LIKE '%/ghvotecast/%';

-- Update contestants table  
UPDATE contestants 
SET image_url = REPLACE(image_url, '/ghvotecast/', '/smartcast/')
WHERE image_url LIKE '%/ghvotecast/%';

-- Check results
SELECT id, name, featured_image FROM events WHERE featured_image IS NOT NULL;
SELECT id, name, image_url FROM contestants WHERE image_url IS NOT NULL;
