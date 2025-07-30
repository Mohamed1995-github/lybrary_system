<?php
/* create_employees_table.php */
require_once 'config/db.php';

try {
    // إنشاء جدول الموظفين
    $sql = "CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lang ENUM('ar', 'fr') NOT NULL DEFAULT 'ar',
        name VARCHAR(255) NOT NULL,
        number VARCHAR(50) NOT NULL UNIQUE,
        function VARCHAR(255) NOT NULL,
        access_rights TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_lang (lang),
        INDEX idx_number (number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    echo "✅ جدول الموظفين تم إنشاؤه بنجاح!\n";
    echo "✅ Table des employés créée avec succès!\n\n";
    
    // إضافة بعض البيانات التجريبية
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
    
    $stmt = $pdo->prepare("INSERT INTO employees (lang, name, number, function, access_rights) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($sample_employees as $employee) {
        try {
            $stmt->execute([
                $employee['lang'],
                $employee['name'],
                $employee['number'],
                $employee['function'],
                $employee['access_rights']
            ]);
        } catch (PDOException $e) {
            // تجاهل الأخطاء إذا كانت البيانات موجودة بالفعل
            if ($e->getCode() != 23000) { // 23000 = duplicate entry
                echo "⚠️ خطأ في إضافة الموظف التجريبي: " . $employee['name'] . "\n";
            }
        }
    }
    
    echo "✅ تم إضافة البيانات التجريبية بنجاح!\n";
    echo "✅ Données d'exemple ajoutées avec succès!\n\n";
    
    // عرض معلومات الجدول
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees");
    $count = $stmt->fetch()['total'];
    
    echo "📊 إحصائيات الجدول / Statistiques de la table:\n";
    echo "   - إجمالي الموظفين / Total des employés: $count\n";
    echo "   - الجدول جاهز للاستخدام / La table est prête à l'utilisation\n\n";
    
} catch (PDOException $e) {
    echo "❌ خطأ في إنشاء الجدول: " . $e->getMessage() . "\n";
    echo "❌ Erreur lors de la création de la table: " . $e->getMessage() . "\n";
}
?> 