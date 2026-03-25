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

// Récupérer les acquisitions
try {
    // Compter le total
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM acquisitions WHERE lang = ?");
    $count_stmt->execute([$lang]);
    $total_acquisitions = $count_stmt->fetch()['total'];
    
    // Récupérer les acquisitions avec pagination
    $stmt = $pdo->prepare("
        SELECT id, resource_type, acquisition_method, provider_type, provider_name, provider_phone, quantity, created_at 
        FROM acquisitions 
        WHERE lang = ? 
        ORDER BY created_at DESC 
        LIMIT {$limit} OFFSET {$offset}
    ");
    $stmt->execute([$lang]);
    $acquisitions = $stmt->fetchAll();
    
    $total_pages = ceil($total_acquisitions / $limit);
    
} catch (PDOException $e) {
    $acquisitions = [];
    $total_acquisitions = 0;
    $total_pages = 0;
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'قسم التزويد',
        'subtitle' => 'إدارة عمليات التزويد والشراء',
        'total_acquisitions' => 'إجمالي التزويدات',
        'add_acquisition' => 'إضافة تزويد جديد',
        'resource_type' => 'نوع المصدر',
        'acquisition_method' => 'طريقة التزويد',
        'provider_type' => 'جهة التزويد',
        'provider_name' => 'اسم المزود',
        'provider_phone' => 'رقم الهاتف',
        'quantity' => 'الكمية',
        'date_added' => 'تاريخ الإضافة',
        'id' => 'المعرف',
        'no_acquisitions' => 'لا توجد تزويدات',
        'no_acquisitions_msg' => 'لم يتم إضافة أي تزويدات بعد',
        'add_first_acquisition' => 'إضافة أول تزويد',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'book' => 'كتاب',
        'magazine' => 'مجلة',
        'other' => 'مصدر آخر',
        'purchase' => 'شراء',
        'exchange' => 'تبادل',
        'donation' => 'إهداء',
        'institution' => 'مؤسسة',
        'publisher' => 'دار النشر',
        'person' => 'شخص'
    ],
    'fr' => [
        'title' => 'Section Acquisition',
        'subtitle' => 'Gestion des acquisitions et achats',
        'total_acquisitions' => 'Total des acquisitions',
        'add_acquisition' => 'Ajouter une acquisition',
        'resource_type' => 'Type de ressource',
        'acquisition_method' => 'Méthode d\'acquisition',
        'provider_type' => 'Type de fournisseur',
        'provider_name' => 'Nom du fournisseur',
        'provider_phone' => 'Numéro de téléphone',
        'quantity' => 'Quantité',
        'date_added' => 'Date d\'ajout',
        'id' => 'ID',
        'no_acquisitions' => 'Aucune acquisition',
        'no_acquisitions_msg' => 'Aucune acquisition n\'a été ajoutée pour le moment',
        'add_first_acquisition' => 'Ajouter la première acquisition',
        'back_to_dashboard' => 'Retour au tableau de bord',
        'book' => 'Livre',
        'magazine' => 'Magazine',
        'other' => 'Autre source',
        'purchase' => 'Achat',
        'exchange' => 'Échange',
        'donation' => 'Don',
        'institution' => 'Institution',
        'publisher' => 'Maison d\'édition',
        'person' => 'Personne'
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
        .container, .list-container, .stats-bar, .acquisitions-grid, .acquisition-card {
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
        .acquisitions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .acquisition-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .acquisition-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .acquisition-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .acquisition-id {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .acquisition-type {
            background: #f3f4f6;
            color: #374151;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1rem;
        }
        .acquisition-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .acquisition-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .acquisition-detail i {
            width: 16px;
            color: #667eea;
        }
        .acquisition-detail.full-width {
            grid-column: 1 / -1;
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
        .method-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .method-purchase { background: #dbeafe; color: #1e40af; }
        .method-exchange { background: #fef3c7; color: #92400e; }
        .method-donation { background: #d1fae5; color: #065f46; }
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
                <i class="fas fa-shopping-cart"></i>
                <?= $t['title'] ?>
            </h1>
            <p><?= $t['subtitle'] ?></p>
            </div>

        <div class="stats-bar">
            <div class="stats-info">
                <div class="stats-number"><?= $total_acquisitions ?></div>
                <div class="stats-label"><?= $t['total_acquisitions'] ?></div>
        </div>
        
            <div class="stats-info">
                <a href="add.php?lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                <i class="fas fa-plus"></i>
                    <?= $t['add_acquisition'] ?>
            </a>
        </div>
    </div>

        <?php if (empty($acquisitions)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3><?= $t['no_acquisitions'] ?></h3>
                <p><?= $t['no_acquisitions_msg'] ?></p>
                <a href="add.php?lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    <?= $t['add_first_acquisition'] ?>
                </a>
            </div>
        <?php else: ?>
            <div class="acquisitions-grid">
                        <?php foreach ($acquisitions as $acquisition): ?>
                    <div class="acquisition-card">
                        <div class="acquisition-header">
                            <div class="acquisition-id">#<?= htmlspecialchars($acquisition['id']) ?></div>
                            <div class="acquisition-detail">
                                <i class="fas fa-clock"></i>
                                <span><?= date('Y-m-d', strtotime($acquisition['created_at'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="acquisition-type">
                            <?= $t[$acquisition['resource_type']] ?>
                        </div>
                        
                        <div class="acquisition-details">
                            <div class="acquisition-detail">
                                <i class="fas fa-shopping-cart"></i>
                                <span><?= $t[$acquisition['acquisition_method']] ?></span>
                            </div>
                            
                            <div class="acquisition-detail">
                                <i class="fas fa-users"></i>
                                <span><?= $t[$acquisition['provider_type']] ?></span>
                            </div>
                            
                            <div class="acquisition-detail full-width">
                                <i class="fas fa-user"></i>
                                <span><strong><?= htmlspecialchars($acquisition['provider_name']) ?></strong></span>
                            </div>
                            
                            <?php if (!empty($acquisition['provider_phone'])): ?>
                            <div class="acquisition-detail">
                                <i class="fas fa-phone"></i>
                                <span><?= htmlspecialchars($acquisition['provider_phone']) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="acquisition-detail">
                                <i class="fas fa-sort-numeric-up"></i>
                                <span><strong><?= htmlspecialchars($acquisition['quantity']) ?></strong> <?= $lang == 'ar' ? 'قطعة' : 'pièce(s)' ?></span>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem; text-align: center;">
                            <span class="method-badge method-<?= $acquisition['acquisition_method'] ?>">
                                <?= $t[$acquisition['acquisition_method']] ?>
                                    </span>
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
