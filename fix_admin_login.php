<?php
/**
 * Script pour vérifier et corriger le compte admin
 */

require_once 'includes/auth.php';

echo "=== VÉRIFICATION ET CORRECTION DU COMPTE ADMIN ===\n\n";

try {
    // Vérifier si l'admin existe
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE number = 'ADMIN001'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo "❌ Admin ADMIN001 non trouvé\n";
        echo "🔧 Création du compte admin...\n";
        
        // Créer l'admin
        $insertStmt = $pdo->prepare("INSERT INTO employees (name, function, access_rights, lang, number, password) VALUES (?, ?, ?, ?, ?, ?)");
        $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
        $result = $insertStmt->execute([
            'Administrateur Système',
            'مدير النظام',
            'إدارة الكتب,Gestion des livres,عرض الكتب,Consultation des livres,إدارة المجلات,Gestion des magazines,عرض المجلات,Consultation des revues,إدارة الصحف,Gestion des journaux,عرض الصحف,Consultation des journaux,إدارة المستعيرين,Gestion des emprunteurs,إدارة الإعارة,Gestion des prêts,إدارة الموظفين,Gestion des employés,إدارة النظام,Administration du système',
            'ar',
            'ADMIN001',
            $defaultPassword
        ]);
        
        if ($result) {
            echo "✅ Compte admin créé avec succès!\n";
        } else {
            echo "❌ Erreur lors de la création\n";
            exit;
        }
    } else {
        echo "✅ Admin ADMIN001 trouvé\n";
        echo "ID: " . $admin['id'] . "\n";
        echo "Nom: " . $admin['name'] . "\n";
        echo "Fonction: " . $admin['function'] . "\n";
        
        // Vérifier le mot de passe
        if (empty($admin['password'])) {
            echo "⚠️ Mot de passe manquant, mise à jour...\n";
            $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE employees SET password = ? WHERE id = ?");
            $updateStmt->execute([$defaultPassword, $admin['id']]);
            echo "✅ Mot de passe mis à jour\n";
        } else {
            echo "✅ Mot de passe présent\n";
            
            // Tester le mot de passe
            if (password_verify('123456', $admin['password'])) {
                echo "✅ Mot de passe '123456' fonctionne\n";
            } else {
                echo "⚠️ Mot de passe incorrect, mise à jour...\n";
                $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE employees SET password = ? WHERE id = ?");
                $updateStmt->execute([$defaultPassword, $admin['id']]);
                echo "✅ Mot de passe mis à jour\n";
            }
        }
    }

    // Afficher les informations de connexion
    echo "\n=== INFORMATIONS DE CONNEXION ===\n";
    echo "URL: http://localhost/library_system/public/employee_login.php\n";
    echo "Numéro employé: ADMIN001\n";
    echo "Mot de passe: 123456\n";
    
    // Tester la connexion
    echo "\n=== TEST DE CONNEXION ===\n";
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE number = 'ADMIN001'");
    $stmt->execute();
    $testAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testAdmin && password_verify('123456', $testAdmin['password'])) {
        echo "✅ Connexion testée avec succès!\n";
        echo "✅ Vous pouvez maintenant vous connecter\n";
    } else {
        echo "❌ Problème avec la connexion\n";
    }

} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== FIN ===\n";
?> 