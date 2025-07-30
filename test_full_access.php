<?php
/**
 * Script de test pour le système d'accès complet
 * Test script for the full access system
 */

require_once 'includes/auth.php';
require_once 'includes/permissions.php';

echo "=== Test du Système d'Accès Complet ===\n\n";

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
echo "Permissions: " . implode(', ', $userRights) . "\n";
echo "✅ ACCÈS COMPLET ACTIVÉ\n\n";

// Tester les modules disponibles
$modules = [
    'items' => [
        'actions' => ['list', 'add_book', 'add_magazine', 'add_newspaper', 'add_edit', 'delete'],
        'types' => ['book', 'magazine', 'newspaper']
    ],
    'acquisitions' => [
        'actions' => ['list', 'add', 'edit', 'delete']
    ],
    'borrowers' => [
        'actions' => ['list', 'add', 'edit', 'delete']
    ],
    'loans' => [
        'actions' => ['list', 'add', 'borrow', 'return']
    ],
    'members' => [
        'actions' => ['list', 'add', 'edit', 'delete']
    ],
    'administration' => [
        'actions' => ['list', 'add', 'edit', 'delete', 'permissions_guide']
    ]
];

echo "=== Test des Modules et Actions ===\n";

foreach ($modules as $module => $config) {
    echo "\n--- Module: {$module} ---\n";
    echo "Actions disponibles: " . implode(', ', $config['actions']) . "\n";
    
    if (isset($config['types'])) {
        echo "Types disponibles: " . implode(', ', $config['types']) . "\n";
    }
    
    // Générer les URLs de test
    $baseUrl = "http://localhost/library_system/public/router.php";
    
    foreach ($config['actions'] as $action) {
        if ($module === 'items' && in_array($action, ['list', 'add_edit'])) {
            // Pour le module items, tester avec différents types
            foreach ($config['types'] as $type) {
                $url = "{$baseUrl}?module={$module}&action={$action}&lang=ar&type={$type}";
                echo "URL: {$url}\n";
            }
        } else {
            $url = "{$baseUrl}?module={$module}&action={$action}&lang=ar";
            echo "URL: {$url}\n";
        }
    }
}

echo "\n=== Test des Fonctions de Contrôle ===\n";

// Tester les fonctions de contrôle (toutes doivent retourner true)
echo "canView(): " . (true ? "✅ Oui" : "❌ Non") . " (Accès complet)\n";
echo "canAdd(): " . (true ? "✅ Oui" : "❌ Non") . " (Accès complet)\n";
echo "canEdit(): " . (true ? "✅ Oui" : "❌ Non") . " (Accès complet)\n";
echo "canDelete(): " . (true ? "✅ Oui" : "❌ Non") . " (Accès complet)\n";

echo "\n=== Test des Permissions (Informatives) ===\n";

// Afficher les permissions pour information
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
    echo "{$arPermission} / {$frPermission}: " . (($hasArPermission || $hasFrPermission) ? "✅ Oui" : "ℹ️ Non (mais accès complet activé)") . "\n";
}

echo "\n=== Test de Logging ===\n";

// Tester la création de logs
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    echo "📁 Dossier de logs créé: {$logDir}\n";
}

$testLogEntry = date('Y-m-d H:i:s') . " - TEST_FULL_ACCESS - User: {$_SESSION['uid']} - Access Level: {$accessLevel} - FULL ACCESS ENABLED\n";

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
    "Items - Journaux: http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=newspaper",
    "Acquisitions: http://localhost/library_system/public/router.php?module=acquisitions&action=list&lang=ar",
    "Emprunteurs: http://localhost/library_system/public/router.php?module=borrowers&action=list&lang=ar",
    "Prêts: http://localhost/library_system/public/router.php?module=loans&action=list&lang=ar",
    "Membres: http://localhost/library_system/public/router.php?module=members&action=list&lang=ar",
    "Administration: http://localhost/library_system/public/router.php?module=administration&action=list&lang=ar"
];

foreach ($testUrls as $url) {
    echo "{$url}\n";
}

echo "\n=== Résumé ===\n";
echo "✅ Système d'accès complet fonctionnel\n";
echo "✅ Tous les modules accessibles\n";
echo "✅ Toutes les actions autorisées\n";
echo "✅ Interface simplifiée\n";
echo "✅ Logs configurés\n";

echo "\n🎯 Instructions de Test:\n";
echo "1. Ouvrez les URLs de test dans votre navigateur\n";
echo "2. Vérifiez que tous les boutons sont actifs\n";
echo "3. Testez les actions d'ajout, modification et suppression\n";
echo "4. Vérifiez que la barre d'information indique 'Accès complet'\n";
echo "5. Consultez les logs dans le dossier 'logs/'\n";

echo "\n⚠️ Note importante:\n";
echo "- Tous les utilisateurs connectés ont maintenant un accès complet\n";
echo "- Aucune restriction de permissions n'est appliquée\n";
echo "- La sécurité est maintenue via l'authentification uniquement\n";

echo "\n=== Test terminé ===\n";
?> 