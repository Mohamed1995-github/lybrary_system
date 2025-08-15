<?php
/* modules/administration/activity.php */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../public/login.php'); 
    exit; 
}

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';

// الحصول على الأنشطة الأخيرة
try {
    // إحصائيات عامة
    $stats = [];
    
    // عدد الكتب المضافة حديثاً
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM books WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_books'] = $stmt->fetch()['count'];
    
    // عدد المجلات المضافة حديثاً
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM books WHERE type = 'magazine' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_magazines'] = $stmt->fetch()['count'];
    
    // عدد الإعارات الحديثة
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM loans WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_loans'] = $stmt->fetch()['count'];
    
    // عدد الأعضاء الجدد
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM members WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_members'] = $stmt->fetch()['count'];
    
    // الأنشطة الأخيرة
    $recent_activities = [];
    
    // آخر الكتب المضافة
    $stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC LIMIT 5");
    $recent_books = $stmt->fetchAll();
    
    // آخر الإعارات
    $stmt = $pdo->query("SELECT l.*, m.name as member_name FROM loans l LEFT JOIN members m ON l.member_id = m.id ORDER BY l.created_at DESC LIMIT 5");
    $recent_loans = $stmt->fetchAll();
    
    // آخر الأعضاء
    $stmt = $pdo->query("SELECT * FROM members ORDER BY created_at DESC LIMIT 5");
    $recent_members = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $stats = [];
    $recent_books = [];
    $recent_loans = [];
    $recent_members = [];
}

// الترجمات
$translations = [
    'ar' => [
        'title' => 'نشاط النظام',
        'recent_activity' => 'النشاط الأخير',
        'system_stats' => 'إحصائيات النظام',
        'recent_books' => 'الكتب المضافة حديثاً',
        'recent_magazines' => 'المجلات المضافة حديثاً',
        'recent_loans' => 'الإعارات الحديثة',
        'recent_members' => 'الأعضاء الجدد',
        'last_books' => 'آخر الكتب المضافة',
        'last_loans' => 'آخر الإعارات',
        'last_members' => 'آخر الأعضاء',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'back_to_administration' => 'العودة للإدارة',
        'no_books' => 'لا توجد كتب مضافة حديثاً',
        'no_loans' => 'لا توجد إعارات حديثة',
        'no_members' => 'لا يوجد أعضاء جدد',
        'added_on' => 'تم الإضافة في',
        'borrowed_on' => 'تم الإعارة في',
        'joined_on' => 'انضم في',
        'days_ago' => 'أيام مضت',
        'hours_ago' => 'ساعات مضت',
        'minutes_ago' => 'دقائق مضت',
        'just_now' => 'الآن'
    ],
    'fr' => [
        'title' => 'Activité du Système',
        'recent_activity' => 'Activité Récente',
        'system_stats' => 'Statistiques du Système',
        'recent_books' => 'Livres Ajoutés Récemment',
        'recent_magazines' => 'Magazines Ajoutés Récemment',
        'recent_loans' => 'Emprunts Récents',
        'recent_members' => 'Nouveaux Membres',
        'last_books' => 'Derniers Livres Ajoutés',
        'last_loans' => 'Derniers Emprunts',
        'last_members' => 'Derniers Membres',
        'back_to_dashboard' => 'Retour au Tableau de Bord',
        'back_to_administration' => 'Retour à l\'Administration',
        'no_books' => 'Aucun livre ajouté récemment',
        'no_loans' => 'Aucun emprunt récent',
        'no_members' => 'Aucun nouveau membre',
        'added_on' => 'Ajouté le',
        'borrowed_on' => 'Emprunté le',
        'joined_on' => 'Rejoint le',
        'days_ago' => 'jours',
        'hours_ago' => 'heures',
        'minutes_ago' => 'minutes',
        'just_now' => 'Maintenant'
    ]
];

$t = $translations[$lang];

// دالة لحساب الوقت المنقضي
function timeAgo($datetime, $lang) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return $lang == 'ar' ? 'الآن' : 'Maintenant';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' ' . ($lang == 'ar' ? 'دقائق مضت' : 'minutes');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ' . ($lang == 'ar' ? 'ساعات مضت' : 'heures');
    } else {
        $days = floor($diff / 86400);
        return $days . ' ' . ($lang == 'ar' ? 'أيام مضت' : 'jours');
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?> - Library System</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .activity-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        
        .stat-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .activity-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        
        .activity-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-title {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }
        
        .navigation-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .nav-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        
        .nav-btn.secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .nav-btn.secondary:hover {
            background: var(--bg-primary);
        }
    </style>
</head>
<body>
    <div class="activity-container">
        <!-- Navigation -->
        <div class="navigation-buttons">
            <a href="../../public/dashboard.php?lang=<?=$lang?>" class="nav-btn">
                <i class="fas fa-home"></i>
                <?= $t['back_to_dashboard'] ?>
            </a>
            <a href="../../public/router.php?module=administration&action=list&lang=<?=$lang?>" class="nav-btn secondary">
                <i class="fas fa-users"></i>
                <?= $t['back_to_administration'] ?>
            </a>
        </div>
        
        <h1><?= $t['title'] ?></h1>
        
        <!-- System Statistics -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?= $stats['recent_books'] ?? 0 ?></div>
                <div class="stat-label"><?= $t['recent_books'] ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="stat-number"><?= $stats['recent_magazines'] ?? 0 ?></div>
                <div class="stat-label"><?= $t['recent_magazines'] ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-number"><?= $stats['recent_loans'] ?? 0 ?></div>
                <div class="stat-label"><?= $t['recent_loans'] ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-number"><?= $stats['recent_members'] ?? 0 ?></div>
                <div class="stat-label"><?= $t['recent_members'] ?></div>
            </div>
        </section>
        
        <!-- Recent Activities -->
        <section class="activity-grid">
            <!-- Recent Books -->
            <div class="activity-card">
                <div class="activity-header">
                    <i class="fas fa-book"></i>
                    <span><?= $t['last_books'] ?></span>
                </div>
                
                <?php if (empty($recent_books)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p><?= $t['no_books'] ?></p>
                    </div>
                <?php else: ?>
                    <ul class="activity-list">
                        <?php foreach ($recent_books as $book): ?>
                            <li class="activity-item">
                                <div>
                                    <div class="activity-title"><?= htmlspecialchars($book['title']) ?></div>
                                    <div class="activity-time">
                                        <?= $t['added_on'] ?> <?= timeAgo($book['created_at'], $lang) ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <!-- Recent Loans -->
            <div class="activity-card">
                <div class="activity-header">
                    <i class="fas fa-handshake"></i>
                    <span><?= $t['last_loans'] ?></span>
                </div>
                
                <?php if (empty($recent_loans)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p><?= $t['no_loans'] ?></p>
                    </div>
                <?php else: ?>
                    <ul class="activity-list">
                        <?php foreach ($recent_loans as $loan): ?>
                            <li class="activity-item">
                                <div>
                                    <div class="activity-title"><?= htmlspecialchars($loan['member_name'] ?? 'عضو غير محدد') ?></div>
                                    <div class="activity-time">
                                        <?= $t['borrowed_on'] ?> <?= timeAgo($loan['created_at'], $lang) ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <!-- Recent Members -->
            <div class="activity-card">
                <div class="activity-header">
                    <i class="fas fa-user-plus"></i>
                    <span><?= $t['last_members'] ?></span>
                </div>
                
                <?php if (empty($recent_members)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p><?= $t['no_members'] ?></p>
                    </div>
                <?php else: ?>
                    <ul class="activity-list">
                        <?php foreach ($recent_members as $member): ?>
                            <li class="activity-item">
                                <div>
                                    <div class="activity-title"><?= htmlspecialchars($member['name']) ?></div>
                                    <div class="activity-time">
                                        <?= $t['joined_on'] ?> <?= timeAgo($member['created_at'], $lang) ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>
