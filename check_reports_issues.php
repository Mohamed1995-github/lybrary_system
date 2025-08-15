<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=library_system', 'root', '');
    
    echo "Checking reports issues...\n";
    
    // Check if borrowers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'borrowers'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Borrowers table exists\n";
    } else {
        echo "❌ Borrowers table doesn't exist, should use 'members' table\n";
    }
    
    // Check if members table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'members'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Members table exists\n";
    } else {
        echo "❌ Members table doesn't exist\n";
    }
    
    // Test the items query
    $stmt = $pdo->prepare("SELECT SUM(copies) as total_copies, SUM(available_copies) as available_copies FROM items");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Items query result: " . json_encode($result) . "\n";
    
    // Test the employees query
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_employees FROM employees");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Employees query result: " . json_encode($result) . "\n";
    
    // Test the loans query
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_loans FROM loans");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Loans query result: " . json_encode($result) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
