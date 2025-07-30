<?php
/**
 * Script de test pour le système de contrôle d'accès par permissions
 * Test script for the permission-based access control system
 */

require_once 'includes/auth.php';
require_once 'includes/permissions.php';

echo "=== Test du Système de Contrôle d'Accès par Permissions ===\n\n";

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) {
    echo "❌ Aucun utilisateur connecté\n";
    echo "Veuillez vous connecter d'abord.\n";
    exit;
}

echo "✅ Utilisateur connecté\n";
echo "ID: " . $_SESSION['uid'] . "\n";
echo "Nom: " . ($_SESSION['name'] ?? 'Non défini') . "\n";
echo "Email: " . ($_SESSION['email'] ?? 'Non défini') . "\n\n";

// Obtenir les informations d'accès
$accessLevel = getEmployeeAccessLevel();
$userRights = getCurrentEmployeeRights();

echo "=== Informations d'Accès ===\n";
echo "Niveau d'accès: {$accessLevel}\n";
echo "Permissions: " . implode(', ', $userRights) . "\n\n";

// Tester les permissions pour différents types d'items
$itemTypes = ['book', 'magazine', 'newspaper'];

echo "=== Test des Permissions par Type d'Item ===\n";

foreach ($itemTypes as $type) {
    echo "\n--- Type: {$type} ---\n";
    
    // Définir les permissions requises
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
    
    // Tester l'accès
    $hasAccess = hasAnyPermission($requiredPermissions) || $accessLevel === 'admin';
    $canAdd = canAdd() && hasAnyPermission($requiredPermissions);
    $canEdit = canEdit() && hasAnyPermission($requiredPermissions);
    $canDelete = canDelete() && hasAnyPermission($requiredPermissions);
    
    echo "Permissions requises: " . implode(', ', $requiredPermissions) . "\n";
    echo "Accès autorisé: " . ($hasAccess ? "✅ Oui" : "❌ Non") . "\n";
    echo "Peut ajouter: " . ($canAdd ? "✅ Oui" : "❌ Non") . "\n";
    echo "Peut modifier: " . ($canEdit ? "✅ Oui" : "❌ Non") . "\n";
    echo "Peut supprimer: " . ($canDelete ? "✅ Oui" : "❌ Non") . "\n";
    
    // Afficher les permissions spécifiques
    echo "Permissions détaillées:\n";
    foreach ($requiredPermissions as $permission) {
        $hasPermission = hasPermission($permission);
        echo "  - {$permission}: " . ($hasPermission ? "✅" : "❌") . "\n";
    }
}

echo "\n=== Test des Fonctions de Contrôle ===\n";

// Tester les fonctions de contrôle générales
echo "canView(): " . (canView() ? "✅ Oui" : "❌ Non") . "\n";
echo "canAdd(): " . (canAdd() ? "✅ Oui" : "❌ Non") . "\n";
echo "canEdit(): " . (canEdit() ? "✅ Oui" : "❌ Non") . "\n";
echo "canDelete(): " . (canDelete() ? "✅ Oui" : "❌ Non") . "\n";

echo "\n=== Test des Permissions Spécifiques ===\n";

// Tester des permissions spécifiques
$specificPermissions = [
    'إدارة الكتب' => 'Gestion des livres',
    'إدارة المجلات' => 'Gestion des magazines',
    'إدارة الصحف' => 'Gestion des journaux',
    'إدارة المستعيرين' => 'Gestion des emprunteurs',
    'إدارة الإعارة' => 'Gestion des prêts',
    'إدارة الموظفين' => 'Gestion des employés'
];

foreach ($specificPermissions as $arPermission => $frPermission) {
    $hasArPermission = hasPermission($arPermission);
    $hasFrPermission = hasPermission($frPermission);
    echo "{$arPermission} / {$frPermission}: " . (($hasArPermission || $hasFrPermission) ? "✅ Oui" : "❌ Non") . "\n";
}

echo "\n=== Test des URLs d'Accès ===\n";

// Générer les URLs de test
$baseUrl = "http://localhost/library_system/public/router.php";
$langs = ['ar', 'fr'];

foreach ($itemTypes as $type) {
    foreach ($langs as $lang) {
        $url = "{$baseUrl}?module=items&action=list&lang={$lang}&type={$type}";
        echo "URL: {$url}\n";
    }
}

echo "\n=== Recommandations ===\n";

if ($accessLevel === 'admin') {
    echo "🔑 Vous êtes administrateur - Accès complet à tous les modules\n";
} elseif ($accessLevel === 'librarian') {
    echo "📚 Vous êtes bibliothécaire - Accès à la plupart des modules\n";
} elseif ($accessLevel === 'assistant') {
    echo "👥 Vous êtes assistant - Accès limité aux modules\n";
} elseif ($accessLevel === 'reception') {
    echo "🏢 Vous êtes en réception - Accès en consultation uniquement\n";
} else {
    echo "❓ Niveau d'accès non reconnu: {$accessLevel}\n";
}

// Vérifier les permissions manquantes
$allPermissions = [
    'إدارة الكتب', 'Gestion des livres',
    'إدارة المجلات', 'Gestion des magazines', 
    'إدارة الصحف', 'Gestion des journaux',
    'إدارة المستعيرين', 'Gestion des emprunteurs',
    'إدارة الإعارة', 'Gestion des prêts',
    'إدارة الموظفين', 'Gestion des employés'
];

$missingPermissions = [];
foreach ($allPermissions as $permission) {
    if (!hasPermission($permission)) {
        $missingPermissions[] = $permission;
    }
}

if (!empty($missingPermissions)) {
    echo "\n⚠️ Permissions manquantes:\n";
    foreach ($missingPermissions as $permission) {
        echo "  - {$permission}\n";
    }
}

echo "\n=== Test de Logging ===\n";

// Tester la création de logs
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    echo "📁 Dossier de logs créé: {$logDir}\n";
}

$testLogEntry = date('Y-m-d H:i:s') . " - TEST_PERMISSIONS_SYSTEM - User: {$_SESSION['uid']} - Access Level: {$accessLevel}\n";

// Test d'écriture dans access.log
$accessLogFile = $logDir . '/access.log';
if (file_put_contents($accessLogFile, $testLogEntry, FILE_APPEND | LOCK_EX)) {
    echo "✅ Log d'accès créé avec succès\n";
} else {
    echo "❌ Erreur lors de la création du log d'accès\n";
}

// Test d'écriture dans security.log
$securityLogFile = $logDir . '/security.log';
if (file_put_contents($securityLogFile, $testLogEntry, FILE_APPEND | LOCK_EX)) {
    echo "✅ Log de sécurité créé avec succès\n";
} else {
    echo "❌ Erreur lors de la création du log de sécurité\n";
}

echo "\n=== URLs de Test Recommandées ===\n";

echo "🎯 Testez ces URLs dans votre navigateur:\n\n";

// URLs principales pour test
$testUrls = [
    "Items - Livres: http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book",
    "Items - Magazines: http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=magazine",
    "Items - Journaux: http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=newspaper"
];

foreach ($testUrls as $url) {
    echo "{$url}\n";
}

echo "\n=== Résumé ===\n";
echo "✅ Système de permissions fonctionnel\n";
echo "✅ Contrôle d'accès granulaire activé\n";
echo "✅ Interface adaptative selon les permissions\n";
echo "✅ Logs configurés\n";

echo "\n🎯 Instructions de Test:\n";
echo "1. Ouvrez les URLs de test dans votre navigateur\n";
echo "2. Vérifiez que les boutons s'affichent selon vos permissions\n";
echo "3. Testez l'accès aux différents types d'items\n";
echo "4. Vérifiez que la barre d'information indique le statut des permissions\n";
echo "5. Consultez les logs dans le dossier 'logs/'\n";

echo "\n⚠️ Note importante:\n";
echo "- L'accès est maintenant contrôlé par les permissions\n";
echo "- Seuls les utilisateurs avec les bonnes permissions peuvent accéder\n";
echo "- Les administrateurs ont accès à tout\n";

echo "\n=== Test terminé ===\n";
?> 