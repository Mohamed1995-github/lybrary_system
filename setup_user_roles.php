<?php
/**
 * Script pour configurer les rôles des utilisateurs
 */

require_once 'includes/auth.php';

echo "=== Configuration des rôles utilisateurs ===\n\n";

// Vérifier si la table users existe et sa structure
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('role', $columns)) {
        echo "❌ La colonne 'role' n'existe pas dans la table users\n";
        echo "Ajout de la colonne 'role'...\n";
        
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'assistant'");
        echo "✅ Colonne 'role' ajoutée avec succès\n\n";
    } else {
        echo "✅ La colonne 'role' existe déjà\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification de la table: " . $e->getMessage() . "\n";
    exit;
}

// Afficher les utilisateurs existants
echo "=== Utilisateurs existants ===\n";
$stmt = $pdo->query("SELECT id, username, email, role FROM users");
$users = $stmt->fetchAll();

if (empty($users)) {
    echo "Aucun utilisateur trouvé.\n";
} else {
    foreach ($users as $user) {
        echo "ID: {$user['id']} | Username: {$user['username']} | Email: {$user['email']} | Rôle: {$user['role']}\n";
    }
}

echo "\n=== Configuration des rôles ===\n";
echo "Les rôles disponibles sont:\n";
echo "- admin: Accès complet à tous les modules\n";
echo "- librarian: Accès à la plupart des modules (sauf administration)\n";
echo "- assistant: Accès en lecture seule à certains modules\n\n";

// Demander à l'utilisateur de configurer les rôles
echo "Voulez-vous configurer les rôles maintenant ? (y/n): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));
fclose($handle);

if (strtolower($response) === 'y' || strtolower($response) === 'yes') {
    echo "\nConfiguration des rôles:\n";
    
    foreach ($users as $user) {
        echo "\nUtilisateur: {$user['username']} (ID: {$user['id']})\n";
        echo "Rôle actuel: {$user['role']}\n";
        echo "Nouveau rôle (admin/librarian/assistant) [{$user['role']}]: ";
        
        $handle = fopen("php://stdin", "r");
        $new_role = trim(fgets($handle));
        fclose($handle);
        
        if (!empty($new_role) && in_array($new_role, ['admin', 'librarian', 'assistant'])) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $user['id']]);
            echo "✅ Rôle mis à jour: {$user['username']} -> {$new_role}\n";
        } else {
            echo "⏭️  Rôle inchangé\n";
        }
    }
}

echo "\n=== Test de connexion ===\n";
echo "Maintenant, connectez-vous avec un utilisateur et testez:\n";
echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=magazine\n\n";

echo "🎉 Configuration terminée !\n";
?> 