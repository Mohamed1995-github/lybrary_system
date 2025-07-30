<?php
require_once 'config/db.php';

echo "<h2>🔧 Adding Field Column to Items Table</h2>";

try {
    // Check if field column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM items LIKE 'field'");
    if ($stmt->rowCount() == 0) {
        // Add field column to items table
        $sql = "ALTER TABLE items ADD COLUMN field VARCHAR(100) DEFAULT 'General' AFTER classification";
        $pdo->exec($sql);
        echo "✅ Field column added successfully to items table!<br>";
        
        // Update existing records to have a default field
        $updateSql = "UPDATE items SET field = 'General' WHERE field IS NULL OR field = ''";
        $pdo->exec($updateSql);
        echo "✅ Updated existing records with default field value.<br>";
    } else {
        echo "ℹ️  Field column already exists in items table.<br>";
    }
    
    echo "<br>🎉 Field categorization system is ready!<br>";
    echo "You can now:<br>";
    echo "1. Add/edit items with field categorization<br>";
    echo "2. View books grouped by field<br>";
    echo "3. Filter books by specific fields<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?> 