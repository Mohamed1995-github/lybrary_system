<?php
/* modules/acquisitions/list.php */
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_GET['lang'] ?? 'ar';

// Options pour l'affichage
$source_types = [
    'book' => ['ar' => 'كتب', 'fr' => 'Livres'],
    'magazine' => ['ar' => 'مجلات', 'fr' => 'Revues / Magazines'],
    'other' => ['ar' => 'مصادر أخرى', 'fr' => 'Autre source']
];

$acquisition_methods = [
    'purchase' => ['ar' => 'شراء', 'fr' => 'Achat'],
    'exchange' => ['ar' => 'تبادل', 'fr' => 'Échange'],
    'donation' => ['ar' => 'إهداء', 'fr' => 'Don']
];

$supplier_types = [
    'institution' => ['ar' => 'مؤسسة', 'fr' => 'Institution'],
    'publisher' => ['ar' => 'دار النشر', 'fr' => 'Maison d\'édition'],
    'person' => ['ar' => 'شخص', 'fr' => 'Personne']
];

// Récupérer les acquisitions
try {
    $stmt = $pdo->prepare("SELECT * FROM acquisitions WHERE lang = ? ORDER BY acquired_date DESC, created_at DESC");
    $stmt->execute([$lang]);
    $acquisitions = $stmt->fetchAll();
} catch (PDOException $e) {
    $acquisitions = [];
    $error = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'قائمة التزويد' : 'Liste des approvisionnements' ?> - Library System</title>
<link rel="stylesheet" href="../../public/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../../public/assets/js/script.js"></script>
<style>
.page-container {
    min-height: 100vh;
    padding: 2rem;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
}

.page-icon {
    font-size: 2rem;
    color: var(--success-color);
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(34 197 94 / 0.1);
    border-radius: var(--radius-lg);
}

.page-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-radius: var(--radius-lg);
    font-weight: 500;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.btn-primary {
    background: var(--success-color);
    color: white;
    border: none;
}

.btn-primary:hover {
    background: #16a34a;
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 2px solid var(--border-color);
}

.btn-secondary:hover {
    background: var(--bg-tertiary);
    transform: translateY(-1px);
}

.data-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 2rem;
    border: 1px solid var(--border-color);
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.empty-state p {
    margin-bottom: 2rem;
    font-size: 1rem;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

th {
    background: var(--bg-secondary);
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

td {
    color: var(--text-primary);
    font-size: 0.875rem;
}

tr:hover {
    background: var(--bg-secondary);
}

.table-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.action-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.action-edit {
    background: var(--primary-color);
    color: white;
}

.action-edit:hover {
    background: var(--primary-hover);
    transform: scale(1.1);
}

.action-delete {
    background: var(--error-color);
    color: white;
}

.action-delete:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 500;
}

.status-purchase {
    background: rgb(34 197 94 / 0.1);
    color: var(--success-color);
}

.status-exchange {
    background: rgb(59 130 246 / 0.1);
    color: var(--primary-color);
}

.status-donation {
    background: rgb(168 85 247 / 0.1);
    color: #9333ea;
}

@media (max-width: 768px) {
    .page-container {
        padding: 1rem;
    }
    
    .page-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .page-title {
        font-size: 1.5rem;
        justify-content: center;
    }
    
    .page-actions {
        justify-content: center;
    }
    
    .data-card {
        padding: 1rem;
        margin: 0 1rem;
    }
    
    .table-container {
        font-size: 0.75rem;
    }
    
    th, td {
        padding: 0.5rem;
    }
    
    .table-actions {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>
</head>
<body>
<div class="page-container">
    <div class="page-header fade-in">
        <div class="page-title">
            <div class="page-icon">
                <i class="fas fa-truck"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'قائمة التزويد' : 'Liste des approvisionnements' ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'إدارة جميع عمليات التزويد في المكتبة' : 'Gestion de tous les approvisionnements de la bibliothèque' ?>
                </p>
            </div>
        </div>
        
        <div class="page-actions">
            <a href="../../public/router.php?module=acquisitions&action=add&lang=<?=$lang?>" class="action-btn btn-primary">
                <i class="fas fa-plus"></i>
                <?= $lang == 'ar' ? 'إضافة تزويد جديد' : 'Ajouter un approvisionnement' ?>
            </a>
            <a href="/library_system/public/dashboard.php?lang=<?=$lang?>" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
            </a>
        </div>
    </div>

    <div class="data-card fade-in">
        <?php if (empty($acquisitions)): ?>
            <div class="empty-state">
                <i class="fas fa-truck"></i>
                <h3><?= $lang == 'ar' ? 'لا توجد عمليات تزويد' : 'Aucun approvisionnement' ?></h3>
                <p><?= $lang == 'ar' ? 'لم يتم إضافة أي تزويد بعد' : 'Aucun approvisionnement n\'a été ajouté pour le moment' ?></p>
                <a href="../../public/router.php?module=acquisitions&action=add&lang=<?=$lang?>" class="action-btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة تزويد جديد' : 'Ajouter un approvisionnement' ?>
                </a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><?= $lang == 'ar' ? 'نوع المصدر' : 'Type de source' ?></th>
                            <th><?= $lang == 'ar' ? 'طريقة التزويد' : 'Méthode' ?></th>
                            <th><?= $lang == 'ar' ? 'جهة التزويد' : 'Fournisseur' ?></th>
                            <th><?= $lang == 'ar' ? 'الاسم' : 'Nom' ?></th>
                            <th><?= $lang == 'ar' ? 'رقم الهاتف' : 'Téléphone' ?></th>
                            <th><?= $lang == 'ar' ? 'التكلفة' : 'Coût' ?></th>
                            <th><?= $lang == 'ar' ? 'الكمية' : 'Quantité' ?></th>
                            <th><?= $lang == 'ar' ? 'التاريخ' : 'Date' ?></th>
                            <th><?= $lang == 'ar' ? 'الإجراءات' : 'Actions' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($acquisitions as $acquisition): ?>
                            <tr>
                                <td>
                                    <span class="status-badge status-purchase">
                                        <i class="fas fa-book"></i>
                                        <?= $source_types[$acquisition['source_type']][$lang] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $method_class = 'status-' . $acquisition['acquisition_method'];
                                    $method_icon = [
                                        'purchase' => 'fas fa-shopping-cart',
                                        'exchange' => 'fas fa-exchange-alt',
                                        'donation' => 'fas fa-gift'
                                    ][$acquisition['acquisition_method']] ?? 'fas fa-question';
                                    ?>
                                    <span class="status-badge <?= $method_class ?>">
                                        <i class="<?= $method_icon ?>"></i>
                                        <?= $acquisition_methods[$acquisition['acquisition_method']][$lang] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-exchange">
                                        <i class="fas fa-building"></i>
                                        <?= $supplier_types[$acquisition['supplier_type']][$lang] ?>
                                    </span>
                                </td>
                                <td><strong><?= htmlspecialchars($acquisition['supplier_name']) ?></strong></td>
                                <td><?= htmlspecialchars($acquisition['supplier_phone'] ?: '-') ?></td>
                                <td>
                                    <?php if ($acquisition['cost'] > 0): ?>
                                        <span style="color: var(--success-color); font-weight: 600;">
                                            <?= number_format($acquisition['cost'], 2) ?> <?= $lang == 'ar' ? 'د.ج' : 'DA' ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--primary-color);">
                                        <?= $acquisition['quantity'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($acquisition['acquired_date'])) ?>
                                </td>
                                <td class="table-actions">
                                    <a href="../../public/router.php?module=acquisitions&action=edit&lang=<?=$lang?>&id=<?=$acquisition['id']?>" class="action-icon action-edit" title="<?= $lang == 'ar' ? 'تعديل' : 'Modifier' ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../../public/router.php?module=acquisitions&action=delete&lang=<?=$lang?>&id=<?=$acquisition['id']?>" class="action-icon action-delete" title="<?= $lang == 'ar' ? 'حذف' : 'Supprimer' ?>" onclick="return confirm('<?= $lang == 'ar' ? 'هل أنت متأكد من حذف هذا الاستحواذ؟' : 'Êtes-vous sûr de vouloir supprimer cette acquisition ?' ?>')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
