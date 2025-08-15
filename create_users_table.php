<?php
echo "=== Création de la table users ===\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=library_system', 'root', '');
    
    // Vérifier si la table users existe déjà
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ La table 'users' existe déjà\n";
    } else {
        // Créer la table users
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            role ENUM('admin', 'employee', 'user') DEFAULT 'user',
            name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✅ Table 'users' créée avec succès\n";
    }
    
    // Vérifier s'il y a des utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Créer un utilisateur admin par défaut
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, name) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $admin_password, 'admin@library.com', 'admin', 'Administrateur']);
        
        echo "✅ Utilisateur admin créé:\n";
        echo "- Username: admin\n";
        echo "- Password: admin123\n";
        echo "- Role: admin\n";
    } else {
        echo "✅ Des utilisateurs existent déjà dans la table\n";
    }
    
    // Afficher la structure de la table
    echo "\nStructure de la table 'users':\n";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Afficher les utilisateurs existants
    echo "\nUtilisateurs existants:\n";
    $stmt = $pdo->query("SELECT id, username, email, role, name FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        echo "- ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}, Name: {$user['name']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Création terminée ===\n";
?>

