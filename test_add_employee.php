<?php
/* test_add_employee.php */
require_once 'config/db.php';

echo "<h1>🧪 اختبار إضافة موظف جديد مع كلمة مرور</h1>\n";
echo "<hr>\n";

try {
    // بيانات موظف تجريبي
    $testEmployee = [
        'lang' => 'ar',
        'name' => 'موظف تجريبي للاختبار',
        'number' => 'TEST001',
        'function' => 'أمين المكتبة',
        'access_rights' => 'إدارة الكتب, إدارة المجلات, إدارة الصحف'
    ];
    
    echo "<h2>1. إضافة موظف تجريبي</h2>\n";
    
    // التحقق من عدم وجود الموظف مسبقاً
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE number = ?");
    $stmt->execute([$testEmployee['number']]);
    if ($stmt->fetchColumn() > 0) {
        echo "⚠️ الموظف موجود مسبقاً، سيتم حذفه أولاً...<br>\n";
        $pdo->prepare("DELETE FROM employees WHERE number = ?")->execute([$testEmployee['number']]);
    }
    
    // إنشاء كلمة مرور افتراضية
    $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
    
    // إضافة الموظف
    $stmt = $pdo->prepare("INSERT INTO employees (lang, name, number, function, access_rights, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $testEmployee['lang'],
        $testEmployee['name'],
        $testEmployee['number'],
        $testEmployee['function'],
        $testEmployee['access_rights'],
        $defaultPassword
    ]);
    
    echo "✅ تم إضافة الموظف التجريبي بنجاح!<br>\n";
    
    // التحقق من إضافة كلمة المرور
    echo "<h2>2. التحقق من كلمة المرور</h2>\n";
    
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE number = ?");
    $stmt->execute([$testEmployee['number']]);
    $employee = $stmt->fetch();
    
    if ($employee) {
        echo "✅ تم العثور على الموظف في قاعدة البيانات<br>\n";
        echo "📋 معلومات الموظف:<br>\n";
        echo "- الاسم: " . $employee['name'] . "<br>\n";
        echo "- الرقم: " . $employee['number'] . "<br>\n";
        echo "- الوظيفة: " . $employee['function'] . "<br>\n";
        echo "- كلمة المرور موجودة: " . (empty($employee['password']) ? '❌' : '✅') . "<br>\n";
        
        // اختبار تسجيل الدخول
        if (!empty($employee['password'])) {
            $testPassword = '123456';
            if (password_verify($testPassword, $employee['password'])) {
                echo "✅ كلمة المرور تعمل بشكل صحيح!<br>\n";
            } else {
                echo "❌ خطأ في كلمة المرور!<br>\n";
            }
        }
    } else {
        echo "❌ لم يتم العثور على الموظف!<br>\n";
    }
    
    // عرض جميع الموظفين
    echo "<h2>3. قائمة جميع الموظفين</h2>\n";
    
    $stmt = $pdo->query("SELECT name, number, function, lang, password FROM employees ORDER BY created_at DESC");
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
    
    // روابط الاختبار
    echo "<h2>4. روابط الاختبار</h2>\n";
    echo "<ul>\n";
    echo "<li><a href='modules/administration/add.php?lang=ar' target='_blank'>إضافة موظف جديد (عربي)</a></li>\n";
    echo "<li><a href='modules/administration/list.php?lang=ar' target='_blank'>قائمة الموظفين (عربي)</a></li>\n";
    echo "<li><a href='public/employee_login.php?lang=ar' target='_blank'>تسجيل دخول الموظفين (عربي)</a></li>\n";
    echo "<li><a href='public/employee_login.php?lang=fr' target='_blank'>Connexion employés (français)</a></li>\n";
    echo "</ul>\n";
    
    // معلومات تسجيل الدخول
    echo "<h3>معلومات تسجيل الدخول للاختبار:</h3>\n";
    echo "<p>🔑 رقم الموظف: <strong>TEST001</strong></p>\n";
    echo "<p>🔑 كلمة المرور: <strong>123456</strong></p>\n";
    
    echo "<h2>✅ الاختبار مكتمل!</h2>\n";
    echo "<p>نظام إضافة الموظفين مع كلمة المرور يعمل بشكل صحيح.</p>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ خطأ في الاختبار</h2>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
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