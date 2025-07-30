<?php
/* test_administration.php */
require_once 'config/db.php';

echo "<h1>🧪 اختبار نظام الإدارة - Test du système d'administration</h1>\n";
echo "<hr>\n";

try {
    // اختبار الاتصال بقاعدة البيانات
    echo "<h2>1. اختبار الاتصال بقاعدة البيانات</h2>\n";
    $pdo->query("SELECT 1");
    echo "✅ الاتصال بقاعدة البيانات يعمل بشكل صحيح<br>\n";
    echo "✅ Connexion à la base de données fonctionne correctement<br>\n";
    
    // اختبار وجود جدول الموظفين
    echo "<h2>2. اختبار وجود جدول الموظفين</h2>\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() > 0) {
        echo "✅ جدول الموظفين موجود<br>\n";
        echo "✅ Table des employés existe<br>\n";
    } else {
        echo "❌ جدول الموظفين غير موجود<br>\n";
        echo "❌ Table des employés n'existe pas<br>\n";
    }
    
    // اختبار عدد الموظفين
    echo "<h2>3. اختبار عدد الموظفين</h2>\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees");
    $count = $stmt->fetch()['total'];
    echo "📊 إجمالي الموظفين: $count<br>\n";
    echo "📊 Total des employés: $count<br>\n";
    
    // اختبار الموظفين باللغة العربية
    echo "<h2>4. اختبار الموظفين باللغة العربية</h2>\n";
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE lang = 'ar'");
    $stmt->execute();
    $arabic_employees = $stmt->fetchAll();
    
    if (count($arabic_employees) > 0) {
        echo "✅ يوجد " . count($arabic_employees) . " موظف باللغة العربية<br>\n";
        echo "✅ Il y a " . count($arabic_employees) . " employé(s) en arabe<br>\n";
        
        foreach ($arabic_employees as $emp) {
            echo "   - {$emp['name']} ({$emp['number']}) - {$emp['function']}<br>\n";
        }
    } else {
        echo "⚠️ لا يوجد موظفين باللغة العربية<br>\n";
        echo "⚠️ Aucun employé en arabe<br>\n";
    }
    
    // اختبار الموظفين باللغة الفرنسية
    echo "<h2>5. اختبار الموظفين باللغة الفرنسية</h2>\n";
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE lang = 'fr'");
    $stmt->execute();
    $french_employees = $stmt->fetchAll();
    
    if (count($french_employees) > 0) {
        echo "✅ يوجد " . count($french_employees) . " موظف باللغة الفرنسية<br>\n";
        echo "✅ Il y a " . count($french_employees) . " employé(s) en français<br>\n";
        
        foreach ($french_employees as $emp) {
            echo "   - {$emp['name']} ({$emp['number']}) - {$emp['function']}<br>\n";
        }
    } else {
        echo "⚠️ لا يوجد موظفين باللغة الفرنسية<br>\n";
        echo "⚠️ Aucun employé en français<br>\n";
    }
    
    // اختبار الوظائف المتاحة
    echo "<h2>6. اختبار الوظائف المتاحة</h2>\n";
    $stmt = $pdo->query("SELECT DISTINCT function FROM employees ORDER BY function");
    $functions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 الوظائف المستخدمة:<br>\n";
    echo "📋 Fonctions utilisées:<br>\n";
    foreach ($functions as $function) {
        echo "   - $function<br>\n";
    }
    
    // اختبار حقوق الوصول
    echo "<h2>7. اختبار حقوق الوصول</h2>\n";
    $stmt = $pdo->query("SELECT access_rights FROM employees LIMIT 1");
    $rights = $stmt->fetch()['access_rights'];
    
    echo "🔐 حقوق الوصول (مثال):<br>\n";
    echo "🔐 Droits d'accès (exemple):<br>\n";
    echo "   $rights<br>\n";
    
    // اختبار الروابط
    echo "<h2>8. اختبار الروابط</h2>\n";
    echo "<p>🔗 روابط نظام الإدارة:</p>\n";
    echo "<ul>\n";
    echo "<li><a href='modules/administration/list.php?lang=ar' target='_blank'>قائمة الموظفين (عربي)</a></li>\n";
    echo "<li><a href='modules/administration/list.php?lang=fr' target='_blank'>Liste des employés (français)</a></li>\n";
    echo "<li><a href='modules/administration/add.php?lang=ar' target='_blank'>إضافة موظف جديد (عربي)</a></li>\n";
    echo "<li><a href='modules/administration/add.php?lang=fr' target='_blank'>Ajouter un employé (français)</a></li>\n";
    echo "<li><a href='public/dashboard.php?lang=ar' target='_blank'>لوحة التحكم (عربي)</a></li>\n";
    echo "<li><a href='public/dashboard.php?lang=fr' target='_blank'>Tableau de bord (français)</a></li>\n";
    echo "</ul>\n";
    
    echo "<h2>✅ اختبار النظام مكتمل!</h2>\n";
    echo "<p>نظام الإدارة جاهز للاستخدام.</p>\n";
    echo "<p>Le système d'administration est prêt à l'utilisation.</p>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ خطأ في الاختبار</h2>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
    echo "<p>Erreur: " . $e->getMessage() . "</p>\n";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}

h1 {
    color: #2c3e50;
    border-bottom: 3px solid #3498db;
    padding-bottom: 10px;
}

h2 {
    color: #34495e;
    margin-top: 30px;
    border-left: 4px solid #3498db;
    padding-left: 15px;
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