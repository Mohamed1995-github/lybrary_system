<?php
/**
 * Script pour assigner automatiquement les rôles aux utilisateurs
 */

require_once 'includes/auth.php';

echo "=== Attribution automatique des rôles ===\n\n";

try {
    // Vérifier si la colonne role existe
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('role', $columns)) {
        echo "Ajout de la colonne 'role'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'assistant'");
        echo "✅ Colonne 'role' ajoutée\n\n";
    }
    
    // Afficher les utilisateurs actuels
    echo "=== Utilisateurs actuels ===\n";
    $stmt = $pdo->query("SELECT id, username, email, role FROM users");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "Aucun utilisateur trouvé.\n";
        exit;
    }
    
    foreach ($users as $user) {
        echo "ID: {$user['id']} | Username: {$user['username']} | Rôle: {$user['role']}\n";
    }
    
    echo "\n=== Attribution des rôles ===\n";
    
    // Assigner le rôle 'admin' au premier utilisateur
    if (!empty($users)) {
        $first_user = $users[0];
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->execute([$first_user['id']]);
        echo "✅ {$first_user['username']} -> admin (administrateur principal)\n";
    }
    
    // Assigner le rôle 'librarian' aux autres utilisateurs
    for ($i = 1; $i < count($users); $i++) {
        $user = $users[$i];
        $stmt = $pdo->prepare("UPDATE users SET role = 'librarian' WHERE id = ?");
        $stmt->execute([$user['id']]);
        echo "✅ {$user['username']} -> librarian (employé)\n";
    }
    
    echo "\n=== Résultat final ===\n";
    $stmt = $pdo->query("SELECT id, username, role FROM users");
    $updated_users = $stmt->fetchAll();
    
    foreach ($updated_users as $user) {
        echo "{$user['username']} -> {$user['role']}\n";
    }
    
    echo "\n🎉 Rôles attribués avec succès !\n";
    echo "Maintenant vous pouvez vous connecter et accéder aux modules.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?> 