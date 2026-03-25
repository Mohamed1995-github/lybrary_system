<?php
require_once '../../../../config/db.php';
require_once '../../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';

// Paramètres de pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Récupérer les emprunteurs
try {
    // Compter le total
    $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM borrowers");
    $total_borrowers = $count_stmt->fetch()['total'];
    
    // Récupérer les emprunteurs avec pagination
    $stmt = $pdo->prepare("
        SELECT b.id, b.name, b.phone, b.id_number, b.created_at,
               COUNT(l.id) as total_loans,
               COUNT(CASE WHEN l.status = 'active' THEN 1 END) as active_loans
        FROM borrowers b
        LEFT JOIN loans l ON b.id = l.borrower_id
        GROUP BY b.id
        ORDER BY b.name ASC 
        LIMIT {$limit} OFFSET {$offset}
    ");
    $stmt->execute();
    $borrowers = $stmt->fetchAll();
    
    $total_pages = ceil($total_borrowers / $limit);
    
} catch (PDOException $e) {
    $borrowers = [];
    $total_borrowers = 0;
    $total_pages = 0;
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'قائمة المستعيرين',
        'subtitle' => 'إدارة بيانات المستعيرين',
        'total_borrowers' => 'إجمالي المستعيرين',
        'add_borrower' => 'إضافة مستعير جديد',
        'name' => 'الإسم',
        'phone' => 'رقم الهاتف',
        'id_number' => 'رقم بطاقة التعريف',
        'total_loans' => 'إجمالي الإعارات',
        'active_loans' => 'الإعارات النشطة',
        'date_added' => 'تاريخ الإضافة',
        'id' => 'المعرف',
        'no_borrowers' => 'لا يوجد مستعيرين',
        'no_borrowers_msg' => 'لم يتم إضافة أي مستعيرين بعد',
        'add_first_borrower' => 'إضافة أول مستعير',
        'back_to_dashboard' => 'العودة للوحة التحكم'
    ],
    'fr' => [
        'title' => 'Liste des emprunteurs',
        'subtitle' => 'Gestion des données des emprunteurs',
        'total_borrowers' => 'Total des emprunteurs',
        'add_borrower' => 'Ajouter un emprunteur',
        'name' => 'Nom',
        'phone' => 'Numéro de téléphone',
        'id_number' => 'Numéro de carte d\'identité',
        'total_loans' => 'Total des emprunts',
        'active_loans' => 'Emprunts actifs',
        'date_added' => 'Date d\'ajout',
        'id' => 'ID',
        'no_borrowers' => 'Aucun emprunteur',
        'no_borrowers_msg' => 'Aucun emprunteur n\'a été ajouté pour le moment',
        'add_first_borrower' => 'Ajouter le premier emprunteur',
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
    <link rel="stylesheet" href="../../../assets/css/style.css">
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
        .container, .list-container, .stats-bar, .borrowers-grid, .borrower-card {
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
        .borrowers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .borrower-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .borrower-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .borrower-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .borrower-id {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .borrower-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        .borrower-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .borrower-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .borrower-detail i {
            width: 16px;
            color: #667eea;
        }
        .borrower-detail.full-width {
            grid-column: 1 / -1;
        }
        .loan-stats {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            text-align: center;
        }
        .loan-stat {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .loan-stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .loan-stat-label {
            font-size: 0.75rem;
            color: #6b7280;
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
        <a href="../dashboard.php?lang=<?= $lang ?>" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <?= $t['back_to_dashboard'] ?>
        </a>
        
        <div class="header">
            <h1>
                <i class="fas fa-users"></i>
                <?= $t['title'] ?>
            </h1>
            <p><?= $t['subtitle'] ?></p>
        </div>

        <div class="stats-bar">
            <div class="stats-info">
                <div class="stats-number"><?= $total_borrowers ?></div>
                <div class="stats-label"><?= $t['total_borrowers'] ?></div>
            </div>
            
            <div class="stats-info">
                <a href="add.php?lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                    <i class="fas fa-plus"></i>
                    <?= $t['add_borrower'] ?>
                </a>
            </div>
        </div>

        <?php if (empty($borrowers)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3><?= $t['no_borrowers'] ?></h3>
                <p><?= $t['no_borrowers_msg'] ?></p>
                <a href="add.php?lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    <?= $t['add_first_borrower'] ?>
                </a>
            </div>
        <?php else: ?>
            <div class="borrowers-grid">
                <?php foreach ($borrowers as $borrower): ?>
                    <div class="borrower-card">
                        <div class="borrower-header">
                            <div class="borrower-id">#<?= htmlspecialchars($borrower['id']) ?></div>
                            <div class="borrower-detail">
                                <i class="fas fa-clock"></i>
                                <span><?= date('Y-m-d', strtotime($borrower['created_at'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="borrower-name"><?= htmlspecialchars($borrower['name']) ?></div>
                        
                        <div class="borrower-details">
                            <div class="borrower-detail">
                                <i class="fas fa-phone"></i>
                                <span><?= htmlspecialchars($borrower['phone']) ?></span>
                            </div>
                            
                            <div class="borrower-detail">
                                <i class="fas fa-id-card"></i>
                                <span><?= htmlspecialchars($borrower['id_number']) ?></span>
                            </div>
                        </div>
                        
                        <div class="loan-stats">
                            <div class="loan-stat">
                                <div class="loan-stat-number"><?= $borrower['total_loans'] ?></div>
                                <div class="loan-stat-label"><?= $t['total_loans'] ?></div>
                            </div>
                            <div class="loan-stat">
                                <div class="loan-stat-number" style="color: #f59e0b;"><?= $borrower['active_loans'] ?></div>
                                <div class="loan-stat-label"><?= $t['active_loans'] ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?lang=<?= $lang ?>&page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                            <?= $lang == 'ar' ? 'السابق' : 'Précédent' ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?lang=<?= $lang ?>&page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?lang=<?= $lang ?>&page=<?= $page + 1 ?>">
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


