<?php
/**
 * Script simple pour corriger les rôles utilisateurs
 */

require_once 'includes/auth.php';

echo "=== Correction des rôles utilisateurs ===\n\n";

try {
    // Ajouter la colonne role si elle n'existe pas
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'assistant'");
    echo "✅ Colonne 'role' ajoutée/vérifiée\n\n";
    
    // Afficher les utilisateurs
    $stmt = $pdo->query("SELECT id, username, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Utilisateurs trouvés:\n";
    foreach ($users as $user) {
        echo "- {$user['username']} (ID: {$user['id']}, Rôle: {$user['role']})\n";
    }
    
    // Assigner le rôle admin au premier utilisateur
    if (!empty($users)) {
        $first_user = $users[0];
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->execute([$first_user['id']]);
        echo "\n✅ {$first_user['username']} -> admin\n";
        
        // Assigner librarian aux autres
        for ($i = 1; $i < count($users); $i++) {
            $user = $users[$i];
            $stmt = $pdo->prepare("UPDATE users SET role = 'librarian' WHERE id = ?");
            $stmt->execute([$user['id']]);
            echo "✅ {$user['username']} -> librarian\n";
        }
    }
    
    echo "\n🎉 Rôles configurés ! Maintenant connectez-vous et testez:\n";
    echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=magazine\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?> 