<?php
/**
 * Script de débogage pour diagnostiquer le problème d'accès admin
 * Debug script to diagnose admin access issues
 */

require_once 'includes/auth.php';
require_once 'includes/permissions.php';

echo "=== Diagnostic du Problème d'Accès Admin ===\n\n";

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) {
    echo "❌ Aucun utilisateur connecté\n";
    echo "Veuillez vous connecter d'abord avec: php auto_login_admin.php\n";
    exit;
}

echo "✅ Utilisateur connecté\n";
echo "ID: " . $_SESSION['uid'] . "\n";
echo "Nom: " . ($_SESSION['name'] ?? $_SESSION['employee_name'] ?? 'Non défini') . "\n";
echo "Email: " . ($_SESSION['email'] ?? 'Non défini') . "\n";
echo "Fonction: " . ($_SESSION['employee_function'] ?? 'Non défini') . "\n";
echo "Droits: " . ($_SESSION['employee_rights'] ?? 'Non défini') . "\n";
echo "Langue: " . ($_SESSION['lang'] ?? 'Non défini') . "\n\n";

// Obtenir les informations d'accès
$accessLevel = getEmployeeAccessLevel();
$userRights = getCurrentEmployeeRights();

echo "=== Informations d'Accès ===\n";
echo "Niveau d'accès détecté: {$accessLevel}\n";
echo "Permissions: " . implode(', ', $userRights) . "\n\n";

// Vérifier les données de l'employé dans la base de données
echo "=== Vérification Base de Données ===\n";

try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$_SESSION['uid']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        echo "✅ Employé trouvé dans la base de données\n";
        echo "ID: " . $employee['id'] . "\n";
        echo "Nom: " . $employee['name'] . "\n";
        echo "Fonction: " . $employee['function'] . "\n";
        echo "Droits d'accès: " . $employee['access_rights'] . "\n";
        echo "Email: " . $employee['email'] . "\n";
        echo "Statut: " . $employee['status'] . "\n";
        echo "Langue: " . ($employee['lang'] ?? 'Non défini') . "\n\n";

        // Analyser la fonction pour détecter le niveau d'accès
        $function = $employee['function'];
        echo "=== Analyse de la Fonction ===\n";
        echo "Fonction brute: '{$function}'\n";

        if (strpos($function, 'مدير') !== false) {
            echo "✅ Contient 'مدير' - devrait être admin\n";
        } elseif (strpos($function, 'Directeur') !== false) {
            echo "✅ Contient 'Directeur' - devrait être admin\n";
        } elseif (strpos($function, 'أمين') !== false) {
            echo "ℹ️ Contient 'أمين' - devrait être librarian\n";
        } elseif (strpos($function, 'Bibliothécaire') !== false) {
            echo "ℹ️ Contient 'Bibliothécaire' - devrait être librarian\n";
        } elseif (strpos($function, 'مساعد') !== false) {
            echo "ℹ️ Contient 'مساعد' - devrait être assistant\n";
        } elseif (strpos($function, 'Assistant') !== false) {
            echo "ℹ️ Contient 'Assistant' - devrait être assistant\n";
        } else {
            echo "❓ Fonction non reconnue: '{$function}'\n";
        }

    } else {
        echo "❌ Employé non trouvé dans la base de données\n";
        echo "Vérifiez que vous utilisez le bon système de connexion (employees vs users)\n";
    }

} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

echo "\n=== Test des Fonctions de Permissions ===\n";

// Tester les fonctions de permissions
echo "hasPermission('إدارة الكتب'): " . (hasPermission('إدارة الكتب') ? "✅ Oui" : "❌ Non") . "\n";
echo "hasPermission('Gestion des livres'): " . (hasPermission('Gestion des livres') ? "✅ Oui" : "❌ Non") . "\n";
echo "hasAnyPermission(['إدارة الكتب', 'Gestion des livres']): " . (hasAnyPermission(['إدارة الكتب', 'Gestion des livres']) ? "✅ Oui" : "❌ Non") . "\n";

echo "\n=== Test des Fonctions de Contrôle ===\n";
echo "canView(): " . (canView() ? "✅ Oui" : "❌ Non") . "\n";
echo "canAdd(): " . (canAdd() ? "✅ Oui" : "❌ Non") . "\n";
echo "canEdit(): " . (canEdit() ? "✅ Oui" : "❌ Non") . "\n";
echo "canDelete(): " . (canDelete() ? "✅ Oui" : "❌ Non") . "\n";

echo "\n=== Test d'Accès aux Items ===\n";

$itemTypes = ['book', 'magazine', 'newspaper'];

foreach ($itemTypes as $type) {
    echo "\n--- Type: {$type} ---\n";

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
}

echo "\n=== Vérification de la Session ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Save Path: " . session_save_path() . "\n";

echo "\n=== Variables de Session ===\n";
foreach ($_SESSION as $key => $value) {
    echo "{$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n";
}

echo "\n=== Recommandations ===\n";

if ($accessLevel !== 'admin') {
    echo "🔧 Pour corriger le problème:\n";
    echo "1. Vérifiez que la fonction de l'employé contient 'مدير' ou 'Directeur'\n";
    echo "2. Ou modifiez la fonction getEmployeeAccessLevel() pour reconnaître votre fonction\n";
    echo "3. Ou ajoutez manuellement les permissions nécessaires\n";
    echo "4. Ou utilisez: php fix_admin_access.php\n";
} else {
    echo "✅ Le niveau d'accès est correctement détecté comme 'admin'\n";
    echo "🔧 Si vous avez encore des problèmes, vérifiez:\n";
    echo "1. Les permissions dans la base de données\n";
    echo "2. La logique de vérification dans les modules\n";
    echo "3. Les variables de session\n";
}

echo "\n=== URLs de Test ===\n";
echo "Testez ces URLs pour vérifier l'accès:\n";
echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
echo "http://localhost/library_system/modules/items/add_book.php?lang=ar\n";

echo "\n=== Diagnostic terminé ===\n";
?> 