<?php
/**
 * Test rapide d'accès aux modules
 * Quick access test for modules
 */

require_once 'includes/auth.php';
require_once 'includes/permissions.php';

echo "=== TEST RAPIDE D'ACCÈS ===\n\n";

// 1. Vérifier la session
if (!isset($_SESSION['uid'])) {
    echo "❌ Pas connecté\n";
    echo "🔧 Exécutez: php auto_login_admin.php\n";
    exit;
}

echo "✅ Connecté - ID: " . $_SESSION['uid'] . "\n";

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
    echo "🔧 Exécutez: php force_admin_access.php\n";
}

echo "\n=== URLs DE TEST ===\n";
echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
echo "http://localhost/library_system/modules/items/add_book.php?lang=ar\n";
?> 