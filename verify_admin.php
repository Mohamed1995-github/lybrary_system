<?php
/**
 * Vérification simple du compte admin
 */

require_once 'includes/auth.php';
require_once 'includes/permissions.php';

echo "=== VÉRIFICATION COMPTE ADMIN ===\n\n";

// Vérifier la session
if (!isset($_SESSION['uid'])) {
    echo "❌ Pas connecté\n";
    echo "🔧 Exécutez: php create_admin_account.php\n";
    exit;
}

echo "✅ Connecté - ID: " . $_SESSION['uid'] . "\n";

// Vérifier le niveau d'accès
$accessLevel = getEmployeeAccessLevel();
echo "Niveau d'accès: {$accessLevel}\n";

// Vérifier les permissions d'ajout
$canAdd = canAdd();
$canView = canView();
$canEdit = canEdit();
$canDelete = canDelete();

echo "\n=== PERMISSIONS ===\n";
echo "canAdd(): " . ($canAdd ? "✅ Oui" : "❌ Non") . "\n";
echo "canView(): " . ($canView ? "✅ Oui" : "❌ Non") . "\n";
echo "canEdit(): " . ($canEdit ? "✅ Oui" : "❌ Non") . "\n";
echo "canDelete(): " . ($canDelete ? "✅ Oui" : "❌ Non") . "\n";

// Vérifier les permissions spécifiques pour les livres
$hasBookPermission = hasPermission('إدارة الكتب') || hasPermission('Gestion des livres');
echo "Permission livres: " . ($hasBookPermission ? "✅ Oui" : "❌ Non") . "\n";

echo "\n=== RÉSULTAT ===\n";
if ($accessLevel === 'admin' && $canAdd && $hasBookPermission) {
    echo "🎉 COMPTE ADMIN PARFAIT!\n";
    echo "✅ Vous pouvez ajouter des livres\n";
    echo "URL: http://localhost/library_system/modules/items/add_book.php?lang=ar\n";
} else {
    echo "❌ Problème détecté\n";
    echo "🔧 Vérifiez les permissions\n";
}

echo "\n=== FIN ===\n";
?> 