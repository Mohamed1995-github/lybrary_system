<?php
/**
 * Script pour corriger l'accès admin
 * Script to fix admin access
 */

require_once 'includes/auth.php';

echo "=== Correction de l'Accès Admin ===\n\n";

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) {
    echo "❌ Aucun utilisateur connecté\n";
    echo "Veuillez vous connecter d'abord.\n";
    exit;
}

echo "✅ Utilisateur connecté\n";
echo "ID: " . $_SESSION['uid'] . "\n";
echo "Nom: " . ($_SESSION['name'] ?? 'Non défini') . "\n\n";

try {
    // Vérifier l'employé actuel
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$_SESSION['uid']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo "❌ Employé non trouvé dans la base de données\n";
        exit;
    }
    
    echo "=== Informations Actuelles ===\n";
    echo "Nom: " . $employee['name'] . "\n";
    echo "Fonction actuelle: " . $employee['function'] . "\n";
    echo "Droits d'accès actuels: " . $employee['access_rights'] . "\n\n";
    
    // Définir les permissions admin complètes
    $adminPermissions = [
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
        'Gestion des employés'
    ];
    
    $adminPermissionsString = implode(',', $adminPermissions);
    
    // Mettre à jour la fonction et les permissions
    $updateStmt = $pdo->prepare("UPDATE employees SET function = ?, access_rights = ? WHERE id = ?");
    $result = $updateStmt->execute(['مدير', $adminPermissionsString, $_SESSION['uid']]);
    
    if ($result) {
        echo "✅ Mise à jour réussie!\n";
        echo "Nouvelle fonction: مدير (Admin)\n";
        echo "Nouvelles permissions: Toutes les permissions admin\n\n";
        
        // Vérifier la mise à jour
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);
        $updatedEmployee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "=== Vérification ===\n";
        echo "Fonction mise à jour: " . $updatedEmployee['function'] . "\n";
        echo "Permissions mises à jour: " . $updatedEmployee['access_rights'] . "\n\n";
        
        echo "🎯 Maintenant vous devriez avoir accès à toutes les fonctionnalités!\n";
        echo "Testez ces URLs:\n";
        echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
        echo "http://localhost/library_system/modules/items/add_book.php?lang=ar\n";
        
    } else {
        echo "❌ Erreur lors de la mise à jour\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

echo "\n=== Correction terminée ===\n";
?> 