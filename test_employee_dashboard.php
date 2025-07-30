<?php
/* test_employee_dashboard.php */
require_once 'config/db.php';
require_once 'includes/permissions.php';

echo "<h1>🧪 اختبار لوحة التحكم المخصصة للموظفين</h1>\n";
echo "<h1>Test du tableau de bord personnalisé des employés</h1>\n";
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
    
    // اختبار الموظفين المتاحين
    echo "<h2>3. اختبار الموظفين المتاحين</h2>\n";
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY lang, name");
    $employees = $stmt->fetchAll();
    
    if (count($employees) > 0) {
        echo "✅ يوجد " . count($employees) . " موظف في النظام<br>\n";
        echo "✅ Il y a " . count($employees) . " employé(s) dans le système<br>\n";
        
        echo "<h3>الموظفون المتاحون / Employés disponibles:</h3>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 1rem 0;'>\n";
        echo "<tr style='background: #f3f4f6;'>\n";
        echo "<th style='padding: 0.5rem; text-align: center;'>الاسم / Nom</th>\n";
        echo "<th style='padding: 0.5rem; text-align: center;'>الرقم / Numéro</th>\n";
        echo "<th style='padding: 0.5rem; text-align: center;'>الوظيفة / Fonction</th>\n";
        echo "<th style='padding: 0.5rem; text-align: center;'>اللغة / Langue</th>\n";
        echo "<th style='padding: 0.5rem; text-align: center;'>الحقوق / Droits</th>\n";
        echo "</tr>\n";
        
        foreach ($employees as $emp) {
            echo "<tr>\n";
            echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['name']) . "</td>\n";
            echo "<td style='padding: 0.5rem; text-align: center;'>" . htmlspecialchars($emp['number']) . "</td>\n";
            echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($emp['function']) . "</td>\n";
            echo "<td style='padding: 0.5rem; text-align: center;'>" . htmlspecialchars($emp['lang']) . "</td>\n";
            echo "<td style='padding: 0.5rem; font-size: 0.875rem;'>" . htmlspecialchars($emp['access_rights']) . "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "⚠️ لا يوجد موظفين في النظام<br>\n";
        echo "⚠️ Aucun employé dans le système<br>\n";
    }
    
    // اختبار وظائف إدارة الحقوق
    echo "<h2>4. اختبار وظائف إدارة الحقوق</h2>\n";
    
    // محاكاة تسجيل دخول موظف
    if (count($employees) > 0) {
        $testEmployee = $employees[0];
        $_SESSION['uid'] = $testEmployee['id'];
        
        echo "🔐 محاكاة تسجيل دخول: " . htmlspecialchars($testEmployee['name']) . "<br>\n";
        echo "🔐 Simulation de connexion: " . htmlspecialchars($testEmployee['name']) . "<br>\n";
        
        // اختبار الحصول على الحقوق
        $rights = getCurrentEmployeeRights();
        echo "📋 الحقوق المتاحة: " . implode(', ', $rights) . "<br>\n";
        echo "📋 Droits disponibles: " . implode(', ', $rights) . "<br>\n";
        
        // اختبار القوائم المتاحة
        $menus = getAvailableMenus($testEmployee['lang']);
        echo "📋 القوائم المتاحة: " . count($menus) . " قائمة<br>\n";
        echo "📋 Menus disponibles: " . count($menus) . " menu(s)<br>\n";
        
        foreach ($menus as $key => $menu) {
            echo "   - " . $menu['title'] . " (" . $key . ")<br>\n";
        }
        
        // اختبار الإجراءات المتاحة
        $actions = getAvailableActions($testEmployee['lang']);
        echo "⚡ الإجراءات المتاحة: " . count($actions) . " إجراء<br>\n";
        echo "⚡ Actions disponibles: " . count($actions) . " action(s)<br>\n";
        
        foreach ($actions as $key => $action) {
            echo "   - " . $action['title'] . " (" . $key . ")<br>\n";
        }
        
        // اختبار الصلاحيات
        echo "<h3>اختبار الصلاحيات / Test des permissions:</h3>\n";
        echo "يمكن الإضافة: " . (canAdd() ? "✅ نعم" : "❌ لا") . " / " . (canAdd() ? "✅ Oui" : "❌ Non") . "<br>\n";
        echo "يمكن التعديل: " . (canEdit() ? "✅ نعم" : "❌ لا") . " / " . (canEdit() ? "✅ Oui" : "❌ Non") . "<br>\n";
        echo "يمكن الحذف: " . (canDelete() ? "✅ نعم" : "❌ لا") . " / " . (canDelete() ? "✅ Oui" : "❌ Non") . "<br>\n";
        
        // إزالة محاكاة تسجيل الدخول
        unset($_SESSION['uid']);
    }
    
    // اختبار الروابط
    echo "<h2>5. اختبار الروابط</h2>\n";
    echo "<p>🔗 روابط النظام:</p>\n";
    echo "<ul>\n";
    echo "<li><a href='public/login.php?lang=ar' target='_blank'>تسجيل الدخول العام (عربي)</a></li>\n";
    echo "<li><a href='public/login.php?lang=fr' target='_blank'>Connexion générale (français)</a></li>\n";
    echo "<li><a href='public/employee_login.php?lang=ar' target='_blank'>تسجيل دخول الموظفين (عربي)</a></li>\n";
    echo "<li><a href='public/employee_login.php?lang=fr' target='_blank'>Connexion employés (français)</a></li>\n";
    echo "</ul>\n";
    
    // اختبار لوحة التحكم المخصصة
    echo "<h2>6. اختبار لوحة التحكم المخصصة</h2>\n";
    echo "<p>⚠️ ملاحظة: يجب تسجيل الدخول أولاً لاختبار لوحة التحكم المخصصة</p>\n";
    echo "<p>⚠️ Note: Vous devez d'abord vous connecter pour tester le tableau de bord personnalisé</p>\n";
    
    echo "<h3>خطوات الاختبار / Étapes de test:</h3>\n";
    echo "<ol>\n";
    echo "<li>اذهب إلى <a href='public/employee_login.php?lang=ar' target='_blank'>تسجيل دخول الموظفين</a></li>\n";
    echo "<li>استخدم رقم الموظف: EMP001 وكلمة المرور: 123456</li>\n";
    echo "<li>ستتم توجيهك إلى لوحة التحكم المخصصة</li>\n";
    echo "<li>سترى فقط القوائم والإجراءات المتاحة حسب حقوقك</li>\n";
    echo "</ol>\n";
    
    echo "<h2>✅ اختبار النظام مكتمل!</h2>\n";
    echo "<p>لوحة التحكم المخصصة للموظفين جاهزة للاستخدام.</p>\n";
    echo "<p>Le tableau de bord personnalisé des employés est prêt à l'utilisation.</p>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ خطأ في الاختبار</h2>\n";
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

h3 {
    color: #2c3e50;
    margin-top: 20px;
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

ul, ol {
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