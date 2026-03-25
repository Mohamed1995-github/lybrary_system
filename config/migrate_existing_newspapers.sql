-- ترحيل البيانات الموجودة - تحديث newspaper_type بناءً على العنوان
-- Migrate existing data - Update newspaper_type based on title

-- تحديث جريدة الشعب (Popular newspaper)
UPDATE items 
SET newspaper_type = 'popular'
WHERE type = 'newspaper' 
AND (
    title LIKE '%جريدة الشعب%' 
    OR title LIKE '%Journal du Peuple%'
    OR title = 'جريدة الشعب'
    OR title = 'Journal du Peuple'
);

-- تحديث الجريدة الرسمية (Official newspaper)
UPDATE items 
SET newspaper_type = 'official'
WHERE type = 'newspaper' 
AND (
    title LIKE '%الجريدة الرسمية%' 
    OR title LIKE '%Journal officiel%'
    OR title LIKE '%Journal Officiel%'
    OR title = 'الجريدة الرسمية'
    OR title = 'Journal officiel'
);

-- التحقق من النتائج
-- Verify results
SELECT 
    newspaper_type,
    COUNT(*) as count,
    GROUP_CONCAT(DISTINCT title SEPARATOR ', ') as titles
FROM items 
WHERE type = 'newspaper'
GROUP BY newspaper_type;
