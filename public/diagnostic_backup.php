<?php
echo "<h1>Backup System Diagnostic</h1>";

// Check if we can access the backup file
echo "<h2>1. File Access Test</h2>";
$backup_file = __DIR__ . '/../modules/administration/backup.php';
if (file_exists($backup_file)) {
    echo "✅ Backup file exists: " . $backup_file . "<br>";
} else {
    echo "❌ Backup file not found: " . $backup_file . "<br>";
}

// Check database connection
echo "<h2>2. Database Connection Test</h2>";
try {
    require_once __DIR__ . '/../config/db.php';
    echo "✅ Database connection successful<br>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'library_system'");
    $result = $stmt->fetch();
    echo "✅ Database query successful. Tables found: " . $result['count'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Check backup directory
echo "<h2>3. Backup Directory Test</h2>";
$backup_dir = __DIR__ . '/../backups/';
if (is_dir($backup_dir)) {
    echo "✅ Backup directory exists: " . $backup_dir . "<br>";
} else {
    echo "⚠️ Backup directory doesn't exist, will be created automatically<br>";
}

// Check mysqldump availability
echo "<h2>4. MySQL Dump Test</h2>";
$output = [];
$return_var = 0;
exec("mysqldump --version", $output, $return_var);
if ($return_var === 0) {
    echo "✅ mysqldump is available<br>";
    echo "Version: " . implode(" ", $output) . "<br>";
} else {
    echo "❌ mysqldump not found or not accessible<br>";
}

// Check current URL and parameters
echo "<h2>5. URL Parameters Test</h2>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Module: " . ($_GET['module'] ?? 'not set') . "<br>";
echo "Action: " . ($_GET['action'] ?? 'not set') . "<br>";
echo "Language: " . ($_GET['lang'] ?? 'not set') . "<br>";

// Test router functionality
echo "<h2>6. Router Test</h2>";
$allowed_modules = [
    'acquisitions' => ['add', 'edit', 'delete', 'list'],
    'items' => ['add_book', 'add_magazine', 'add_newspaper', 'add_edit', 'edit', 'delete', 'list', 'list_grouped', 'statistics'],
    'loans' => ['add', 'borrow', 'return', 'list'],
    'borrowers' => ['add', 'edit', 'list'],
    'administration' => ['add', 'edit', 'delete', 'list', 'permissions_guide', 'reports', 'activity', 'backup'],
    'members' => ['add', 'edit', 'list']
];

$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? '';

if (isset($allowed_modules[$module])) {
    echo "✅ Module '$module' is allowed<br>";
    if (in_array($action, $allowed_modules[$module])) {
        echo "✅ Action '$action' is allowed for module '$module'<br>";
    } else {
        echo "❌ Action '$action' is not allowed for module '$module'<br>";
        echo "Allowed actions: " . implode(", ", $allowed_modules[$module]) . "<br>";
    }
} else {
    echo "❌ Module '$module' is not allowed<br>";
    echo "Allowed modules: " . implode(", ", array_keys($allowed_modules)) . "<br>";
}

echo "<h2>7. Quick Links</h2>";
echo "<a href='router.php?module=administration&action=backup&lang=ar'>Test Backup (Arabic)</a><br>";
echo "<a href='router.php?module=administration&action=backup&lang=fr'>Test Backup (French)</a><br>";
echo "<a href='router.php?module=administration&action=list&lang=ar'>Test Administration List</a><br>";
echo "<a href='dashboard.php?lang=ar'>Test Dashboard</a><br>";
?>
