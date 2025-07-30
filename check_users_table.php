<?php
/**
 * Script pour vérifier la structure de la table users
 */

require_once 'includes/auth.php';

echo "=== Vérification de la table users ===\n\n";

try {
    // Vérifier la structure de la table
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Structure de la table users:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\n=== Utilisateurs existants ===\n";
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "Aucun utilisateur trouvé.\n";
    } else {
        foreach ($users as $user) {
            echo "ID: {$user['id']} | Username: {$user['username']}";
            if (isset($user['role'])) {
                echo " | Rôle: {$user['role']}";
            }
            if (isset($user['email'])) {
                echo " | Email: {$user['email']}";
            }
            echo "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?> 