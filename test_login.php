<?php
/**
 * Script de test de connexion simple
 * Simple login test script
 */

require_once 'includes/auth.php';

echo "=== Test de Connexion Simple ===\n\n";

// Vérifier si déjà connecté
if (isset($_SESSION['uid'])) {
    echo "✅ Déjà connecté\n";
    echo "ID: " . $_SESSION['uid'] . "\n";
    echo "Nom: " . ($_SESSION['name'] ?? $_SESSION['employee_name'] ?? 'Non défini') . "\n";
    echo "Fonction: " . ($_SESSION['employee_function'] ?? 'Non défini') . "\n\n";
    
    echo "🎯 Testez maintenant:\n";
    echo "php debug_admin_access.php\n";
    exit;
}

echo "❌ Pas connecté\n\n";

// Essayer de se connecter automatiquement
try {
    // Rechercher n'importe quel employé
    $stmt = $pdo->prepare("SELECT * FROM employees LIMIT 1");
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        echo "✅ Employé trouvé dans la base de données\n";
        echo "ID: " . $employee['id'] . "\n";
        echo "Nom: " . $employee['name'] . "\n";
        echo "Fonction: " . $employee['function'] . "\n\n";

        // Créer la session
        $_SESSION['uid'] = $employee['id'];
        $_SESSION['name'] = $employee['name'];
        $_SESSION['email'] = $employee['email'];
        $_SESSION['employee_name'] = $employee['name'];
        $_SESSION['employee_function'] = $employee['function'];
        $_SESSION['employee_rights'] = $employee['access_rights'];
        $_SESSION['lang'] = 'ar';

        echo "✅ Connexion réussie!\n";
        echo "Session créée avec succès\n\n";

        echo "🎯 Maintenant testez:\n";
        echo "php debug_admin_access.php\n";

    } else {
        echo "❌ Aucun employé trouvé dans la base de données\n";
        echo "La table 'employees' est vide\n";
    }

} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n";
?> 