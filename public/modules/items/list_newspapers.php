<?php
require_once __DIR__ . '/../../../config/db.php';
require_once '../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';

// Paramètres de pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Récupérer les journaux groupés par type
try {
    // Compter le total
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM items WHERE type = 'newspaper' AND lang = ?");
    $count_stmt->execute([$lang]);
    $total_newspapers = $count_stmt->fetch()['total'];
    
    // Récupérer tous les journaux ordonnés par date de création (plus récent en premier)
    // Groupés par newspaper_type (basé sur la méthode d'ajout)
    $stmt = $pdo->prepare("
        SELECT id, title, issue_from, issue_to, newspaper_date, missing_issues, 
               box_number, cabinet_number, shelf_number, drawer_number, newspaper_type, created_at 
        FROM items 
        WHERE type = 'newspaper' AND lang = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$lang]);
    $all_newspapers = $stmt->fetchAll();
    
    // Grouper les journaux par newspaper_type (basé sur la méthode d'ajout)
    $newspapers_by_type = [
        'popular' => [],
        'official' => []
    ];
    
    foreach ($all_newspapers as $newspaper) {
        $type = $newspaper['newspaper_type'] ?? null;
        
        // Grouper selon le champ newspaper_type
        if ($type === 'popular') {
            $newspapers_by_type['popular'][] = $newspaper;
        } elseif ($type === 'official') {
            $newspapers_by_type['official'][] = $newspaper;
        }
    }
    
    $total_pages = 1; // Pas de pagination pour l'instant
    
} catch (PDOException $e) {
    $newspapers_by_type = [];
    $total_newspapers = 0;
    $total_pages = 0;
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'الجرائد',
        'subtitle' => 'قائمة الجرائد',
        'total_newspapers' => 'إجمالي الجرائد',
        'add_newspaper' => 'إضافة جريدة',
        'newspaper_name' => 'اسم الجريدة',
        'issues' => 'الأعداد',
        'issue_from' => 'من',
        'issue_to' => 'إلى',
        'newspaper_date' => 'التاريخ',
        'missing_issues' => 'الأعداد الناقصة',
        'location' => 'الموقع',
        'box' => 'علبة',
        'cabinet' => 'خزانة',
        'shelf' => 'رف',
        'drawer' => 'درج',
        'id' => 'المعرف',
        'date_added' => 'تاريخ الإضافة',
        'no_newspapers' => 'لا توجد جرائد',
        'no_newspapers_msg' => 'لم يتم إضافة أي جرائد بعد',
        'add_first_newspaper' => 'إضافة أول جريدة',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'shaab_newspaper' => 'جريدة الشعب',
        'official_newspaper' => 'الجريدة الرسمية',
        'total_issues' => 'إجمالي الإصدارات'
    ],
    'fr' => [
        'title' => 'Journaux',
        'subtitle' => 'Liste des journaux',
        'total_newspapers' => 'Total des journaux',
        'add_newspaper' => 'Ajouter un journal',
        'newspaper_name' => 'Nom du journal',
        'issues' => 'Numéros',
        'issue_from' => 'De',
        'issue_to' => 'À',
        'newspaper_date' => 'Date',
        'missing_issues' => 'Numéros manquants',
        'location' => 'Emplacement',
        'box' => 'Boîte',
        'cabinet' => 'Armoire',
        'shelf' => 'Étagère',
        'drawer' => 'Tiroir',
        'id' => 'ID',
        'date_added' => 'Date d\'ajout',
        'no_newspapers' => 'Aucun journal',
        'no_newspapers_msg' => 'Aucun journal n\'a été ajouté pour le moment',
        'add_first_newspaper' => 'Ajouter le premier journal',
        'back_to_dashboard' => 'Retour au tableau de bord',
        'shaab_newspaper' => 'Journal du Peuple',
        'official_newspaper' => 'Journal officiel',
        'total_issues' => 'Total des éditions'
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
        }
        html {
            background-color: white !important;
            background: white !important;
        }
        * {
            box-sizing: border-box;
        }
        .container, .list-container, .stats-bar, .newspapers-grid, .newspaper-card {
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
        .stats-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stats-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stats-label {
            color: #6b7280;
            font-size: 1rem;
        }
        .newspaper-section {
            margin-bottom: 3rem;
        }
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-header h2 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .newspapers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .no-newspapers-section {
            text-align: center;
            padding: 2rem;
            background: #f9fafb;
            border-radius: 8px;
            color: #6b7280;
        }
        .newspaper-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .newspaper-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .newspaper-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        .newspaper-details {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        .newspaper-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .newspaper-detail i {
            width: 16px;
            color: #667eea;
        }
        .newspaper-detail strong {
            color: #374151;
            font-weight: 600;
        }
        .issues-range {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #f3f4f6;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            color: #667eea;
        }
        .location-info {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #ede9fe;
            color: #6d28d9;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .missing-issues-badge {
            background: #fef3c7;
            color: #92400e;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
            margin-top: 0.5rem;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }
        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
        .empty-state h3 {
            margin: 0 0 0.5rem 0;
            color: #374151;
        }
        .empty-state p {
            margin: 0 0 2rem 0;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            text-decoration: none;
            color: #374151;
        }
        .pagination a:hover {
            background: #f3f4f6;
        }
        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #667eea;
            text-decoration: none;
            margin-bottom: 1rem;
        }
        .back-link:hover {
            color: #5a67d8;
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
                <i class="fas fa-newspaper"></i>
                <?= $t['title'] ?>
            </h1>
            <p><?= $t['subtitle'] ?></p>
        </div>

        <div class="stats-bar">
            <div class="stats-info">
                <div class="stats-number"><?= $total_newspapers ?></div>
                <div class="stats-label"><?= $t['total_newspapers'] ?></div>
            </div>
            
            <div class="stats-info">
                <a href="add_newspaper.php?lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                    <i class="fas fa-plus"></i>
                    <?= $t['add_newspaper'] ?>
                </a>
            </div>
        </div>

        <?php if ($total_newspapers == 0): ?>
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h3><?= $t['no_newspapers'] ?></h3>
                <p><?= $t['no_newspapers_msg'] ?></p>
                <a href="add_newspaper.php?lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    <?= $t['add_first_newspaper'] ?>
                </a>
            </div>
        <?php else: ?>
            <!-- Section: جريدة الشعب / Journal du Peuple -->
            <?php 
            $shaab_newspapers = $newspapers_by_type['popular'] ?? [];
            ?>
            <div class="newspaper-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-newspaper"></i>
                        <?= $t['shaab_newspaper'] ?>
                    </h2>
                    <span class="section-count">
                        <?= count($shaab_newspapers) ?> <?= $t['total_issues'] ?>
                    </span>
                </div>
                
                <?php if (empty($shaab_newspapers)): ?>
                    <div class="no-newspapers-section">
                        <i class="fas fa-info-circle"></i>
                        <?= $lang == 'ar' ? 'لا توجد إصدارات من جريدة الشعب' : 'Aucune édition du Journal du Peuple' ?>
                    </div>
                <?php else: ?>
                    <div class="newspapers-grid">
                        <?php foreach ($shaab_newspapers as $newspaper): ?>
                            <div class="newspaper-card">
                                <div class="newspaper-title">
                                    <i class="fas fa-newspaper"></i>
                                    <?= htmlspecialchars($newspaper['title']) ?>
                                </div>
                                
                                <div class="newspaper-details">
                                    <!-- Issues Range -->
                                    <div class="newspaper-detail">
                                        <i class="fas fa-sort-numeric-up"></i>
                                        <span><?= $t['issues'] ?>:</span>
                                        <span class="issues-range">
                                            <?= htmlspecialchars($newspaper['issue_from']) ?> 
                                            <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i> 
                                            <?= htmlspecialchars($newspaper['issue_to']) ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Date -->
                                    <?php if (!empty($newspaper['newspaper_date'])): ?>
                                    <div class="newspaper-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span><?= $t['newspaper_date'] ?>:</span>
                                        <strong><?= date('Y-m-d', strtotime($newspaper['newspaper_date'])) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Missing Issues -->
                                    <?php if (!empty($newspaper['missing_issues'])): ?>
                                    <div class="newspaper-detail">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span><?= $t['missing_issues'] ?>:</span>
                                        <span class="missing-issues-badge"><?= htmlspecialchars($newspaper['missing_issues']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Location -->
                                    <?php if (!empty($newspaper['box_number']) || !empty($newspaper['cabinet_number']) || !empty($newspaper['shelf_number']) || !empty($newspaper['drawer_number'])): ?>
                                    <div class="newspaper-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= $t['location'] ?>:</span>
                                        <div class="location-info">
                                            <?php if (!empty($newspaper['box_number'])): ?>
                                                <span class="location-badge">
                                                    <i class="fas fa-box"></i>
                                                    <?= $t['box'] ?> <?= htmlspecialchars($newspaper['box_number']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($newspaper['cabinet_number'])): ?>
                                                <span class="location-badge">
                                                    <i class="fas fa-archive"></i>
                                                    <?= $t['cabinet'] ?> <?= htmlspecialchars($newspaper['cabinet_number']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($newspaper['shelf_number'])): ?>
                                                <span class="location-badge">
                                                    <i class="fas fa-layer-group"></i>
                                                    <?= $t['shelf'] ?> <?= htmlspecialchars($newspaper['shelf_number']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($newspaper['drawer_number'])): ?>
                                                <span class="location-badge">
                                                    <i class="fas fa-drawer"></i>
                                                    <?= $t['drawer'] ?> <?= htmlspecialchars($newspaper['drawer_number']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Date Added -->
                                    <div class="newspaper-detail">
                                        <i class="fas fa-clock"></i>
                                        <span><?= $t['date_added'] ?>:</span>
                                        <strong><?= date('Y-m-d', strtotime($newspaper['created_at'])) ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Section: الجريدة الرسمية / Journal officiel -->
            <?php 
            $official_newspapers = $newspapers_by_type['official'] ?? [];
            ?>
            <div class="newspaper-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-file-alt"></i>
                        <?= $t['official_newspaper'] ?>
                    </h2>
                    <span class="section-count">
                        <?= count($official_newspapers) ?> <?= $t['total_issues'] ?>
                    </span>
                </div>
                
                <?php if (empty($official_newspapers)): ?>
                    <div class="no-newspapers-section">
                        <i class="fas fa-info-circle"></i>
                        <?= $lang == 'ar' ? 'لا توجد إصدارات من الجريدة الرسمية' : 'Aucune édition du Journal officiel' ?>
                    </div>
                <?php else: ?>
                    <div class="newspapers-grid">
                        <?php foreach ($official_newspapers as $newspaper): ?>
                            <div class="newspaper-card">
                                <div class="newspaper-title">
                                    <i class="fas fa-file-alt"></i>
                                    <?= htmlspecialchars($newspaper['title']) ?>
                                </div>
                                
                                <div class="newspaper-details">
                                    <!-- Issues Range -->
                                    <div class="newspaper-detail">
                                        <i class="fas fa-sort-numeric-up"></i>
                                        <span><?= $t['issues'] ?>:</span>
                                        <span class="issues-range">
                                            <?= htmlspecialchars($newspaper['issue_from']) ?> 
                                            <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i> 
                                            <?= htmlspecialchars($newspaper['issue_to']) ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Date -->
                                    <?php if (!empty($newspaper['newspaper_date'])): ?>
                                    <div class="newspaper-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span><?= $t['newspaper_date'] ?>:</span>
                                        <strong><?= date('Y-m-d', strtotime($newspaper['newspaper_date'])) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Missing Issues -->
                                    <?php if (!empty($newspaper['missing_issues'])): ?>
                                    <div class="newspaper-detail">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span><?= $t['missing_issues'] ?>:</span>
                                        <span class="missing-issues-badge"><?= htmlspecialchars($newspaper['missing_issues']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Location -->
                                    <?php if (!empty($newspaper['box_number']) || !empty($newspaper['cabinet_number']) || !empty($newspaper['shelf_number']) || !empty($newspaper['drawer_number'])): ?>
                                    <div class="newspaper-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= $t['location'] ?>:</span>
                                        <div class="location-info">
                                            <?php if (!empty($newspaper['box_number'])): ?>
                                                <span class="location-badge">
                                                    <i class="fas fa-box"></i>
                                                    <?= $t['box'] ?> <?= htmlspecialchars($newspaper['box_number']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($newspaper['cabinet_number'])): ?>
                                                <span class="location-badge">
                                                    <i class="fas fa-archive"></i>
                                                    <?= $t['cabinet'] ?> <?= htmlspecialchars($newspaper['cabinet_number']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($newspaper['shelf_number'])): ?>
                                                <span class="location-badge">
                                                    <i class="fas fa-layer-group"></i>
                                                    <?= $t['shelf'] ?> <?= htmlspecialchars($newspaper['shelf_number']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($newspaper['drawer_number'])): ?>
                                                <span class="location-badge">
                                                    <i class="fas fa-drawer"></i>
                                                    <?= $t['drawer'] ?> <?= htmlspecialchars($newspaper['drawer_number']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Date Added -->
                                    <div class="newspaper-detail">
                                        <i class="fas fa-clock"></i>
                                        <span><?= $t['date_added'] ?>:</span>
                                        <strong><?= date('Y-m-d', strtotime($newspaper['created_at'])) ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>


