<?php
require_once '../../../config/db.php';
require_once '../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';
$status_filter = $_GET['status'] ?? 'all';

// Paramètres de pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Construire la requête selon le filtre
$where_clause = "1=1";
$params = [];

if ($status_filter === 'active') {
    $where_clause = "l.status = 'active'";
} elseif ($status_filter === 'overdue') {
    $where_clause = "l.status = 'active' AND l.due_date < NOW()";
} elseif ($status_filter === 'returned') {
    $where_clause = "l.status = 'returned'";
}

// Récupérer les emprunts
try {
    // Compter le total
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM loans l
        JOIN borrowers b ON l.borrower_id = b.id
        JOIN items i ON l.item_id = i.id
        WHERE $where_clause
    ";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_loans = $count_stmt->fetch()['total'];
    
    // Récupérer les emprunts avec pagination
    $sql = "
        SELECT l.id, l.loan_date, l.due_date, l.return_date, l.status, l.notes,
               b.name as borrower_name, b.phone as borrower_phone,
               i.title as item_title, i.type as item_type, i.author as item_author
        FROM loans l
        JOIN borrowers b ON l.borrower_id = b.id
        JOIN items i ON l.item_id = i.id
        WHERE $where_clause
        ORDER BY l.loan_date DESC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $loans = $stmt->fetchAll();
    
    $total_pages = ceil($total_loans / $limit);
    
} catch (PDOException $e) {
    $loans = [];
    $total_loans = 0;
    $total_pages = 0;
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'قائمة الإعارات',
        'subtitle' => 'إدارة جميع الإعارات',
        'total_loans' => 'إجمالي الإعارات',
        'add_loan' => 'إضافة إعارة جديدة',
        'borrower' => 'المستعير',
        'item' => 'المصدر',
        'loan_date' => 'تاريخ الإعارة',
        'due_date' => 'تاريخ الاستحقاق',
        'return_date' => 'تاريخ الإرجاع',
        'status' => 'الحالة',
        'notes' => 'ملاحظات',
        'id' => 'المعرف',
        'no_loans' => 'لا توجد إعارات',
        'no_loans_msg' => 'لم يتم إضافة أي إعارات بعد',
        'add_first_loan' => 'إضافة أول إعارة',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'active' => 'نشطة',
        'returned' => 'مرجعة',
        'overdue' => 'متأخرة',
        'all' => 'الكل',
        'filter_by_status' => 'تصفية حسب الحالة',
        'days_overdue' => 'أيام التأخير'
    ],
    'fr' => [
        'title' => 'Liste des emprunts',
        'subtitle' => 'Gestion de tous les emprunts',
        'total_loans' => 'Total des emprunts',
        'add_loan' => 'Ajouter un emprunt',
        'borrower' => 'Emprunteur',
        'item' => 'Ressource',
        'loan_date' => 'Date d\'emprunt',
        'due_date' => 'Date d\'échéance',
        'return_date' => 'Date de retour',
        'status' => 'Statut',
        'notes' => 'Notes',
        'id' => 'ID',
        'no_loans' => 'Aucun emprunt',
        'no_loans_msg' => 'Aucun emprunt n\'a été ajouté pour le moment',
        'add_first_loan' => 'Ajouter le premier emprunt',
        'back_to_dashboard' => 'Retour au tableau de bord',
        'active' => 'Actif',
        'returned' => 'Retourné',
        'overdue' => 'En retard',
        'all' => 'Tous',
        'filter_by_status' => 'Filtrer par statut',
        'days_overdue' => 'Jours de retard'
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
        .container, .list-container, .stats-bar, .loans-grid, .loan-card {
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
            flex-wrap: wrap;
            gap: 1rem;
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
        .filter-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .filter-select {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
        }
        .loans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 1.5rem;
        }
        .loan-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .loan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .loan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .loan-id {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-active {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-returned {
            background: #d1fae5;
            color: #065f46;
        }
        .status-overdue {
            background: #fee2e2;
            color: #991b1b;
        }
        .loan-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        .loan-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .loan-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .loan-detail i {
            width: 16px;
            color: #667eea;
        }
        .loan-detail.full-width {
            grid-column: 1 / -1;
        }
        .overdue-warning {
            background: #fef3c7;
            color: #92400e;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            text-align: center;
            margin-top: 1rem;
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
        <a href="dashboard.php?lang=<?= $lang ?>" class="back-link">
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

        <div class="stats-bar">
            <div class="stats-info">
                <div class="stats-number"><?= $total_loans ?></div>
                <div class="stats-label"><?= $t['total_loans'] ?></div>
            </div>
            
            <div class="filter-section">
                <label for="statusFilter" style="font-weight: 600; color: #374151;">
                    <?= $t['filter_by_status'] ?>:
                </label>
                <select id="statusFilter" class="filter-select" onchange="filterByStatus()">
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>><?= $t['all'] ?></option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>><?= $t['active'] ?></option>
                    <option value="overdue" <?= $status_filter === 'overdue' ? 'selected' : '' ?>><?= $t['overdue'] ?></option>
                    <option value="returned" <?= $status_filter === 'returned' ? 'selected' : '' ?>><?= $t['returned'] ?></option>
                </select>
            </div>
            
            <div class="stats-info">
                <a href="add.php?lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                    <i class="fas fa-plus"></i>
                    <?= $t['add_loan'] ?>
                </a>
            </div>
        </div>

        <?php if (empty($loans)): ?>
            <div class="empty-state">
                <i class="fas fa-book-reader"></i>
                <h3><?= $t['no_loans'] ?></h3>
                <p><?= $t['no_loans_msg'] ?></p>
                <a href="add.php?lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    <?= $t['add_first_loan'] ?>
                </a>
            </div>
        <?php else: ?>
            <div class="loans-grid">
                <?php foreach ($loans as $loan): ?>
                    <?php 
                    $is_overdue = $loan['status'] === 'active' && strtotime($loan['due_date']) < time();
                    $days_overdue = $is_overdue ? floor((time() - strtotime($loan['due_date'])) / 86400) : 0;
                    ?>
                    <div class="loan-card">
                        <div class="loan-header">
                            <div class="loan-id">#<?= htmlspecialchars($loan['id']) ?></div>
                            <span class="status-badge status-<?= $loan['status'] ?>">
                                <?= $t[$loan['status']] ?>
                            </span>
                        </div>
                        
                        <div class="loan-title"><?= htmlspecialchars($loan['item_title']) ?></div>
                        
                        <div class="loan-details">
                            <div class="loan-detail">
                                <i class="fas fa-user"></i>
                                <span><?= htmlspecialchars($loan['borrower_name']) ?></span>
                            </div>
                            
                            <div class="loan-detail">
                                <i class="fas fa-phone"></i>
                                <span><?= htmlspecialchars($loan['borrower_phone']) ?></span>
                            </div>
                            
                            <div class="loan-detail">
                                <i class="fas fa-calendar-plus"></i>
                                <span><?= date('Y-m-d', strtotime($loan['loan_date'])) ?></span>
                            </div>
                            
                            <div class="loan-detail">
                                <i class="fas fa-calendar-check"></i>
                                <span><?= date('Y-m-d', strtotime($loan['due_date'])) ?></span>
                            </div>
                            
                            <?php if ($loan['return_date']): ?>
                            <div class="loan-detail">
                                <i class="fas fa-undo"></i>
                                <span><?= date('Y-m-d', strtotime($loan['return_date'])) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($loan['notes']): ?>
                            <div class="loan-detail full-width">
                                <i class="fas fa-sticky-note"></i>
                                <span><?= htmlspecialchars($loan['notes']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($is_overdue): ?>
                        <div class="overdue-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= $days_overdue ?> <?= $t['days_overdue'] ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?lang=<?= $lang ?>&status=<?= $status_filter ?>&page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                            <?= $lang == 'ar' ? 'السابق' : 'Précédent' ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?lang=<?= $lang ?>&status=<?= $status_filter ?>&page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?lang=<?= $lang ?>&status=<?= $status_filter ?>&page=<?= $page + 1 ?>">
                            <?= $lang == 'ar' ? 'التالي' : 'Suivant' ?>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        function filterByStatus() {
            const status = document.getElementById('statusFilter').value;
            const url = new URL(window.location);
            url.searchParams.set('status', status);
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }
    </script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>