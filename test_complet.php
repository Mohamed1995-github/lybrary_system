<?php
/**
 * Test complet : connexion + vérification d'accès
 * Complete test: login + access verification
 */

require_once 'includes/auth.php';
require_once 'includes/permissions.php';

echo "=== TEST COMPLET D'ACCÈS ===\n\n";

// 1. Vérifier si déjà connecté
if (!isset($_SESSION['uid'])) {
    echo "❌ Pas connecté - Connexion automatique...\n\n";
    
    // Connexion automatique
    try {
        // Rechercher un employé admin
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE function LIKE '%Directeur%' OR function LIKE '%مدير%' LIMIT 1");
        $stmt->execute();
        $adminEmployee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($adminEmployee) {
            // Créer la session complète
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
        echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
        exit;
    }
} else {
    echo "✅ Déjà connecté\n";
    echo "ID: " . $_SESSION['uid'] . "\n";
    echo "Nom: " . ($_SESSION['name'] ?? $_SESSION['employee_name'] ?? 'Non défini') . "\n\n";
}

// 2. Vérifier le niveau d'accès
$accessLevel = getEmployeeAccessLevel();
echo "Niveau d'accès: {$accessLevel}\n";

// 3. Tester les permissions
$permissions = ['إدارة الكتب', 'Gestion des livres'];
$hasAny = hasAnyPermission($permissions);
echo "A des permissions: " . ($hasAny ? "✅ Oui" : "❌ Non") . "\n";

// 4. Tester les fonctions de contrôle
echo "canView(): " . (canView() ? "✅ Oui" : "❌ Non") . "\n";
echo "canAdd(): " . (canAdd() ? "✅ Oui" : "❌ Non") . "\n";
echo "canEdit(): " . (canEdit() ? "✅ Oui" : "❌ Non") . "\n";
echo "canDelete(): " . (canDelete() ? "✅ Oui" : "❌ Non") . "\n";

// 5. Test d'accès aux items
echo "\n=== TEST D'ACCÈS AUX ITEMS ===\n";
$itemTypes = ['book', 'magazine', 'newspaper'];

foreach ($itemTypes as $type) {
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
    echo "{$type}: " . ($hasAccess ? "✅ Accès" : "❌ Pas d'accès") . "\n";
}

echo "\n=== RÉSULTAT ===\n";
if ($accessLevel === 'admin') {
    echo "✅ Vous êtes admin - accès complet\n";
} elseif ($hasAny) {
    echo "✅ Vous avez des permissions - accès partiel\n";
} else {
    echo "❌ Aucune permission - pas d'accès\n";
}

echo "\n=== URLs DE TEST ===\n";
echo "Testez ces URLs dans votre navigateur:\n";
echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
echo "http://localhost/library_system/modules/items/add_book.php?lang=ar\n";

echo "\n=== TEST TERMINÉ ===\n";
?> 