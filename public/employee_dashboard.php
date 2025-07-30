<?php
/* public/employee_dashboard.php */
require_once '../includes/auth.php';
require_once '../includes/permissions.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { 
    header('Location: login.php'); 
    exit; 
}

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';

// الحصول على معلومات الموظف الحالي
$currentEmployee = getCurrentEmployee();

// الحصول على القوائم والإجراءات المتاحة
$availableMenus = getAvailableMenus($lang);
$availableActions = getAvailableActions($lang);

// الحصول على إحصائيات سريعة حسب الحقوق
$stats = [];
if (hasPermission('إدارة الكتب') || hasPermission('Gestion des livres')) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE lang = ? AND type = 'book'");
        $stmt->execute([$lang]);
        $stats['books'] = $stmt->fetch()['count'];
    } catch (PDOException $e) {
        $stats['books'] = 0;
    }
}

if (hasPermission('إدارة المجلات') || hasPermission('Gestion des magazines')) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE lang = ? AND type = 'magazine'");
        $stmt->execute([$lang]);
        $stats['magazines'] = $stmt->fetch()['count'];
    } catch (PDOException $e) {
        $stats['magazines'] = 0;
    }
}

if (hasPermission('إدارة المستعيرين') || hasPermission('Gestion des emprunteurs')) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM borrowers WHERE lang = ?");
        $stmt->execute([$lang]);
        $stats['borrowers'] = $stmt->fetch()['count'];
    } catch (PDOException $e) {
        $stats['borrowers'] = 0;
    }
}

if (hasPermission('إدارة الإعارة') || hasPermission('Gestion des prêts')) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM loans WHERE lang = ? AND return_date IS NULL");
        $stmt->execute([$lang]);
        $stats['active_loans'] = $stmt->fetch()['count'];
    } catch (PDOException $e) {
        $stats['active_loans'] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'لوحة التحكم - ' : 'Tableau de bord - ' ?><?= $currentEmployee ? htmlspecialchars($currentEmployee['name']) : 'Employee' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .employee-header {
            background: linear-gradient(135deg, var(--primary-color), #1e40af);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .employee-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .employee-avatar {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .employee-details h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .employee-details p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .employee-rights {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .right-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .stat-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .menu-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        
        .menu-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .menu-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .menu-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgb(37 99 235 / 0.1);
            border-radius: 8px;
        }
        
        .menu-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            background: #1e40af;
            transform: translateY(-1px);
        }
        
        .no-access {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        
        .no-access i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }
        
        .logout-btn {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        @media (max-width: 768px) {
            .employee-info {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logout Button -->
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <?= $lang == 'ar' ? 'تسجيل الخروج' : 'Déconnexion' ?>
        </a>

        <!-- Employee Header -->
        <div class="employee-header">
            <div class="employee-info">
                <div class="employee-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="employee-details">
                    <h1><?= htmlspecialchars($currentEmployee['name']) ?></h1>
                    <p><?= htmlspecialchars($currentEmployee['function']) ?> - <?= htmlspecialchars($currentEmployee['number']) ?></p>
                </div>
            </div>
            
            <div class="employee-rights">
                <?php 
                $rights = explode(',', $currentEmployee['access_rights']);
                foreach ($rights as $right): 
                    $right = trim($right);
                    if (!empty($right)):
                ?>
                    <span class="right-badge"><?= htmlspecialchars($right) ?></span>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>

        <?php if (empty($availableMenus)): ?>
            <!-- No Access Message -->
            <div class="no-access">
                <i class="fas fa-lock"></i>
                <h2><?= $lang == 'ar' ? 'لا توجد صلاحيات' : 'Aucune permission' ?></h2>
                <p><?= $lang == 'ar' ? 'ليس لديك صلاحيات للوصول إلى أي قسم من النظام' : 'Vous n\'avez pas les permissions pour accéder à aucune section du système' ?></p>
            </div>
        <?php else: ?>
            <!-- Statistics -->
            <?php if (!empty($stats)): ?>
                <div class="stats-grid">
                    <?php if (isset($stats['books'])): ?>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-number"><?= $stats['books'] ?></div>
                            <div class="stat-label"><?= $lang == 'ar' ? 'الكتب' : 'Livres' ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($stats['magazines'])): ?>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="stat-number"><?= $stats['magazines'] ?></div>
                            <div class="stat-label"><?= $lang == 'ar' ? 'المجلات' : 'Revues' ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($stats['borrowers'])): ?>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number"><?= $stats['borrowers'] ?></div>
                            <div class="stat-label"><?= $lang == 'ar' ? 'المستعيرين' : 'Emprunteurs' ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($stats['active_loans'])): ?>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="stat-number"><?= $stats['active_loans'] ?></div>
                            <div class="stat-label"><?= $lang == 'ar' ? 'الإعارات النشطة' : 'Prêts actifs' ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Available Menus -->
            <h2 style="margin-bottom: 1rem; color: var(--text-primary);">
                <i class="fas fa-th-large"></i>
                <?= $lang == 'ar' ? 'الأقسام المتاحة' : 'Sections disponibles' ?>
            </h2>
            
            <div class="menu-grid">
                <?php foreach ($availableMenus as $key => $menu): ?>
                    <a href="<?= $menu['url'] ?>?lang=<?= $lang ?>" class="menu-card">
                        <div class="menu-header">
                            <div class="menu-icon">
                                <i class="<?= $menu['icon'] ?>"></i>
                            </div>
                            <h3 class="menu-title"><?= $menu['title'] ?></h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Quick Actions -->
            <?php if (!empty($availableActions)): ?>
                <h2 style="margin-bottom: 1rem; color: var(--text-primary);">
                    <i class="fas fa-bolt"></i>
                    <?= $lang == 'ar' ? 'إجراءات سريعة' : 'Actions rapides' ?>
                </h2>
                
                <div class="actions-grid">
                    <?php foreach ($availableActions as $key => $action): ?>
                        <a href="<?= $action['url'] ?>?lang=<?= $lang ?>" class="action-btn">
                            <i class="<?= $action['icon'] ?>"></i>
                            <?= $action['title'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html> 