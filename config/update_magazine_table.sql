-- Update items table to support additional magazine fields
-- Run this SQL script to add the new columns to the existing table

ALTER TABLE items
ADD COLUMN magazine_type VARCHAR(100),
ADD COLUMN issn VARCHAR(50),
ADD COLUMN magazine_number VARCHAR(100),
ADD COLUMN cabinet_number VARCHAR(50),
ADD COLUMN shelf_number VARCHAR(50),
ADD COLUMN drawer_number VARCHAR(50),
ADD COLUMN registration_date DATE,
ADD COLUMN modification_date DATE,
ADD COLUMN periodical_file VARCHAR(255);

-- Change publication_year to publication_date
ALTER TABLE items MODIFY COLUMN publication_year DATE;

-- Optional: Update existing magazine records if needed
-- UPDATE items SET registration_date = DATE(created_at) WHERE type = 'magazine' AND registration_date IS NULL;
-- UPDATE items SET modification_date = DATE(updated_at) WHERE type = 'magazine' AND modification_date IS NULL;