<?php
/* modules/administration/reports.php */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';

// الحصول على التقارير والإحصائيات
try {
    // إحصائيات العناصر
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_items FROM items");
    $stmt->execute();
    $total_items = $stmt->fetch()['total_items'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_books FROM items WHERE type = 'book'");
    $stmt->execute();
    $total_books = $stmt->fetch()['total_books'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_magazines FROM items WHERE type = 'magazine'");
    $stmt->execute();
    $total_magazines = $stmt->fetch()['total_magazines'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_newspapers FROM items WHERE type = 'newspaper'");
    $stmt->execute();
    $total_newspapers = $stmt->fetch()['total_newspapers'];

    // إحصائيات النسخ
    $stmt = $pdo->prepare("SELECT SUM(copies) as total_copies, SUM(available_copies) as available_copies FROM items");
    $stmt->execute();
    $copies_stats = $stmt->fetch();
    $total_copies = $copies_stats['total_copies'] ?? 0;
    $available_copies = $copies_stats['available_copies'] ?? 0;
    $borrowed_copies = $total_copies - $available_copies;

    // إحصائيات الموظفين
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_employees FROM employees");
    $stmt->execute();
    $total_employees = $stmt->fetch()['total_employees'];

    // إحصائيات المستعيرين
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_borrowers FROM members");
    $stmt->execute();
    $total_borrowers = $stmt->fetch()['total_borrowers'];

    // إحصائيات الإعارات
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_loans FROM loans");
    $stmt->execute();
    $total_loans = $stmt->fetch()['total_loans'];

    // أحدث العناصر المضافة
    $stmt = $pdo->prepare("SELECT * FROM items ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $recent_items = $stmt->fetchAll();

    // أحدث الموظفين المضافين
    $stmt = $pdo->prepare("SELECT * FROM employees ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $recent_employees = $stmt->fetchAll();

    // أحدث الإعارات
    $stmt = $pdo->prepare("SELECT * FROM loans ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $recent_loans = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Database error in reports.php: " . $e->getMessage());
    $has_error = true;
    $error_message = $e->getMessage();
    
    // تعيين قيم افتراضية
    $total_items = $total_books = $total_magazines = $total_newspapers = 0;
    $total_copies = $available_copies = $borrowed_copies = 0;
    $total_employees = $total_borrowers = $total_loans = 0;
    $recent_items = $recent_employees = $recent_loans = [];
}

// ترجمة النصوص
$translations = [
    'ar' => [
        'title' => 'التقارير والإحصائيات',
        'system_overview' => 'نظرة عامة على النظام',
        'items_statistics' => 'إحصائيات العناصر',
        'employees_statistics' => 'إحصائيات الموظفين',
        'loans_statistics' => 'إحصائيات الإعارات',
        'recent_activities' => 'النشاطات الأخيرة',
        'total_items' => 'إجمالي العناصر',
        'total_books' => 'إجمالي الكتب',
        'total_magazines' => 'إجمالي المجلات',
        'total_newspapers' => 'إجمالي الجرائد',
        'total_copies' => 'إجمالي النسخ',
        'available_copies' => 'النسخ المتاحة',
        'borrowed_copies' => 'النسخ المعارة',
        'total_employees' => 'إجمالي الموظفين',
        'total_borrowers' => 'إجمالي المستعيرين',
        'total_loans' => 'إجمالي الإعارات',
        'recent_items' => 'أحدث العناصر المضافة',
        'recent_employees' => 'أحدث الموظفين المضافين',
        'recent_loans' => 'أحدث الإعارات',
        'no_data' => 'لا توجد بيانات',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'back_to_administration' => 'العودة للإدارة',
        'item_title' => 'العنوان',
        'item_type' => 'النوع',
        'employee_name' => 'اسم الموظف',
        'employee_role' => 'الدور',
        'loan_date' => 'تاريخ الإعارة',
        'return_date' => 'تاريخ الإرجاع',
        'status' => 'الحالة',
        'available' => 'متاح',
        'borrowed' => 'معار',
        'returned' => 'مُرجع'
    ],
    'fr' => [
        'title' => 'Rapports et Statistiques',
        'system_overview' => 'Aperçu du système',
        'items_statistics' => 'Statistiques des éléments',
        'employees_statistics' => 'Statistiques des employés',
        'loans_statistics' => 'Statistiques des emprunts',
        'recent_activities' => 'Activités récentes',
        'total_items' => 'Total des éléments',
        'total_books' => 'Total des livres',
        'total_magazines' => 'Total des revues',
        'total_newspapers' => 'Total des journaux',
        'total_copies' => 'Total des copies',
        'available_copies' => 'Copies disponibles',
        'borrowed_copies' => 'Copies empruntées',
        'total_employees' => 'Total des employés',
        'total_borrowers' => 'Total des emprunteurs',
        'total_loans' => 'Total des emprunts',
        'recent_items' => 'Éléments récemment ajoutés',
        'recent_employees' => 'Employés récemment ajoutés',
        'recent_loans' => 'Emprunts récents',
        'no_data' => 'Aucune donnée',
        'back_to_dashboard' => 'Retour au tableau de bord',
        'back_to_administration' => 'Retour à l\'administration',
        'item_title' => 'Titre',
        'item_type' => 'Type',
        'employee_name' => 'Nom de l\'employé',
        'employee_role' => 'Rôle',
        'loan_date' => 'Date d\'emprunt',
        'return_date' => 'Date de retour',
        'status' => 'Statut',
        'available' => 'Disponible',
        'borrowed' => 'Emprunté',
        'returned' => 'Retourné'
    ]
];

$t = $translations[$lang];
$has_error = false;
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?> - Library System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-secondary);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-icon {
            width: 3rem;
            height: 3rem;
            background: var(--primary-color);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .page-title h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .page-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-lg);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--bg-secondary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }

        .stat-icon.items { background: var(--primary-color); }
        .stat-icon.books { background: var(--success-color); }
        .stat-icon.magazines { background: var(--warning-color); }
        .stat-icon.newspapers { background: var(--error-color); }
        .stat-icon.employees { background: #8b5cf6; }
        .stat-icon.loans { background: #06b6d4; }

        .stat-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .stat-description {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .report-section {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .data-table th,
        .data-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            background: var(--bg-secondary);
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .data-table tr:hover {
            background: var(--bg-secondary);
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-available {
            background: #dcfce7;
            color: #166534;
        }

        .status-borrowed {
            background: #fef3c7;
            color: #92400e;
        }

        .status-returned {
            background: #dbeafe;
            color: #1e40af;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .reports-grid {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                font-size: 0.875rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.5rem 0.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- رسالة الخطأ -->
        <?php if ($has_error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-triangle"></i>
                <?= $lang == 'ar' ? 'حدث خطأ في قاعدة البيانات' : 'Erreur de base de données' ?>
            </div>
        <?php endif; ?>
        
        <!-- رأس الصفحة -->
        <div class="page-header">
            <div class="page-title">
                <div class="page-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1><?= $t['title'] ?></h1>
            </div>
            
            <div class="page-actions">
                <a href="../../public/dashboard.php?lang=<?=$lang?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <?= $t['back_to_dashboard'] ?>
                </a>
                <a href="../../public/router.php?module=administration&action=list&lang=<?=$lang?>" class="btn btn-primary">
                    <i class="fas fa-users-cog"></i>
                    <?= $t['back_to_administration'] ?>
                </a>
            </div>
        </div>

        <!-- نظرة عامة على النظام -->
        <div class="report-section">
            <h2 class="section-title">
                <i class="fas fa-chart-pie"></i>
                <?= $t['system_overview'] ?>
            </h2>
            
            <div class="stats-grid">
                <!-- إحصائيات العناصر -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon items">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-title"><?= $t['total_items'] ?></div>
                    </div>
                    <div class="stat-value"><?= $total_items ?></div>
                    <div class="stat-description"><?= $t['items_statistics'] ?></div>
                </div>

                <!-- إحصائيات الكتب -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon books">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-title"><?= $t['total_books'] ?></div>
                    </div>
                    <div class="stat-value"><?= $total_books ?></div>
                    <div class="stat-description"><?= $t['total_books'] ?></div>
                </div>

                <!-- إحصائيات المجلات -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon magazines">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="stat-title"><?= $t['total_magazines'] ?></div>
                    </div>
                    <div class="stat-value"><?= $total_magazines ?></div>
                    <div class="stat-description"><?= $t['total_magazines'] ?></div>
                </div>

                <!-- إحصائيات الجرائد -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon newspapers">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="stat-title"><?= $t['total_newspapers'] ?></div>
                    </div>
                    <div class="stat-value"><?= $total_newspapers ?></div>
                    <div class="stat-description"><?= $t['total_newspapers'] ?></div>
                </div>

                <!-- إحصائيات النسخ -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon items">
                            <i class="fas fa-copy"></i>
                        </div>
                        <div class="stat-title"><?= $t['total_copies'] ?></div>
                    </div>
                    <div class="stat-value"><?= $total_copies ?></div>
                    <div class="stat-description"><?= $t['available_copies'] ?>: <?= $available_copies ?> | <?= $t['borrowed_copies'] ?>: <?= $borrowed_copies ?></div>
                </div>

                <!-- إحصائيات الموظفين -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon employees">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-title"><?= $t['total_employees'] ?></div>
                    </div>
                    <div class="stat-value"><?= $total_employees ?></div>
                    <div class="stat-description"><?= $t['employees_statistics'] ?></div>
                </div>

                <!-- إحصائيات المستعيرين -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon employees">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-title"><?= $t['total_borrowers'] ?></div>
                    </div>
                    <div class="stat-value"><?= $total_borrowers ?></div>
                    <div class="stat-description"><?= $t['total_borrowers'] ?></div>
                </div>

                <!-- إحصائيات الإعارات -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon loans">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="stat-title"><?= $t['total_loans'] ?></div>
                    </div>
                    <div class="stat-value"><?= $total_loans ?></div>
                    <div class="stat-description"><?= $t['loans_statistics'] ?></div>
                </div>
            </div>
        </div>

        <!-- النشاطات الأخيرة -->
        <div class="reports-grid">
            <!-- أحدث العناصر -->
            <div class="report-section">
                <h3 class="section-title">
                    <i class="fas fa-clock"></i>
                    <?= $t['recent_items'] ?>
                </h3>
                
                <?php if (empty($recent_items)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4><?= $t['no_data'] ?></h4>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= $t['item_title'] ?></th>
                                <th><?= $t['item_type'] ?></th>
                                <th><?= $t['status'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_items as $item): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($item['type']) ?></td>
                                    <td>
                                                                                 <?php
                                         $available = $item['available_copies'] ?? 0;
                                         $total = $item['copies'] ?? 0;
                                         
                                         if ($available == 0) {
                                            echo '<span class="status-badge status-borrowed">';
                                            echo '<i class="fas fa-times"></i>';
                                            echo $t['borrowed'];
                                            echo '</span>';
                                        } elseif ($available < $total) {
                                            echo '<span class="status-badge status-borrowed">';
                                            echo '<i class="fas fa-exclamation"></i>';
                                            echo $t['borrowed'];
                                            echo '</span>';
                                        } else {
                                            echo '<span class="status-badge status-available">';
                                            echo '<i class="fas fa-check"></i>';
                                            echo $t['available'];
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- أحدث الموظفين -->
            <div class="report-section">
                <h3 class="section-title">
                    <i class="fas fa-users"></i>
                    <?= $t['recent_employees'] ?>
                </h3>
                
                <?php if (empty($recent_employees)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <h4><?= $t['no_data'] ?></h4>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= $t['employee_name'] ?></th>
                                <th><?= $t['employee_role'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_employees as $employee): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($employee['name'] ?? 'N/A') ?></strong></td>
                                    <td><?= htmlspecialchars($employee['role'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- أحدث الإعارات -->
            <div class="report-section">
                <h3 class="section-title">
                    <i class="fas fa-exchange-alt"></i>
                    <?= $t['recent_loans'] ?>
                </h3>
                
                <?php if (empty($recent_loans)): ?>
                    <div class="empty-state">
                        <i class="fas fa-handshake"></i>
                        <h4><?= $t['no_data'] ?></h4>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= $t['loan_date'] ?></th>
                                <th><?= $t['return_date'] ?></th>
                                <th><?= $t['status'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_loans as $loan): ?>
                                <tr>
                                    <td><?= htmlspecialchars($loan['loan_date'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($loan['return_date'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php
                                        $return_date = $loan['return_date'] ?? null;
                                        if ($return_date) {
                                            echo '<span class="status-badge status-returned">';
                                            echo '<i class="fas fa-check"></i>';
                                            echo $t['returned'];
                                            echo '</span>';
                                        } else {
                                            echo '<span class="status-badge status-borrowed">';
                                            echo '<i class="fas fa-clock"></i>';
                                            echo $t['borrowed'];
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
