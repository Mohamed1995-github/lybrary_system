<?php
/* update_employees_table.php */
require_once 'config/db.php';

echo "<h1>🔄 تحديث جدول الموظفين - Mise à jour de la table des employés</h1>\n";
echo "<hr>\n";

try {
    // التحقق من وجود الجدول
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() == 0) {
        echo "❌ جدول الموظفين غير موجود. سيتم إنشاؤه...<br>\n";
        echo "❌ Table des employés n'existe pas. Elle sera créée...<br>\n";
        
        // إنشاء الجدول
        $sql = "CREATE TABLE employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lang ENUM('ar', 'fr') NOT NULL DEFAULT 'ar',
            name VARCHAR(255) NOT NULL,
            number VARCHAR(50) NOT NULL UNIQUE,
            function VARCHAR(255) NOT NULL,
            access_rights TEXT NOT NULL,
            password VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_lang (lang),
            INDEX idx_number (number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✅ تم إنشاء جدول الموظفين بنجاح!<br>\n";
        echo "✅ Table des employés créée avec succès!<br>\n";
    } else {
        echo "✅ جدول الموظفين موجود<br>\n";
        echo "✅ Table des employés existe<br>\n";
        
        // التحقق من وجود عمود كلمة المرور
        $stmt = $pdo->query("SHOW COLUMNS FROM employees LIKE 'password'");
        if ($stmt->rowCount() == 0) {
            echo "⚠️ عمود كلمة المرور غير موجود. سيتم إضافته...<br>\n";
            echo "⚠️ Colonne mot de passe n'existe pas. Elle sera ajoutée...<br>\n";
            
            // إضافة عمود كلمة المرور
            $pdo->exec("ALTER TABLE employees ADD COLUMN password VARCHAR(255) DEFAULT NULL AFTER access_rights");
            echo "✅ تم إضافة عمود كلمة المرور بنجاح!<br>\n";
            echo "✅ Colonne mot de passe ajoutée avec succès!<br>\n";
        } else {
            echo "✅ عمود كلمة المرور موجود<br>\n";
            echo "✅ Colonne mot de passe existe<br>\n";
        }
    }
    
    // تحديث كلمات المرور للموظفين الموجودين
    echo "<h2>🔐 تحديث كلمات المرور للموظفين الموجودين</h2>\n";
    echo "<h2>Mise à jour des mots de passe des employés existants</h2>\n";
    
    $stmt = $pdo->query("SELECT id, number FROM employees WHERE password IS NULL OR password = ''");
    $employeesWithoutPassword = $stmt->fetchAll();
    
    if (count($employeesWithoutPassword) > 0) {
        echo "📋 يوجد " . count($employeesWithoutPassword) . " موظف بدون كلمة مرور<br>\n";
        echo "📋 Il y a " . count($employeesWithoutPassword) . " employé(s) sans mot de passe<br>\n";
        
        $updateStmt = $pdo->prepare("UPDATE employees SET password = ? WHERE id = ?");
        $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
        
        foreach ($employeesWithoutPassword as $emp) {
            $updateStmt->execute([$defaultPassword, $emp['id']]);
            echo "✅ تم تحديث كلمة المرور للموظف: " . $emp['number'] . "<br>\n";
            echo "✅ Mot de passe mis à jour pour l'employé: " . $emp['number'] . "<br>\n";
        }
    } else {
        echo "✅ جميع الموظفين لديهم كلمات مرور<br>\n";
        echo "✅ Tous les employés ont des mots de passe<br>\n";
    }
    
    // عرض معلومات الجدول المحدث
    echo "<h2>📊 معلومات الجدول المحدث</h2>\n";
    echo "<h2>Informations de la table mise à jour</h2>\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees");
    $count = $stmt->fetch()['total'];
    echo "📈 إجمالي الموظفين: $count<br>\n";
    echo "📈 Total des employés: $count<br>\n";
    
    // عرض هيكل الجدول
    echo "<h3>هيكل الجدول / Structure de la table:</h3>\n";
    $stmt = $pdo->query("DESCRIBE employees");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 1rem 0;'>\n";
    echo "<tr style='background: #f3f4f6;'>\n";
    echo "<th style='padding: 0.5rem;'>الحقل / Champ</th>\n";
    echo "<th style='padding: 0.5rem;'>النوع / Type</th>\n";
    echo "<th style='padding: 0.5rem;'>Null</th>\n";
    echo "<th style='padding: 0.5rem;'>المفتاح / Clé</th>\n";
    echo "<th style='padding: 0.5rem;'>الافتراضي / Défaut</th>\n";
    echo "</tr>\n";
    
    foreach ($columns as $column) {
        echo "<tr>\n";
        echo "<td style='padding: 0.5rem;'>" . $column['Field'] . "</td>\n";
        echo "<td style='padding: 0.5rem;'>" . $column['Type'] . "</td>\n";
        echo "<td style='padding: 0.5rem;'>" . $column['Null'] . "</td>\n";
        echo "<td style='padding: 0.5rem;'>" . $column['Key'] . "</td>\n";
        echo "<td style='padding: 0.5rem;'>" . ($column['Default'] ?? 'NULL') . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // معلومات تسجيل الدخول
    echo "<h3>معلومات تسجيل الدخول / Informations de connexion:</h3>\n";
    echo "<p>🔑 كلمة المرور الافتراضية لجميع الموظفين: <strong>123456</strong></p>\n";
    echo "<p>🔑 Mot de passe par défaut pour tous les employés: <strong>123456</strong></p>\n";
    
    // عرض الموظفين المتاحين
    $stmt = $pdo->query("SELECT name, number, function, lang FROM employees ORDER BY lang, name");
    $employees = $stmt->fetchAll();
    
    if (count($employees) > 0) {
        echo "<h3>الموظفون المتاحون / Employés disponibles:</h3>\n";
        echo "<table border='1' style='border-collapse: collapse; margin: 1rem 0;'>\n";
        echo "<tr style='background: #f3f4f6;'>\n";
        echo "<th style='padding: 0.5rem;'>الاسم / Nom</th>\n";
        echo "<th style='padding: 0.5rem;'>الرقم / Numéro</th>\n";
        echo "<th style='padding: 0.5rem;'>الوظيفة / Fonction</th>\n";
        echo "<th style='padding: 0.5rem;'>اللغة / Langue</th>\n";
        echo "</tr>\n";
        
        foreach ($employees as $emp) {
            echo "<tr>\n";
            echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['name']) . "</td>\n";
            echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['number']) . "</td>\n";
            echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['function']) . "</td>\n";
            echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['lang']) . "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    echo "<h2>✅ تحديث الجدول مكتمل!</h2>\n";
    echo "<p>جدول الموظفين جاهز للاستخدام مع نظام تسجيل الدخول.</p>\n";
    echo "<p>La table des employés est prête à l'utilisation avec le système de connexion.</p>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ خطأ في تحديث الجدول</h2>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
    echo "<p>Erreur: " . $e->getMessage() . "</p>\n";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}

h1, h2, h3 {
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

th {
    background: #f8f9fa;
    font-weight: 600;
}

hr {
    border: none;
    border-top: 2px solid #ecf0f1;
    margin: 30px 0;
}
</style> 