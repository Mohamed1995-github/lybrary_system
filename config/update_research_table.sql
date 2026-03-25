-- Update student_research table to support new research fields
-- Run this SQL script to add the new columns to the existing table

ALTER TABLE student_research
ADD COLUMN academic_year VARCHAR(50),
ADD COLUMN researcher VARCHAR(255) NOT NULL,
ADD COLUMN institution VARCHAR(255),
ADD COLUMN research_nature ENUM('report', 'master', 'phd'),
ADD COLUMN inventory_number VARCHAR(100),
ADD COLUMN box_number VARCHAR(50),
ADD COLUMN cabinet_number VARCHAR(50),
ADD COLUMN shelf_number VARCHAR(50),
ADD COLUMN drawer_number VARCHAR(50),
ADD COLUMN registration_date DATE,
ADD COLUMN modification_number VARCHAR(100);

-- Optional: Drop old columns that are no longer needed
-- ALTER TABLE student_research DROP COLUMN student_name;
-- ALTER TABLE student_research DROP COLUMN nni;
-- ALTER TABLE student_research DROP COLUMN summary;