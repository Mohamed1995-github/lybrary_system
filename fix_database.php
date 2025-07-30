<?php
require_once 'config/db.php';

echo "<h2>🔧 Database Fix Script</h2>";

try {
    // Check if borrowers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'borrowers'");
    if ($stmt->rowCount() == 0) {
        // Create borrowers table
        $sql = "CREATE TABLE borrowers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            national_id VARCHAR(20) NOT NULL UNIQUE,
            phone VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "✅ Borrowers table created successfully!<br>";
    } else {
        echo "ℹ️  Borrowers table already exists.<br>";
    }
    
    // Check if loans table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'loans'");
    if ($stmt->rowCount() > 0) {
        echo "ℹ️  Loans table found.<br>";
        
        // Check current loans table structure
        $columns = $pdo->query("SHOW COLUMNS FROM loans")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Current loans table columns:</h3><ul>";
        foreach ($columns as $col) {
            echo "<li>{$col['Field']} - {$col['Type']}</li>";
        }
        echo "</ul>";
        
        // Check if borrower_id column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM loans LIKE 'borrower_id'");
        if ($stmt->rowCount() == 0) {
            // Add borrower_id column to loans table
            $pdo->exec("ALTER TABLE loans ADD COLUMN borrower_id INT NULL");
            echo "✅ Added borrower_id column to loans table!<br>";
        } else {
            echo "ℹ️  borrower_id column already exists in loans table.<br>";
        }
        
        // Check if return_date column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM loans LIKE 'return_date'");
        if ($stmt->rowCount() == 0) {
            // Add return_date column to loans table
            $pdo->exec("ALTER TABLE loans ADD COLUMN return_date DATE NULL");
            echo "✅ Added return_date column to loans table!<br>";
        } else {
            echo "ℹ️  return_date column already exists in loans table.<br>";
        }
        
        // Try to add foreign key constraint
        try {
            $pdo->exec("ALTER TABLE loans ADD CONSTRAINT fk_loans_borrower FOREIGN KEY (borrower_id) REFERENCES borrowers(id)");
            echo "✅ Added foreign key constraint!<br>";
        } catch (PDOException $e) {
            echo "⚠️  Could not add foreign key constraint (may already exist): " . $e->getMessage() . "<br>";
        }
        
    } else {
        echo "⚠️  Loans table not found. You may need to create it separately.<br>";
    }
    
    echo "<br><h3>🎉 Database fix completed!</h3>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li><a href='modules/borrowers/add.php'>Add borrowers</a></li>";
    echo "<li><a href='modules/borrowers/list.php'>View borrowers list</a></li>";
    echo "<li><a href='modules/loans/borrow.php'>Create loans</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?> 