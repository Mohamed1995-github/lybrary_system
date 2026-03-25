-- Add parts column to items table for books and other materials
-- Run this SQL to add the new column to existing table

ALTER TABLE items
ADD COLUMN parts INT DEFAULT 1;

-- Optional: initialize parts based on a heuristic if needed
-- UPDATE items SET parts = 1 WHERE parts IS NULL;