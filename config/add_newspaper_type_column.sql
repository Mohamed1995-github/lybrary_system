-- إضافة حقل newspaper_type لتتبع نوع الجريدة
-- Add newspaper_type column to track newspaper type based on addition method

ALTER TABLE items
ADD COLUMN newspaper_type VARCHAR(20);

-- إضافة فهرس للأداء
-- Add index for performance
CREATE INDEX idx_newspaper_type ON items(newspaper_type);
