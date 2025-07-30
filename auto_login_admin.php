<?php
/**
 * Script de connexion automatique admin pour les tests
 * Auto-login admin script for testing
 */

require_once 'includes/auth.php';

echo "=== Connexion Automatique Admin ===\n\n";

// Vérifier si déjà connecté
if (isset($_SESSION['uid'])) {
    echo "✅ Déjà connecté\n";
    echo "ID: " . $_SESSION['uid'] . "\n";
    echo "Nom: " . ($_SESSION['name'] ?? $_SESSION['employee_name'] ?? 'Non défini') . "\n";
    echo "Email: " . ($_SESSION['email'] ?? 'Non défini') . "\n";
    echo "Fonction: " . ($_SESSION['employee_function'] ?? 'Non défini') . "\n\n";

    echo "🎯 Vous pouvez maintenant tester les URLs:\n";
    echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
    echo "http://localhost/library_system/modules/items/add_book.php?lang=ar\n";
    exit;
}

try {
    // Rechercher un employé admin dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE function LIKE '%مدير%' OR function LIKE '%Directeur%' LIMIT 1");
    $stmt->execute();
    $adminEmployee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($adminEmployee) {
        // Créer la session complète pour cet employé admin
        $_SESSION['uid'] = $adminEmployee['id'];
        $_SESSION['name'] = $adminEmployee['name'];
        $_SESSION['email'] = $adminEmployee['email'];
        $_SESSION['role'] = 'admin';
        $_SESSION['employee_name'] = $adminEmployee['name'];
        $_SESSION['employee_function'] = $adminEmployee['function'];
        $_SESSION['employee_rights'] = $adminEmployee['access_rights'];
        $_SESSION['lang'] = 'ar';

        echo "✅ Connexion réussie!\n";
        echo "ID: " . $_SESSION['uid'] . "\n";
        echo "Nom: " . $_SESSION['name'] . "\n";
        echo "Email: " . $_SESSION['email'] . "\n";
        echo "Fonction: " . $_SESSION['employee_function'] . "\n";
        echo "Droits d'accès: " . $_SESSION['employee_rights'] . "\n\n";

        echo "🎯 Vous êtes maintenant connecté en tant qu'admin!\n";
        echo "Testez ces URLs:\n";
        echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
        echo "http://localhost/library_system/modules/items/add_book.php?lang=ar\n";

    } else {
        echo "❌ Aucun employé admin trouvé dans la base de données\n";
        echo "Création d'un compte admin temporaire...\n\n";

        // Créer un employé admin temporaire
        $adminData = [
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'function' => 'مدير',
            'access_rights' => 'إدارة الكتب,Gestion des livres,عرض الكتب,Consultation des livres,إدارة المجلات,Gestion des magazines,عرض المجلات,Consultation des revues,إدارة الصحف,Gestion des journaux,عرض الصحف,Consultation des journaux,إدارة المستعيرين,Gestion des emprunteurs,إدارة الإعارة,Gestion des prêts,إدارة الموظفين,Gestion des employés',
            'status' => 'active',
            'lang' => 'ar'
        ];

        $insertStmt = $pdo->prepare("INSERT INTO employees (name, email, function, access_rights, status, lang) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $insertStmt->execute([
            $adminData['name'],
            $adminData['email'],
            $adminData['function'],
            $adminData['access_rights'],
            $adminData['status'],
            $adminData['lang']
        ]);

        if ($result) {
            $adminId = $pdo->lastInsertId();

            // Créer la session complète
            $_SESSION['uid'] = $adminId;
            $_SESSION['name'] = $adminData['name'];
            $_SESSION['email'] = $adminData['email'];
            $_SESSION['role'] = 'admin';
            $_SESSION['employee_name'] = $adminData['name'];
            $_SESSION['employee_function'] = $adminData['function'];
            $_SESSION['employee_rights'] = $adminData['access_rights'];
            $_SESSION['lang'] = 'ar';

            echo "✅ Compte admin créé et connexion réussie!\n";
            echo "ID: " . $_SESSION['uid'] . "\n";
            echo "Nom: " . $_SESSION['name'] . "\n";
            echo "Email: " . $_SESSION['email'] . "\n";
            echo "Fonction: " . $_SESSION['employee_function'] . "\n\n";

            echo "🎯 Vous êtes maintenant connecté en tant qu'admin!\n";
            echo "Testez ces URLs:\n";
            echo "http://localhost/library_system/public/router.php?module=items&action=list&lang=ar&type=book\n";
            echo "http://localhost/library_system/modules/items/add_book.php?lang=ar\n";

        } else {
            echo "❌ Erreur lors de la création du compte admin\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

echo "\n=== Connexion terminée ===\n";
?> 