<?php
/* modules/items/statistics.php */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';

// الحصول على إحصائيات العناصر
try {
    // إحصائيات الكتب
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(copies) as total_copies, SUM(available_copies) as available_copies FROM items WHERE type = 'book'");
    $stmt->execute();
    $books_stats = $stmt->fetch();

    // إحصائيات المجلات
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(copies) as total_copies, SUM(available_copies) as available_copies FROM items WHERE type = 'magazine'");
    $stmt->execute();
    $magazines_stats = $stmt->fetch();

    // إحصائيات الجرائد
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(copies) as total_copies, SUM(available_copies) as available_copies FROM items WHERE type = 'newspaper'");
    $stmt->execute();
    $newspapers_stats = $stmt->fetch();

    // إحصائيات عامة
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_items, SUM(copies) as total_copies, SUM(available_copies) as available_copies FROM items");
    $stmt->execute();
    $general_stats = $stmt->fetch();

    // أحدث العناصر المضافة
    $stmt = $pdo->prepare("SELECT * FROM items ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $recent_items = $stmt->fetchAll();

} catch (PDOException $e) {
    // تسجيل الخطأ
    error_log("Database error in statistics.php: " . $e->getMessage());
    
    // تعيين متغيرات الخطأ
    $has_error = true;
    $error_message = $e->getMessage();
    
    // تعيين قيم افتراضية
    $books_stats = ['total' => 0, 'total_copies' => 0, 'available_copies' => 0];
    $magazines_stats = ['total' => 0, 'total_copies' => 0, 'available_copies' => 0];
    $newspapers_stats = ['total' => 0, 'total_copies' => 0, 'available_copies' => 0];
    $general_stats = ['total_items' => 0, 'total_copies' => 0, 'available_copies' => 0];
    $recent_items = [];
}

// ترجمة النصوص
$translations = [
    'ar' => [
        'title' => 'إحصائيات العناصر',
        'books' => 'الكتب',
        'magazines' => 'المجلات',
        'newspapers' => 'الجرائد',
        'total_items' => 'إجمالي العناصر',
        'total_copies' => 'إجمالي النسخ',
        'available_copies' => 'النسخ المتاحة',
        'borrowed_copies' => 'النسخ المعارة',
        'recent_items' => 'أحدث العناصر المضافة',
        'no_items' => 'لا توجد عناصر',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'back_to_list' => 'العودة للقائمة',
        'item_title' => 'العنوان',
        'item_type' => 'النوع',
        'item_status' => 'الحالة',
        'available' => 'متاح',
        'partially_available' => 'متاح جزئياً',
        'unavailable' => 'غير متاح'
    ],
    'fr' => [
        'title' => 'Statistiques des éléments',
        'books' => 'Livres',
        'magazines' => 'Revues',
        'newspapers' => 'Journaux',
        'total_items' => 'Total des éléments',
        'total_copies' => 'Total des copies',
        'available_copies' => 'Copies disponibles',
        'borrowed_copies' => 'Copies empruntées',
        'recent_items' => 'Éléments récemment ajoutés',
        'no_items' => 'Aucun élément',
        'back_to_dashboard' => 'Retour au tableau de bord',
        'back_to_list' => 'Retour à la liste',
        'item_title' => 'Titre',
        'item_type' => 'Type',
        'item_status' => 'Statut',
        'available' => 'Disponible',
        'partially_available' => 'Partiellement disponible',
        'unavailable' => 'Indisponible'
    ]
];

$t = $translations[$lang];

// متغير لتتبع وجود أخطاء
$has_error = false;
$error_message = '';
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
            max-width: 1200px;
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 2rem;
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
            margin-bottom: 1.5rem;
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.books {
            background: var(--primary-color);
        }

        .stat-icon.magazines {
            background: var(--success-color);
        }

        .stat-icon.newspapers {
            background: var(--warning-color);
        }

        .stat-icon.general {
            background: var(--error-color);
        }

        .stat-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .stat-numbers {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .recent-section {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .items-table th,
        .items-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .items-table th {
            background: var(--bg-secondary);
            font-weight: 600;
            color: var(--text-primary);
        }

        .items-table tr:hover {
            background: var(--bg-secondary);
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

        .status-partial {
            background: #fef3c7;
            color: #92400e;
        }

        .status-unavailable {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
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
            
            .stat-numbers {
                grid-template-columns: 1fr;
            }
            
            .items-table {
                font-size: 0.875rem;
            }
            
            .items-table th,
            .items-table td {
                padding: 0.75rem 0.5rem;
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
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h1><?= $t['title'] ?></h1>
            </div>
            
            <div class="page-actions">
                <a href="../../public/dashboard.php?lang=<?=$lang?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <?= $t['back_to_dashboard'] ?>
                </a>
                <a href="../../public/router.php?module=items&action=list&lang=<?=$lang?>" class="btn btn-primary">
                    <i class="fas fa-list"></i>
                    <?= $t['back_to_list'] ?>
                </a>
            </div>
        </div>

        <!-- شبكة الإحصائيات -->
        <div class="stats-grid">
            <!-- إحصائيات الكتب -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon books">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-title"><?= $t['books'] ?></div>
                </div>
                <div class="stat-numbers">
                    <div class="stat-item">
                        <div class="stat-value"><?= $books_stats['total'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['total_items'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $books_stats['total_copies'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['total_copies'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $books_stats['available_copies'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['available_copies'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= ($books_stats['total_copies'] ?? 0) - ($books_stats['available_copies'] ?? 0) ?></div>
                        <div class="stat-label"><?= $t['borrowed_copies'] ?></div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات المجلات -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon magazines">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-title"><?= $t['magazines'] ?></div>
                </div>
                <div class="stat-numbers">
                    <div class="stat-item">
                        <div class="stat-value"><?= $magazines_stats['total'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['total_items'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $magazines_stats['total_copies'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['total_copies'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $magazines_stats['available_copies'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['available_copies'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= ($magazines_stats['total_copies'] ?? 0) - ($magazines_stats['available_copies'] ?? 0) ?></div>
                        <div class="stat-label"><?= $t['borrowed_copies'] ?></div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات الجرائد -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon newspapers">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-title"><?= $t['newspapers'] ?></div>
                </div>
                <div class="stat-numbers">
                    <div class="stat-item">
                        <div class="stat-value"><?= $newspapers_stats['total'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['total_items'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $newspapers_stats['total_copies'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['total_copies'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $newspapers_stats['available_copies'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['available_copies'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= ($newspapers_stats['total_copies'] ?? 0) - ($newspapers_stats['available_copies'] ?? 0) ?></div>
                        <div class="stat-label"><?= $t['borrowed_copies'] ?></div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات عامة -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon general">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-title"><?= $t['total_items'] ?></div>
                </div>
                <div class="stat-numbers">
                    <div class="stat-item">
                        <div class="stat-value"><?= $general_stats['total_items'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['total_items'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $general_stats['total_copies'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['total_copies'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $general_stats['available_copies'] ?? 0 ?></div>
                        <div class="stat-label"><?= $t['available_copies'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= ($general_stats['total_copies'] ?? 0) - ($general_stats['available_copies'] ?? 0) ?></div>
                        <div class="stat-label"><?= $t['borrowed_copies'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- أحدث العناصر -->
        <div class="recent-section">
            <h2 class="section-title">
                <i class="fas fa-clock"></i>
                <?= $t['recent_items'] ?>
            </h2>
            
            <?php if (empty($recent_items)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3><?= $t['no_items'] ?></h3>
                </div>
            <?php else: ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th><?= $t['item_title'] ?></th>
                            <th><?= $t['item_type'] ?></th>
                            <th><?= $t['item_status'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_items as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                <td><?= htmlspecialchars($item['type']) ?></td>
                                <td>
                                    <?php
                                    $available = $item['available_copies'];
                                    $total = $item['copies'];
                                    
                                    if ($available == 0) {
                                        echo '<span class="status-badge status-unavailable">';
                                        echo '<i class="fas fa-times"></i>';
                                        echo $t['unavailable'];
                                        echo '</span>';
                                    } elseif ($available < $total) {
                                        echo '<span class="status-badge status-partial">';
                                        echo '<i class="fas fa-exclamation"></i>';
                                        echo $t['partially_available'];
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
    </div>
</body>
</html>
