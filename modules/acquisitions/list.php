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
<link rel="stylesheet" href="../../public/assets/css/acquisitions.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../../public/assets/js/script.js"></script>
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
