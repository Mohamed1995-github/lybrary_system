<?php
/**
 * Script pour vérifier les informations de l'utilisateur connecté
 */

require_once 'includes/auth.php';

echo "=== Informations de l'utilisateur ===\n\n";

if (isset($_SESSION['uid'])) {
    echo "✅ Utilisateur connecté\n";
    echo "ID: " . $_SESSION['uid'] . "\n";
    echo "Rôle: " . ($_SESSION['role'] ?? 'Non défini') . "\n";
    echo "Nom: " . ($_SESSION['name'] ?? 'Non défini') . "\n";
    echo "Email: " . ($_SESSION['email'] ?? 'Non défini') . "\n";
    
    echo "\n=== Permissions disponibles ===\n";
    $role = $_SESSION['role'] ?? 'guest';
    
    switch ($role) {
        case 'admin':
            echo "🔑 Admin - Accès complet à tous les modules\n";
            break;
        case 'librarian':
            echo "📚 Librarian - Accès à la plupart des modules (sauf administration)\n";
            break;
        case 'assistant':
            echo "👥 Assistant - Accès en lecture seule à certains modules\n";
            break;
        default:
            echo "❓ Rôle non reconnu: $role\n";
    }
    
} else {
    echo "❌ Aucun utilisateur connecté\n";
    echo "Veuillez vous connecter d'abord.\n";
}

echo "\n=== Test d'accès aux modules ===\n";
echo "Pour tester, essayez d'accéder à:\n";
echo "- http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=magazine\n";
echo "- http://localhost/library_system/public/router.php?module=items&action=add_edit&lang=ar&type=magazine\n";
?> 