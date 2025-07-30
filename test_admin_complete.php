<?php
/**
 * Test complet du compte administrateur
 * Complete admin account test
 */

require_once 'includes/auth.php';
require_once 'includes/permissions.php';

echo "=== TEST COMPLET DU COMPTE ADMINISTRATEUR ===\n\n";

// 1. Vérifier la connexion
if (!isset($_SESSION['uid'])) {
    echo "❌ Pas connecté - Connexion automatique...\n\n";
    
    try {
        // Rechercher l'admin système
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE function = 'مدير النظام' OR number = 'ADMIN001' LIMIT 1");
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
            echo "❌ Aucun admin système trouvé\n";
            echo "🔧 Exécutez: php create_admin_account.php\n";
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

// 2. Vérifier le niveau d'accès
$accessLevel = getEmployeeAccessLevel();
echo "=== NIVEAU D'ACCÈS ===\n";
echo "Niveau détecté: {$accessLevel}\n";
echo "Status: " . ($accessLevel === 'admin' ? "✅ Admin" : "❌ Pas admin") . "\n\n";

// 3. Vérifier toutes les permissions
echo "=== VÉRIFICATION DES PERMISSIONS ===\n";
$allPermissions = [
    'إدارة الكتب',
    'Gestion des livres',
    'عرض الكتب',
    'Consultation des livres',
    'إدارة المجلات',
    'Gestion des magazines',
    'عرض المجلات',
    'Consultation des revues',
    'إدارة الصحف',
    'Gestion des journaux',
    'عرض الصحف',
    'Consultation des journaux',
    'إدارة المستعيرين',
    'Gestion des emprunteurs',
    'إدارة الإعارة',
    'Gestion des prêts',
    'إدارة الموظفين',
    'Gestion des employés',
    'إدارة النظام',
    'Administration du système'
];

$permissionsStatus = [];
foreach ($allPermissions as $permission) {
    $hasPermission = hasPermission($permission);
    $permissionsStatus[$permission] = $hasPermission;
    echo "{$permission}: " . ($hasPermission ? "✅" : "❌") . "\n";
}

$totalPermissions = count($allPermissions);
$grantedPermissions = count(array_filter($permissionsStatus));
echo "\nRésumé: {$grantedPermissions}/{$totalPermissions} permissions accordées\n\n";

// 4. Vérifier les fonctions de contrôle
echo "=== FONCTIONS DE CONTRÔLE ===\n";
echo "canView(): " . (canView() ? "✅ Oui" : "❌ Non") . "\n";
echo "canAdd(): " . (canAdd() ? "✅ Oui" : "❌ Non") . "\n";
echo "canEdit(): " . (canEdit() ? "✅ Oui" : "❌ Non") . "\n";
echo "canDelete(): " . (canDelete() ? "✅ Oui" : "❌ Non") . "\n\n";

// 5. Test d'accès aux modules
echo "=== TEST D'ACCÈS AUX MODULES ===\n";
$modules = [
    'books' => ['إدارة الكتب', 'Gestion des livres', 'عرض الكتب', 'Consultation des livres'],
    'magazines' => ['إدارة المجلات', 'Gestion des magazines', 'عرض المجلات', 'Consultation des revues'],
    'newspapers' => ['إدارة الصحف', 'Gestion des journaux', 'عرض الصحف', 'Consultation des journaux'],
    'borrowers' => ['إدارة المستعيرين', 'Gestion des emprunteurs'],
    'loans' => ['إدارة الإعارة', 'Gestion des prêts'],
    'employees' => ['إدارة الموظفين', 'Gestion des employés'],
    'system' => ['إدارة النظام', 'Administration du système']
];

foreach ($modules as $module => $requiredPermissions) {
    $hasAccess = hasAnyPermission($requiredPermissions) || $accessLevel === 'admin';
    echo "{$module}: " . ($hasAccess ? "✅ Accès" : "❌ Pas d'accès") . "\n";
}

echo "\n=== RÉSULTAT FINAL ===\n";
if ($accessLevel === 'admin' && $grantedPermissions >= 15) {
    echo "🎉 COMPTE ADMINISTRATEUR PARFAIT!\n";
    echo "✅ Niveau d'accès: Admin\n";
    echo "✅ Permissions: {$grantedPermissions}/{$totalPermissions}\n";
    echo "✅ Toutes les fonctionnalités disponibles\n\n";
    
    echo "🎯 Vous pouvez maintenant:\n";
    echo "• Ajouter, modifier, supprimer des livres\n";
    echo "• Gérer tous les types de documents\n";
    echo "• Gérer les emprunteurs et les prêts\n";
    echo "• Administrer le système complet\n\n";
    
    echo "📋 URLs de test:\n";
    echo "1. http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
    echo "2. http://localhost/library_system/modules/items/add_book.php?lang=ar\n";
    echo "3. http://localhost/library_system/public/dashboard.php?lang=ar\n";
    
} elseif ($accessLevel === 'admin') {
    echo "⚠️ COMPTE ADMIN AVEC PERMISSIONS PARTIELLES\n";
    echo "✅ Niveau d'accès: Admin\n";
    echo "⚠️ Permissions: {$grantedPermissions}/{$totalPermissions}\n";
    echo "🔧 Exécutez: php create_admin_account.php pour compléter les permissions\n";
    
} else {
    echo "❌ COMPTE NON ADMIN\n";
    echo "❌ Niveau d'accès: {$accessLevel}\n";
    echo "🔧 Exécutez: php create_admin_account.php pour créer un admin complet\n";
}

echo "\n=== TEST TERMINÉ ===\n";
?> 