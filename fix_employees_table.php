<?php
/* fix_employees_table.php */
require_once 'config/db.php';

echo "<h1>🔧 إصلاح جدول الموظفين - Réparation de la table des employés</h1>\n";
echo "<hr>\n";

try {
    // حذف الجدول القديم إذا كان موجوداً
    echo "<h2>1. حذف الجدول القديم</h2>\n";
    echo "<h2>1. Suppression de l'ancienne table</h2>\n";
    
    $pdo->exec("DROP TABLE IF EXISTS employees");
    echo "✅ تم حذف الجدول القديم<br>\n";
    echo "✅ Ancienne table supprimée<br>\n";
    
    // إنشاء الجدول الجديد بالهيكل الصحيح
    echo "<h2>2. إنشاء الجدول الجديد</h2>\n";
    echo "<h2>2. Création de la nouvelle table</h2>\n";
    
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
    echo "✅ تم إنشاء الجدول الجديد بنجاح!<br>\n";
    echo "✅ Nouvelle table créée avec succès!<br>\n";
    
    // إضافة البيانات التجريبية
    echo "<h2>3. إضافة البيانات التجريبية</h2>\n";
    echo "<h2>3. Ajout des données d'exemple</h2>\n";
    
    $sample_employees = [
        [
            'lang' => 'ar',
            'name' => 'أحمد محمد علي',
            'number' => 'EMP001',
            'function' => 'مدير المكتبة',
            'access_rights' => 'إدارة الكتب, إدارة المجلات, إدارة الصحف, إدارة التزويد, إدارة المستعيرين, إدارة الإعارة, إدارة الموظفين, إدارة النظام'
        ],
        [
            'lang' => 'ar',
            'name' => 'فاطمة أحمد حسن',
            'number' => 'EMP002',
            'function' => 'أمين المكتبة',
            'access_rights' => 'إدارة الكتب, إدارة المجلات, إدارة الصحف, إدارة المستعيرين, إدارة الإعارة'
        ],
        [
            'lang' => 'fr',
            'name' => 'Jean Dupont',
            'number' => 'EMP003',
            'function' => 'Directeur de bibliothèque',
            'access_rights' => 'Gestion des livres, Gestion des magazines, Gestion des journaux, Gestion des approvisionnements, Gestion des emprunteurs, Gestion des prêts, Gestion des employés, Administration du système'
        ],
        [
            'lang' => 'fr',
            'name' => 'Marie Martin',
            'number' => 'EMP004',
            'function' => 'Bibliothécaire',
            'access_rights' => 'Gestion des livres, Gestion des magazines, Gestion des journaux, Gestion des emprunteurs, Gestion des prêts'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO employees (lang, name, number, function, access_rights, password) VALUES (?, ?, ?, ?, ?, ?)");
    $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
    
    foreach ($sample_employees as $employee) {
        try {
            $stmt->execute([
                $employee['lang'],
                $employee['name'],
                $employee['number'],
                $employee['function'],
                $employee['access_rights'],
                $defaultPassword
            ]);
            echo "✅ تم إضافة الموظف: " . $employee['name'] . " (" . $employee['number'] . ")<br>\n";
            echo "✅ Employé ajouté: " . $employee['name'] . " (" . $employee['number'] . ")<br>\n";
        } catch (PDOException $e) {
            echo "⚠️ خطأ في إضافة الموظف: " . $employee['name'] . " - " . $e->getMessage() . "<br>\n";
            echo "⚠️ Erreur lors de l'ajout de l'employé: " . $employee['name'] . " - " . $e->getMessage() . "<br>\n";
        }
    }
    
    // التحقق من الجدول
    echo "<h2>4. التحقق من الجدول</h2>\n";
    echo "<h2>4. Vérification de la table</h2>\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees");
    $count = $stmt->fetch()['total'];
    echo "📊 إجمالي الموظفين: $count<br>\n";
    echo "📊 Total des employés: $count<br>\n";
    
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
    
    // عرض الموظفين
    echo "<h3>الموظفون / Employés:</h3>\n";
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY lang, name");
    $employees = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 1rem 0;'>\n";
    echo "<tr style='background: #f3f4f6;'>\n";
    echo "<th style='padding: 0.5rem;'>الاسم / Nom</th>\n";
    echo "<th style='padding: 0.5rem;'>الرقم / Numéro</th>\n";
    echo "<th style='padding: 0.5rem;'>الوظيفة / Fonction</th>\n";
    echo "<th style='padding: 0.5rem;'>اللغة / Langue</th>\n";
    echo "<th style='padding: 0.5rem;'>كلمة المرور / Mot de passe</th>\n";
    echo "</tr>\n";
    
    foreach ($employees as $emp) {
        echo "<tr>\n";
        echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['name']) . "</td>\n";
        echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['number']) . "</td>\n";
        echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['function']) . "</td>\n";
        echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['lang']) . "</td>\n";
        echo "<td style='padding: 0.5rem;'>" . (empty($emp['password']) ? '❌' : '✅') . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // معلومات تسجيل الدخول
    echo "<h3>معلومات تسجيل الدخول / Informations de connexion:</h3>\n";
    echo "<p>🔑 كلمة المرور لجميع الموظفين: <strong>123456</strong></p>\n";
    echo "<p>🔑 Mot de passe pour tous les employés: <strong>123456</strong></p>\n";
    
    echo "<h3>روابط الاختبار / Liens de test:</h3>\n";
    echo "<ul>\n";
    echo "<li><a href='public/employee_login.php?lang=ar' target='_blank'>تسجيل دخول الموظفين (عربي)</a></li>\n";
    echo "<li><a href='public/employee_login.php?lang=fr' target='_blank'>Connexion employés (français)</a></li>\n";
    echo "<li><a href='modules/administration/add.php?lang=ar' target='_blank'>إضافة موظف جديد (عربي)</a></li>\n";
    echo "<li><a href='modules/administration/add.php?lang=fr' target='_blank'>Ajouter un employé (français)</a></li>\n";
    echo "</ul>\n";
    
    echo "<h2>✅ إصلاح الجدول مكتمل!</h2>\n";
    echo "<p>جدول الموظفين تم إصلاحه بنجاح وهو جاهز للاستخدام.</p>\n";
    echo "<p>La table des employés a été réparée avec succès et est prête à l'utilisation.</p>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ خطأ في إصلاح الجدول</h2>\n";
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

ul {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

li {
    margin-bottom: 8px;
}

a {
    color: #3498db;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

hr {
    border: none;
    border-top: 2px solid #ecf0f1;
    margin: 30px 0;
}
</style> 