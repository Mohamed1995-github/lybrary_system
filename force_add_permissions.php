<?php
/**
 * Script pour forcer les permissions d'ajout de livres
 * Script to force book addition permissions
 */

require_once 'includes/auth.php';

echo "=== FORCE PERMISSIONS D'AJOUT ===\n\n";

if (!isset($_SESSION['uid'])) {
    echo "❌ Pas connecté\n";
    echo "🔧 Exécutez: php auto_login_admin.php\n";
    exit;
}

echo "✅ Connecté - ID: " . $_SESSION['uid'] . "\n";

try {
    // Vérifier l'employé actuel
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$_SESSION['uid']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo "❌ Employé non trouvé\n";
        exit;
    }

    echo "=== INFORMATIONS ACTUELLES ===\n";
    echo "Nom: " . $employee['name'] . "\n";
    echo "Fonction: " . $employee['function'] . "\n";
    echo "Droits actuels: " . $employee['access_rights'] . "\n\n";

    // Ajouter les permissions d'ajout de livres
    $currentRights = explode(',', $employee['access_rights']);
    $currentRights = array_map('trim', $currentRights);
    
    $addPermissions = [
        'إدارة الكتب',
        'Gestion des livres',
        'عرض الكتب',
        'Consultation des livres'
    ];

    // Ajouter les nouvelles permissions
    foreach ($addPermissions as $permission) {
        if (!in_array($permission, $currentRights)) {
            $currentRights[] = $permission;
        }
    }

    $newRightsString = implode(',', $currentRights);

    // Mettre à jour les permissions
    $updateStmt = $pdo->prepare("UPDATE employees SET access_rights = ? WHERE id = ?");
    $result = $updateStmt->execute([$newRightsString, $_SESSION['uid']]);

    if ($result) {
        echo "✅ Permissions mises à jour!\n";
        echo "Nouvelles permissions: " . $newRightsString . "\n\n";

        // Mettre à jour la session
        $_SESSION['employee_rights'] = $newRightsString;

        // Vérifier la mise à jour
        $stmt = $pdo->prepare("SELECT access_rights FROM employees WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);
        $updatedEmployee = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "=== VÉRIFICATION ===\n";
        echo "Permissions mises à jour: " . $updatedEmployee['access_rights'] . "\n\n";

        echo "🎯 Maintenant vous devriez pouvoir ajouter des livres!\n";
        echo "Testez: http://localhost/library_system/modules/items/add_book.php?lang=ar\n";

    } else {
        echo "❌ Erreur lors de la mise à jour\n";
    }

} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

echo "\n=== FORCE PERMISSIONS TERMINÉ ===\n";
?> 