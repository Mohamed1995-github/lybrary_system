-- Update items table to support additional newspaper fields
-- Run this SQL script to add the new columns to the existing table

ALTER TABLE items
ADD COLUMN IF NOT EXISTS issue_from VARCHAR(50),
ADD COLUMN IF NOT EXISTS issue_to VARCHAR(50),
ADD COLUMN IF NOT EXISTS newspaper_date DATE,
ADD COLUMN IF NOT EXISTS missing_issues TEXT,
ADD COLUMN IF NOT EXISTS box_number VARCHAR(50),
ADD COLUMN IF NOT EXISTS cabinet_number VARCHAR(50),
ADD COLUMN IF NOT EXISTS shelf_number VARCHAR(50),
ADD COLUMN IF NOT EXISTS drawer_number VARCHAR(50),
ADD COLUMN IF NOT EXISTS registration_date DATE,
ADD COLUMN IF NOT EXISTS modification_date DATE;

-- Optional: Update existing newspaper records if needed
-- UPDATE items SET registration_date = DATE(created_at) WHERE type = 'newspaper' AND registration_date IS NULL;
-- UPDATE items SET modification_date = DATE(updated_at) WHERE type = 'newspaper' AND modification_date IS NULL;