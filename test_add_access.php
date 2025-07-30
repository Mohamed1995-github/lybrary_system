<?php
/**
 * Test spécifique pour l'accès à l'ajout de livres
 * Specific test for book addition access
 */

require_once 'includes/auth.php';
require_once 'includes/permissions.php';

echo "=== TEST D'ACCÈS À L'AJOUT DE LIVRES ===\n\n";

// 1. Vérifier la connexion
if (!isset($_SESSION['uid'])) {
    echo "❌ Pas connecté - Connexion automatique...\n\n";
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE function LIKE '%Directeur%' OR function LIKE '%مدير%' LIMIT 1");
        $stmt->execute();
        $adminEmployee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($adminEmployee) {
            $_SESSION['uid'] = $adminEmployee['id'];
            $_SESSION['name'] = $adminEmployee['name'];
            $_SESSION['email'] = $adminEmployee['email'] ?: 'admin@library.com';
            $_SESSION['role'] = 'admin';
            $_SESSION['employee_name'] = $adminEmployee['name'];
            $_SESSION['employee_function'] = $adminEmployee['function'];
            $_SESSION['employee_rights'] = $adminEmployee['access_rights'];
            $_SESSION['lang'] = 'ar';

            echo "✅ Connexion réussie!\n";
            echo "ID: " . $_SESSION['uid'] . "\n";
            echo "Nom: " . $_SESSION['name'] . "\n";
            echo "Fonction: " . $_SESSION['employee_function'] . "\n\n";
        } else {
            echo "❌ Aucun admin trouvé\n";
            exit;
        }
    } catch (PDOException $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit;
    }
} else {
    echo "✅ Déjà connecté\n";
    echo "ID: " . $_SESSION['uid'] . "\n";
    echo "Nom: " . ($_SESSION['name'] ?? $_SESSION['employee_name'] ?? 'Non défini') . "\n\n";
}

// 2. Vérifier les permissions spécifiques pour l'ajout
echo "=== VÉRIFICATION DES PERMISSIONS D'AJOUT ===\n";
$requiredPermissions = ['إدارة الكتب', 'Gestion des livres'];
$accessLevel = getEmployeeAccessLevel();

echo "Niveau d'accès: {$accessLevel}\n";
echo "Permissions requises: " . implode(', ', $requiredPermissions) . "\n";

foreach ($requiredPermissions as $permission) {
    $hasPermission = hasPermission($permission);
    echo "hasPermission('{$permission}'): " . ($hasPermission ? "✅ Oui" : "❌ Non") . "\n";
}

$hasAnyPermission = hasAnyPermission($requiredPermissions);
echo "hasAnyPermission(): " . ($hasAnyPermission ? "✅ Oui" : "❌ Non") . "\n";

// 3. Vérifier les fonctions de contrôle
echo "\n=== FONCTIONS DE CONTRÔLE ===\n";
echo "canAdd(): " . (canAdd() ? "✅ Oui" : "❌ Non") . "\n";
echo "canView(): " . (canView() ? "✅ Oui" : "❌ Non") . "\n";
echo "canEdit(): " . (canEdit() ? "✅ Oui" : "❌ Non") . "\n";
echo "canDelete(): " . (canDelete() ? "✅ Oui" : "❌ Non") . "\n";

// 4. Test d'accès final
echo "\n=== TEST D'ACCÈS FINAL ===\n";
$hasAccess = hasAnyPermission($requiredPermissions) || $accessLevel === 'admin';
echo "Accès à l'ajout de livres: " . ($hasAccess ? "✅ AUTORISÉ" : "❌ REFUSÉ") . "\n";

if ($hasAccess) {
    echo "✅ Vous pouvez ajouter des livres!\n";
    echo "URL: http://localhost/library_system/modules/items/add_book.php?lang=ar\n";
} else {
    echo "❌ Vous ne pouvez pas ajouter de livres\n";
    echo "Raison: ";
    if ($accessLevel !== 'admin') {
        echo "Niveau d'accès '{$accessLevel}' n'est pas admin\n";
    } else {
        echo "Aucune permission trouvée parmi: " . implode(', ', $requiredPermissions) . "\n";
    }
}

// 5. Vérifier les droits dans la base de données
echo "\n=== VÉRIFICATION BASE DE DONNÉES ===\n";
try {
    $stmt = $pdo->prepare("SELECT access_rights FROM employees WHERE id = ?");
    $stmt->execute([$_SESSION['uid']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee) {
        echo "Droits d'accès dans la DB: " . $employee['access_rights'] . "\n";
        
        $rights = explode(',', $employee['access_rights']);
        $rights = array_map('trim', $rights);
        
        echo "Permissions trouvées:\n";
        foreach ($rights as $right) {
            if (!empty($right)) {
                echo "  - {$right}\n";
            }
        }
    }
} catch (PDOException $e) {
    echo "❌ Erreur DB: " . $e->getMessage() . "\n";
}

echo "\n=== TEST TERMINÉ ===\n";
?> 