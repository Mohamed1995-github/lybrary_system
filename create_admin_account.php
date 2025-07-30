<?php
/**
 * Script pour créer un compte administrateur complet
 * Script to create a complete admin account
 */

require_once 'includes/auth.php';

echo "=== CRÉATION COMPTE ADMINISTRATEUR COMPLET ===\n\n";

try {
    // Vérifier si un admin existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employees WHERE function LIKE '%مدير%' OR function LIKE '%Directeur%'");
    $stmt->execute();
    $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo "Admins existants: {$adminCount}\n\n";

    // Définir les données du compte admin complet
    $adminData = [
        'name' => 'Administrateur Système',
        'function' => 'مدير النظام',
        'access_rights' => 'إدارة الكتب,Gestion des livres,عرض الكتب,Consultation des livres,إدارة المجلات,Gestion des magazines,عرض المجلات,Consultation des revues,إدارة الصحف,Gestion des journaux,عرض الصحف,Consultation des journaux,إدارة المستعيرين,Gestion des emprunteurs,إدارة الإعارة,Gestion des prêts,إدارة الموظفين,Gestion des employés,إدارة النظام,Administration du système',
        'lang' => 'ar',
        'number' => 'ADMIN001'
    ];

    // Vérifier si l'admin existe déjà
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE number = ?");
    $stmt->execute([$adminData['number']]);
    $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingAdmin) {
        echo "✅ Admin existe déjà:\n";
        echo "ID: " . $existingAdmin['id'] . "\n";
        echo "Nom: " . $existingAdmin['name'] . "\n";
        echo "Numéro: " . $existingAdmin['number'] . "\n";
        echo "Fonction: " . $existingAdmin['function'] . "\n";
        echo "Droits: " . $existingAdmin['access_rights'] . "\n\n";

        // Mettre à jour les permissions si nécessaire
        $currentRights = explode(',', $existingAdmin['access_rights']);
        $currentRights = array_map('trim', $currentRights);
        
        $requiredRights = explode(',', $adminData['access_rights']);
        $requiredRights = array_map('trim', $requiredRights);
        
        $missingRights = array_diff($requiredRights, $currentRights);
        
        if (!empty($missingRights)) {
            echo "🔧 Ajout des permissions manquantes...\n";
            $newRights = array_merge($currentRights, $missingRights);
            $newRightsString = implode(',', $newRights);
            
            $updateStmt = $pdo->prepare("UPDATE employees SET access_rights = ?, function = ? WHERE id = ?");
            $result = $updateStmt->execute([$newRightsString, $adminData['function'], $existingAdmin['id']]);
            
            if ($result) {
                echo "✅ Permissions mises à jour!\n";
                echo "Nouvelles permissions: " . $newRightsString . "\n\n";
            } else {
                echo "❌ Erreur lors de la mise à jour\n";
            }
        } else {
            echo "✅ Toutes les permissions sont déjà présentes\n";
        }

        $adminId = $existingAdmin['id'];
    } else {
        echo "🔧 Création d'un nouveau compte admin...\n";
        
        // Insérer le nouvel admin
        $insertStmt = $pdo->prepare("INSERT INTO employees (name, function, access_rights, lang, number, password) VALUES (?, ?, ?, ?, ?, ?)");
        $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
        $result = $insertStmt->execute([
            $adminData['name'],
            $adminData['function'],
            $adminData['access_rights'],
            $adminData['lang'],
            $adminData['number'],
            $defaultPassword
        ]);

        if ($result) {
            $adminId = $pdo->lastInsertId();
            echo "✅ Compte admin créé avec succès!\n";
            echo "ID: " . $adminId . "\n";
            echo "Nom: " . $adminData['name'] . "\n";
            echo "Numéro: " . $adminData['number'] . "\n";
            echo "Fonction: " . $adminData['function'] . "\n";
            echo "Droits: " . $adminData['access_rights'] . "\n\n";
        } else {
            echo "❌ Erreur lors de la création\n";
            exit;
        }
    }

    // Créer la session pour cet admin
    $_SESSION['uid'] = $adminId;
    $_SESSION['name'] = $adminData['name'];
    $_SESSION['email'] = 'admin@library.com'; // Email par défaut
    $_SESSION['role'] = 'admin';
    $_SESSION['employee_name'] = $adminData['name'];
    $_SESSION['employee_function'] = $adminData['function'];
    $_SESSION['employee_rights'] = $adminData['access_rights'];
    $_SESSION['lang'] = 'ar';

    echo "=== SESSION CRÉÉE ===\n";
    echo "✅ Connecté en tant qu'admin\n";
    echo "ID: " . $_SESSION['uid'] . "\n";
    echo "Nom: " . $_SESSION['name'] . "\n";
    echo "Fonction: " . $_SESSION['employee_function'] . "\n\n";

    // Vérifier les permissions
    echo "=== VÉRIFICATION DES PERMISSIONS ===\n";
    $rights = explode(',', $adminData['access_rights']);
    $rights = array_map('trim', $rights);
    
    echo "Permissions totales: " . count($rights) . "\n";
    echo "Permissions:\n";
    foreach ($rights as $right) {
        if (!empty($right)) {
            echo "  ✅ {$right}\n";
        }
    }

    echo "\n=== FONCTIONNALITÉS DISPONIBLES ===\n";
    echo "✅ Ajouter des livres\n";
    echo "✅ Modifier des livres\n";
    echo "✅ Supprimer des livres\n";
    echo "✅ Gérer les magazines\n";
    echo "✅ Gérer les journaux\n";
    echo "✅ Gérer les emprunteurs\n";
    echo "✅ Gérer les prêts\n";
    echo "✅ Gérer les employés\n";
    echo "✅ Administration du système\n";

    echo "\n=== URLs DE TEST ===\n";
    echo "Testez ces URLs dans votre navigateur:\n";
    echo "1. Liste des livres: http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
    echo "2. Ajouter un livre: http://localhost/library_system/modules/items/add_book.php?lang=ar\n";
    echo "3. Dashboard: http://localhost/library_system/public/dashboard.php?lang=ar\n";

    echo "\n=== INFORMATIONS DE CONNEXION ===\n";
    echo "Pour vous connecter via l'interface web:\n";
    echo "URL: http://localhost/library_system/public/employee_login.php\n";
    echo "Numéro employé: ADMIN001\n";
    echo "Mot de passe: 123456 (par défaut)\n";

} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

echo "\n=== CRÉATION TERMINÉE ===\n";
?> 