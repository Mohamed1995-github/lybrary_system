<?php
require_once 'config/db.php';

try {
    // Create borrowers table
    $sql = "CREATE TABLE IF NOT EXISTS borrowers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        national_id VARCHAR(20) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Borrowers table created successfully!\n";
    
    // Check if loans table exists and has borrower_id column
    $stmt = $pdo->query("SHOW TABLES LIKE 'loans'");
    if ($stmt->rowCount() > 0) {
        // Check if borrower_id column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM loans LIKE 'borrower_id'");
        if ($stmt->rowCount() == 0) {
            // Add borrower_id column to loans table
            $pdo->exec("ALTER TABLE loans ADD COLUMN borrower_id INT");
            $pdo->exec("ALTER TABLE loans ADD FOREIGN KEY (borrower_id) REFERENCES borrowers(id)");
            echo "✅ Added borrower_id column to loans table!\n";
        } else {
            echo "ℹ️  borrower_id column already exists in loans table.\n";
        }
        
        // Check if return_date column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM loans LIKE 'return_date'");
        if ($stmt->rowCount() == 0) {
            // Add return_date column to loans table
            $pdo->exec("ALTER TABLE loans ADD COLUMN return_date DATE NULL");
            echo "✅ Added return_date column to loans table!\n";
        } else {
            echo "ℹ️  return_date column already exists in loans table.\n";
        }
    } else {
        echo "⚠️  Loans table not found. You may need to create it separately.\n";
    }
    
    echo "\n🎉 Setup completed successfully!\n";
    echo "You can now:\n";
    echo "1. Add borrowers at: modules/borrowers/add.php\n";
    echo "2. View borrowers at: modules/borrowers/list.php\n";
    echo "3. Create loans at: modules/loans/borrow.php\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 