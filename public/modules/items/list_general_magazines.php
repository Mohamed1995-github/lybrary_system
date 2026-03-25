<?php
require_once __DIR__ . '/../../../config/db.php';
require_once '../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';
$category = $_GET['category'] ?? 'general';

// Paramètres de pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Récupérer les magazines généraux
try {
    // Compter le total
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM items WHERE type = 'magazine' AND field = 'General' AND lang = ?");
    $count_stmt->execute([$lang]);
    $total_magazines = $count_stmt->fetch()['total'];
    
    // Récupérer les magazines avec pagination
    $stmt = $pdo->prepare("
        SELECT id, title, issue_number, created_at 
        FROM items 
        WHERE type = 'magazine' AND field = 'General' AND lang = ? 
        ORDER BY title ASC 
        LIMIT {$limit} OFFSET {$offset}
    ");
    $stmt->execute([$lang]);
    $magazines = $stmt->fetchAll();
    
    $total_pages = ceil($total_magazines / $limit);
    
} catch (PDOException $e) {
    $magazines = [];
    $total_magazines = 0;
    $total_pages = 0;
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'المجلات العامة',
        'subtitle' => 'قائمة المجلات العامة',
        'total_magazines' => 'إجمالي المجلات',
        'add_magazine' => 'إضافة مجلة',
        'magazine_title' => 'عنوان المجلة',
        'issue_number' => 'العدد',
        'id' => 'المعرف',
        'date_added' => 'تاريخ الإضافة',
        'no_magazines' => 'لا توجد مجلات',
        'no_magazines_msg' => 'لم يتم إضافة أي مجلات عامة بعد',
        'add_first_magazine' => 'إضافة أول مجلة',
        'back_to_dashboard' => 'العودة للوحة التحكم'
    ],
    'fr' => [
        'title' => 'Revues Générales',
        'subtitle' => 'Liste des revues générales',
        'total_magazines' => 'Total des revues',
        'add_magazine' => 'Ajouter une revue',
        'magazine_title' => 'Titre de la revue',
        'issue_number' => 'Numéro',
        'id' => 'ID',
        'date_added' => 'Date d\'ajout',
        'no_magazines' => 'Aucune revue',
        'no_magazines_msg' => 'Aucune revue générale n\'a été ajoutée pour le moment',
        'add_first_magazine' => 'Ajouter la première revue',
        'back_to_dashboard' => 'Retour au tableau de bord'
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
        .container, .list-container, .stats-bar, .magazines-grid, .magazine-card {
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
        .magazines-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .magazine-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .magazine-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .magazine-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        .magazine-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .magazine-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .magazine-detail i {
            width: 16px;
            color: #667eea;
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
                <div class="stats-number"><?= $total_magazines ?></div>
                <div class="stats-label"><?= $t['total_magazines'] ?></div>
            </div>
            
            <div class="stats-info">
                <a href="add_general_magazine.php?category=<?=$category?>&lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                    <i class="fas fa-plus"></i>
                    <?= $t['add_magazine'] ?>
                </a>
            </div>
        </div>

        <?php if (empty($magazines)): ?>
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h3><?= $t['no_magazines'] ?></h3>
                <p><?= $t['no_magazines_msg'] ?></p>
                <a href="add_general_magazine.php?category=<?=$category?>&lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    <?= $t['add_first_magazine'] ?>
                </a>
            </div>
        <?php else: ?>
            <div class="magazines-grid">
                <?php foreach ($magazines as $magazine): ?>
                    <div class="magazine-card">
                        <div class="magazine-title"><?= htmlspecialchars($magazine['title']) ?></div>
                        
                        <div class="magazine-details">
                            <div class="magazine-detail">
                                <i class="fas fa-sort-numeric-up"></i>
                                <span><?= $t['issue_number'] ?>: <?= htmlspecialchars($magazine['issue_number']) ?></span>
                            </div>
                            
                            <div class="magazine-detail">
                                <i class="fas fa-hashtag"></i>
                                <span><?= $t['id'] ?>: <?= htmlspecialchars($magazine['id']) ?></span>
                            </div>
                            
                            <div class="magazine-detail">
                                <i class="fas fa-clock"></i>
                                <span><?= date('Y-m-d', strtotime($magazine['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?category=<?= $category ?>&lang=<?= $lang ?>&page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                            <?= $lang == 'ar' ? 'السابق' : 'Précédent' ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?category=<?= $category ?>&lang=<?= $lang ?>&page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?category=<?= $category ?>&lang=<?= $lang ?>&page=<?= $page + 1 ?>">
                            <?= $lang == 'ar' ? 'التالي' : 'Suivant' ?>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>


