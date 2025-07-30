<?php
/* modules/administration/permissions_guide.php */
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/permissions.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

// التحقق من صلاحية الوصول
if (!hasPermission('إدارة الموظفين') && !hasPermission('Gestion des employés')) {
    header('Location: list.php?lang=' . ($_GET['lang'] ?? 'ar'));
    exit;
}

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';

// تعريف مستويات الوصول
$accessLevels = [
    'admin' => [
        'ar' => 'مدير',
        'fr' => 'Administrateur',
        'description_ar' => 'صلاحيات كاملة على جميع أجزاء النظام',
        'description_fr' => 'Permissions complètes sur toutes les parties du système'
    ],
    'librarian' => [
        'ar' => 'أمين المكتبة',
        'fr' => 'Bibliothécaire',
        'description_ar' => 'إدارة الكتب والمجلات والصحف والتزويد والمستعيرين والإعارة',
        'description_fr' => 'Gestion des livres, revues, journaux, approvisionnements, emprunteurs et prêts'
    ],
    'assistant' => [
        'ar' => 'مساعد أمين المكتبة',
        'fr' => 'Assistant bibliothécaire',
        'description_ar' => 'إدارة الكتب والمجلات والصحف والمستعيرين والإعارة',
        'description_fr' => 'Gestion des livres, revues, journaux, emprunteurs et prêts'
    ],
    'reception' => [
        'ar' => 'موظف الاستقبال',
        'fr' => 'Employé d\'accueil',
        'description_ar' => 'إدارة المستعيرين والإعارة وعرض الكتب والمجلات والصحف',
        'description_fr' => 'Gestion des emprunteurs et prêts, consultation des livres, revues et journaux'
    ],
    'technical' => [
        'ar' => 'موظف تقني',
        'fr' => 'Employé technique',
        'description_ar' => 'إدارة النظام وعرض الكتب والمجلات والصحف',
        'description_fr' => 'Administration du système, consultation des livres, revues et journaux'
    ],
    'limited' => [
        'ar' => 'صلاحيات محدودة',
        'fr' => 'Permissions limitées',
        'description_ar' => 'عرض الكتب والمجلات والصحف فقط',
        'description_fr' => 'Consultation des livres, revues et journaux uniquement'
    ]
];

// الحصول على الصلاحيات لكل مستوى
$permissionsByLevel = [];
foreach ($accessLevels as $level => $info) {
    $permissionsByLevel[$level] = getRestrictedPermissions($level);
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'دليل الصلاحيات' : 'Guide des permissions' ?> - Library System</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .permission-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .permission-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .level-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }
        
        .admin-icon { background: linear-gradient(135deg, #dc3545, #c82333); }
        .librarian-icon { background: linear-gradient(135deg, #007bff, #0056b3); }
        .assistant-icon { background: linear-gradient(135deg, #28a745, #1e7e34); }
        .reception-icon { background: linear-gradient(135deg, #ffc107, #e0a800); }
        .technical-icon { background: linear-gradient(135deg, #6f42c1, #5a2d91); }
        .limited-icon { background: linear-gradient(135deg, #6c757d, #545b62); }
        
        .level-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        .level-description {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin: 0.25rem 0 0 0;
        }
        
        .permissions-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .permissions-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .permissions-list li:last-child {
            border-bottom: none;
        }
        
        .permission-icon {
            color: var(--primary-color);
            font-size: 0.875rem;
        }
        
        .comparison-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .comparison-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .comparison-table th,
        .comparison-table td {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .comparison-table th {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        .comparison-table td:first-child {
            text-align: left;
            font-weight: 500;
        }
        
        .permission-yes {
            color: #28a745;
            font-weight: 600;
        }
        
        .permission-no {
            color: #dc3545;
            font-weight: 600;
        }
        
        .permission-limited {
            color: #ffc107;
            font-weight: 600;
        }
        
        .info-section {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: 1px solid #2196f3;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-section h3 {
            color: #1976d2;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-section ul {
            margin: 1rem 0 0 0;
            padding-left: 1.5rem;
        }
        
        .info-section li {
            margin-bottom: 0.5rem;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="header-left">
                    <a href="list.php?lang=<?=$lang?>" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        <?= $lang == 'ar' ? 'العودة لقائمة الموظفين' : 'Retour à la liste des employés' ?>
                    </a>
                </div>
                <div>
                    <h1><?= $lang == 'ar' ? 'دليل الصلاحيات' : 'Guide des permissions' ?></h1>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                        <?= $lang == 'ar' ? 'نظرة عامة على مستويات الوصول والصلاحيات المتاحة' : 'Aperçu des niveaux d\'accès et des permissions disponibles' ?>
                    </p>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="main-content">
            <!-- Information Section -->
            <div class="info-section">
                <h3>
                    <i class="fas fa-info-circle"></i>
                    <?= $lang == 'ar' ? 'معلومات مهمة' : 'Informations importantes' ?>
                </h3>
                <ul>
                    <li><?= $lang == 'ar' ? 'يتم تطبيق الصلاحيات تلقائياً حسب الوظيفة المختارة عند إضافة موظف جديد' : 'Les permissions sont appliquées automatiquement selon la fonction choisie lors de l\'ajout d\'un nouvel employé' ?></li>
                    <li><?= $lang == 'ar' ? 'يمكن للمدير تعديل الصلاحيات يدوياً بعد إنشاء الموظف' : 'L\'administrateur peut modifier les permissions manuellement après la création de l\'employé' ?></li>
                    <li><?= $lang == 'ar' ? 'بعض الصلاحيات قد تكون مقيدة حسب مستوى الوصول' : 'Certaines permissions peuvent être restreintes selon le niveau d\'accès' ?></li>
                    <li><?= $lang == 'ar' ? 'يتم التحقق من الصلاحيات في كل صفحة لضمان الأمان' : 'Les permissions sont vérifiées sur chaque page pour assurer la sécurité' ?></li>
                </ul>
            </div>

            <!-- Permissions Grid -->
            <h2 style="margin-bottom: 1rem; color: var(--text-primary);">
                <i class="fas fa-shield-alt"></i>
                <?= $lang == 'ar' ? 'مستويات الوصول والصلاحيات' : 'Niveaux d\'accès et permissions' ?>
            </h2>
            
            <div class="permissions-grid">
                <?php foreach ($accessLevels as $level => $info): ?>
                    <div class="permission-card">
                        <div class="permission-header">
                            <div class="level-icon <?= $level ?>-icon">
                                <i class="fas fa-<?= $level == 'admin' ? 'crown' : ($level == 'librarian' ? 'book' : ($level == 'assistant' ? 'user-graduate' : ($level == 'reception' ? 'user-tie' : ($level == 'technical' ? 'cogs' : 'user')))) ?>"></i>
                            </div>
                            <div>
                                <h3 class="level-title"><?= $info[$lang] ?></h3>
                                <p class="level-description"><?= $info['description_' . $lang] ?></p>
                            </div>
                        </div>
                        
                        <ul class="permissions-list">
                            <?php 
                            $permissions = $permissionsByLevel[$level][$lang] ?? [];
                            if (!empty($permissions)):
                                foreach ($permissions as $permission):
                            ?>
                                <li>
                                    <i class="fas fa-check permission-icon"></i>
                                    <?= htmlspecialchars($permission) ?>
                                </li>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <li style="color: var(--text-secondary); font-style: italic;">
                                    <i class="fas fa-ban permission-icon"></i>
                                    <?= $lang == 'ar' ? 'لا توجد صلاحيات خاصة' : 'Aucune permission spéciale' ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Comparison Table -->
            <h2 style="margin-bottom: 1rem; color: var(--text-primary);">
                <i class="fas fa-table"></i>
                <?= $lang == 'ar' ? 'جدول مقارنة الصلاحيات' : 'Tableau comparatif des permissions' ?>
            </h2>
            
            <div class="comparison-table">
                <table>
                    <thead>
                        <tr>
                            <th><?= $lang == 'ar' ? 'الصلاحية' : 'Permission' ?></th>
                            <?php foreach ($accessLevels as $level => $info): ?>
                                <th><?= $info[$lang] ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $lang == 'ar' ? 'إدارة الكتب' : 'Gestion des livres' ?></td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                        </tr>
                        <tr>
                            <td><?= $lang == 'ar' ? 'إدارة المجلات' : 'Gestion des magazines' ?></td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                        </tr>
                        <tr>
                            <td><?= $lang == 'ar' ? 'إدارة الصحف' : 'Gestion des journaux' ?></td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                        </tr>
                        <tr>
                            <td><?= $lang == 'ar' ? 'إدارة التزويد' : 'Gestion des approvisionnements' ?></td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                        </tr>
                        <tr>
                            <td><?= $lang == 'ar' ? 'إدارة المستعيرين' : 'Gestion des emprunteurs' ?></td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                        </tr>
                        <tr>
                            <td><?= $lang == 'ar' ? 'إدارة الإعارة' : 'Gestion des prêts' ?></td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                        </tr>
                        <tr>
                            <td><?= $lang == 'ar' ? 'إدارة الموظفين' : 'Gestion des employés' ?></td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                        </tr>
                        <tr>
                            <td><?= $lang == 'ar' ? 'إدارة النظام' : 'Administration du système' ?></td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-no">✗</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-no">✗</td>
                        </tr>
                        <tr>
                            <td><?= $lang == 'ar' ? 'عرض الكتب' : 'Consultation des livres' ?></td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                            <td class="permission-yes">✓</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <a href="add.php?lang=<?=$lang?>" class="btn-primary">
                    <i class="fas fa-user-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un nouvel employé' ?>
                </a>
                <a href="list.php?lang=<?=$lang?>" class="action-btn btn-secondary">
                    <i class="fas fa-list"></i>
                    <?= $lang == 'ar' ? 'قائمة الموظفين' : 'Liste des employés' ?>
                </a>
            </div>
        </main>
    </div>

    <script src="../../public/assets/js/script.js"></script>
</body>
</html> 