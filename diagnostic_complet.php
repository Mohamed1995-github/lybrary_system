<?php
/**
 * Diagnostic complet du système d'accès administrateur
 * Complete diagnostic of admin access system
 */

require_once 'includes/auth.php';
require_once 'includes/permissions.php';

echo "=== DIAGNOSTIC COMPLET DU SYSTÈME D'ACCÈS ===\n\n";

// 1. Vérifier la session
echo "=== 1. VÉRIFICATION DE LA SESSION ===\n";
if (!isset($_SESSION['uid'])) {
    echo "❌ Aucun utilisateur connecté\n";
    echo "🔧 Solution: Exécutez php auto_login_admin.php\n\n";
} else {
    echo "✅ Utilisateur connecté\n";
    echo "ID: " . $_SESSION['uid'] . "\n";
    echo "Nom: " . ($_SESSION['name'] ?? $_SESSION['employee_name'] ?? 'Non défini') . "\n";
    echo "Fonction: " . ($_SESSION['employee_function'] ?? 'Non défini') . "\n";
    echo "Droits: " . ($_SESSION['employee_rights'] ?? 'Non défini') . "\n\n";
}

// 2. Vérifier la base de données
echo "=== 2. VÉRIFICATION BASE DE DONNÉES ===\n";
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$_SESSION['uid'] ?? 0]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        echo "✅ Employé trouvé dans la base de données\n";
        echo "ID: " . $employee['id'] . "\n";
        echo "Nom: " . $employee['name'] . "\n";
        echo "Fonction: " . $employee['function'] . "\n";
        echo "Droits d'accès: " . $employee['access_rights'] . "\n";
        echo "Statut: " . $employee['status'] . "\n\n";
    } else {
        echo "❌ Employé non trouvé dans la base de données\n";
        echo "ID recherché: " . ($_SESSION['uid'] ?? 'Non défini') . "\n\n";
    }
} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n\n";
}

// 3. Vérifier les fonctions de permissions
echo "=== 3. VÉRIFICATION DES FONCTIONS DE PERMISSIONS ===\n";
$accessLevel = getEmployeeAccessLevel();
$userRights = getCurrentEmployeeRights();

echo "Niveau d'accès détecté: {$accessLevel}\n";
echo "Permissions: " . implode(', ', $userRights) . "\n\n";

// 4. Tester les permissions spécifiques
echo "=== 4. TEST DES PERMISSIONS SPÉCIFIQUES ===\n";
$permissions = [
    'إدارة الكتب',
    'Gestion des livres',
    'عرض الكتب',
    'Consultation des livres'
];

foreach ($permissions as $permission) {
    $hasPermission = hasPermission($permission);
    echo "hasPermission('{$permission}'): " . ($hasPermission ? "✅ Oui" : "❌ Non") . "\n";
}

echo "\n";

// 5. Tester les fonctions de contrôle
echo "=== 5. TEST DES FONCTIONS DE CONTRÔLE ===\n";
echo "canView(): " . (canView() ? "✅ Oui" : "❌ Non") . "\n";
echo "canAdd(): " . (canAdd() ? "✅ Oui" : "❌ Non") . "\n";
echo "canEdit(): " . (canEdit() ? "✅ Oui" : "❌ Non") . "\n";
echo "canDelete(): " . (canDelete() ? "✅ Oui" : "❌ Non") . "\n\n";

// 6. Tester l'accès aux items
echo "=== 6. TEST D'ACCÈS AUX ITEMS ===\n";
$itemTypes = ['book', 'magazine', 'newspaper'];

foreach ($itemTypes as $type) {
    echo "--- Type: {$type} ---\n";
    
    $requiredPermissions = [];
    switch ($type) {
        case 'book':
            $requiredPermissions = ['إدارة الكتب', 'Gestion des livres', 'عرض الكتب', 'Consultation des livres'];
            break;
        case 'magazine':
            $requiredPermissions = ['إدارة المجلات', 'Gestion des magazines', 'عرض المجلات', 'Consultation des revues'];
            break;
        case 'newspaper':
            $requiredPermissions = ['إدارة الصحف', 'Gestion des journaux', 'عرض الصحف', 'Consultation des journaux'];
            break;
    }

    $hasAccess = hasAnyPermission($requiredPermissions) || $accessLevel === 'admin';
    echo "Accès autorisé: " . ($hasAccess ? "✅ Oui" : "❌ Non") . "\n";
    
    if (!$hasAccess) {
        echo "Raison: ";
        if ($accessLevel !== 'admin') {
            echo "Niveau d'accès '{$accessLevel}' n'est pas admin\n";
        } else {
            echo "Aucune permission trouvée parmi: " . implode(', ', $requiredPermissions) . "\n";
        }
    }
    echo "\n";
}

// 7. Vérifier les fichiers de modules
echo "=== 7. VÉRIFICATION DES FICHIERS DE MODULES ===\n";
$moduleFiles = [
    'modules/items/list.php',
    'modules/items/add_book.php',
    'public/router.php'
];

foreach ($moduleFiles as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} existe\n";
    } else {
        echo "❌ {$file} n'existe pas\n";
    }
}
echo "\n";

// 8. Vérifier la configuration .htaccess
echo "=== 8. VÉRIFICATION .HTACCESS ===\n";
if (file_exists('.htaccess')) {
    echo "✅ .htaccess existe\n";
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'RewriteEngine') !== false) {
        echo "✅ RewriteEngine activé\n";
    } else {
        echo "❌ RewriteEngine non trouvé\n";
    }
} else {
    echo "❌ .htaccess n'existe pas\n";
}
echo "\n";

// 9. Recommandations
echo "=== 9. RECOMMANDATIONS ===\n";

if (!isset($_SESSION['uid'])) {
    echo "🔧 ÉTAPE 1: Connectez-vous d'abord\n";
    echo "   php auto_login_admin.php\n\n";
} elseif ($accessLevel !== 'admin') {
    echo "🔧 ÉTAPE 2: Corrigez le niveau d'accès\n";
    echo "   php fix_admin_access.php\n\n";
} else {
    echo "✅ Le niveau d'accès est correct\n";
    echo "🔧 Si les problèmes persistent, vérifiez:\n";
    echo "   1. Les permissions dans la base de données\n";
    echo "   2. La logique de vérification dans les modules\n";
    echo "   3. Les variables de session\n\n";
}

echo "=== 10. URLs DE TEST ===\n";
echo "Testez ces URLs après correction:\n";
echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
echo "http://localhost/library_system/modules/items/add_book.php?lang=ar\n";
echo "http://localhost/library_system/public/dashboard.php?lang=ar\n\n";

echo "=== DIAGNOSTIC TERMINÉ ===\n";
?> 