
<?php
require_once __DIR__ . '/../../../config/db.php';
require_once '../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';

// Récupérer les statistiques
try {
    // Statistiques des emprunteurs
    $borrowers_count = $pdo->query("SELECT COUNT(*) as total FROM borrowers")->fetch()['total'];
    $active_borrowers = $pdo->query("SELECT COUNT(DISTINCT borrower_id) as total FROM loans WHERE status = 'active'")->fetch()['total'];
    
    // Statistiques des emprunts
    $total_loans = $pdo->query("SELECT COUNT(*) as total FROM loans")->fetch()['total'];
    $active_loans = $pdo->query("SELECT COUNT(*) as total FROM loans WHERE status = 'active'")->fetch()['total'];
    $overdue_loans = $pdo->query("SELECT COUNT(*) as total FROM loans WHERE status = 'active' AND due_date < NOW()")->fetch()['total'];
    $returned_loans = $pdo->query("SELECT COUNT(*) as total FROM loans WHERE status = 'returned'")->fetch()['total'];
    
} catch (PDOException $e) {
    $borrowers_count = $active_borrowers = $total_loans = $active_loans = $overdue_loans = $returned_loans = 0;
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'قسم الإعارة والإسترجاع',
        'subtitle' => 'إدارة المستعيرين والإعارات',
        'borrowers_management' => 'إدارة المستعيرين',
        'borrowers_desc' => 'إدارة بيانات المستعيرين',
        'add_borrower' => 'إضافة مستعير جديد',
        'list_borrowers' => 'قائمة المستعيرين',
        'loans_management' => 'إدارة الإعارات',
        'loans_desc' => 'تتبع الإعارات والعوائد',
        'add_loan' => 'إضافة إعارة جديدة',
        'list_loans' => 'قائمة الإعارات',
        'active_loans' => 'الإعارات النشطة',
        'overdue_loans' => 'الإعارات المتأخرة',
        'return_loan' => 'إرجاع إعارة',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'total_borrowers' => 'إجمالي المستعيرين',
        'active_borrowers' => 'المستعيرين النشطين',
        'total_loans' => 'إجمالي الإعارات',
        'active_loans' => 'الإعارات النشطة',
        'overdue_loans' => 'الإعارات المتأخرة',
        'returned_loans' => 'الإعارات المرجعة'
    ],
    'fr' => [
        'title' => 'Section Prêt et Retour',
        'subtitle' => 'Gestion des emprunteurs et emprunts',
        'borrowers_management' => 'Gestion des Emprunteurs',
        'borrowers_desc' => 'Gérer les données des emprunteurs',
        'add_borrower' => 'Ajouter un emprunteur',
        'list_borrowers' => 'Liste des emprunteurs',
        'loans_management' => 'Gestion des Emprunts',
        'loans_desc' => 'Suivre les emprunts et retours',
        'add_loan' => 'Ajouter un emprunt',
        'list_loans' => 'Liste des emprunts',
        'active_loans' => 'Emprunts actifs',
        'overdue_loans' => 'Emprunts en retard',
        'return_loan' => 'Retour d\'emprunt',
        'back_to_dashboard' => 'Retour au tableau de bord',
        'total_borrowers' => 'Total des emprunteurs',
        'active_borrowers' => 'Emprunteurs actifs',
        'total_loans' => 'Total des emprunts',
        'active_loans' => 'Emprunts actifs',
        'overdue_loans' => 'Emprunts en retard',
        'returned_loans' => 'Emprunts retournés'
    ]
];

$t = $translations[$lang];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: white !important;
            background: white !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        html {
            background-color: white !important;
            background: white !important;
        }
        * {
            box-sizing: border-box;
        }
        .container, .stats-grid, .stat-card, .modules-grid, .module-card, .quick-actions {
            background-color: white !important;
            background: white !important;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 2rem;
        }
        .header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
        }
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        .module-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.15);
        }
        .module-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .module-header h3 {
            margin: 0;
            font-size: 1.25rem;
        }
        .module-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .module-actions {
            padding: 1.5rem;
        }
        .action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .action-btn:hover {
            background: #667eea;
            color: white;
        }
        .action-btn.primary {
            background: #667eea;
            color: white;
        }
        .action-btn.primary:hover {
            background: #5a67d8;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #667eea;
            text-decoration: none;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .back-link:hover {
            color: #5a67d8;
        }
        .quick-actions {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .quick-actions h3 {
            margin: 0 0 1rem 0;
            color: #374151;
        }
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background: #f8fafc;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .quick-action-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .quick-action-btn.warning {
            background: #fef3c7;
            color: #92400e;
        }
        .quick-action-btn.warning:hover {
            background: #f59e0b;
            color: white;
        }
    </style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/professional-theme.css">
</head>
<body>
    <div class="container">
        <a href="../../dashboard.php?lang=<?= $lang ?>" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <?= $t['back_to_dashboard'] ?>
        </a>
        
        <div class="header">
            <h1>
                <i class="fas fa-book-reader"></i>
                <?= $t['title'] ?>
            </h1>
            <p><?= $t['subtitle'] ?></p>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $borrowers_count ?></div>
                <div class="stat-label"><?= $t['total_borrowers'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $active_borrowers ?></div>
                <div class="stat-label"><?= $t['active_borrowers'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_loans ?></div>
                <div class="stat-label"><?= $t['total_loans'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $active_loans ?></div>
                <div class="stat-label"><?= $t['active_loans'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #f59e0b;"><?= $overdue_loans ?></div>
                <div class="stat-label"><?= $t['overdue_loans'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #10b981;"><?= $returned_loans ?></div>
                <div class="stat-label"><?= $t['returned_loans'] ?></div>
            </div>
        </div>

        <!-- إجراءات سريعة -->
        <div class="quick-actions">
            <h3>
                <i class="fas fa-bolt"></i>
                <?= $lang == 'ar' ? 'إجراءات سريعة' : 'Actions rapides' ?>
            </h3>
            <div class="quick-actions-grid">
                <a href="borrowers/add.php?lang=<?=$lang?>" class="quick-action-btn">
                    <i class="fas fa-user-plus"></i>
                    <?= $t['add_borrower'] ?>
                </a>
                <a href="add.php?lang=<?=$lang?>" class="quick-action-btn">
                    <i class="fas fa-book-medical"></i>
                    <?= $t['add_loan'] ?>
                </a>
                <a href="list.php?status=active&lang=<?=$lang?>" class="quick-action-btn">
                    <i class="fas fa-book-open"></i>
                    <?= $t['active_loans'] ?>
                </a>
                <a href="list.php?status=overdue&lang=<?=$lang?>" class="quick-action-btn warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= $t['overdue_loans'] ?>
                </a>
            </div>
        </div>

        <!-- وحدات الإدارة -->
        <div class="modules-grid">
            <!-- إدارة المستعيرين -->
            <div class="module-card">
                <div class="module-header">
                    <h3>
                        <i class="fas fa-users"></i>
                        <?= $t['borrowers_management'] ?>
                    </h3>
                    <p><?= $t['borrowers_desc'] ?></p>
                </div>
                <div class="module-actions">
                    <div class="action-grid">
                        <a href="borrowers/add.php?lang=<?=$lang?>" class="action-btn primary">
                            <i class="fas fa-plus"></i>
                            <?= $t['add_borrower'] ?>
                        </a>
                        <a href="borrowers/list.php?lang=<?=$lang?>" class="action-btn">
                            <i class="fas fa-list"></i>
                            <?= $t['list_borrowers'] ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- إدارة الإعارات -->
            <div class="module-card">
                <div class="module-header">
                    <h3>
                        <i class="fas fa-book-reader"></i>
                        <?= $t['loans_management'] ?>
                    </h3>
                    <p><?= $t['loans_desc'] ?></p>
                </div>
                <div class="module-actions">
                    <div class="action-grid">
                        <a href="add.php?lang=<?=$lang?>" class="action-btn primary">
                            <i class="fas fa-plus"></i>
                            <?= $t['add_loan'] ?>
                        </a>
                        <a href="list.php?lang=<?=$lang?>" class="action-btn">
                            <i class="fas fa-list"></i>
                            <?= $t['list_loans'] ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>
