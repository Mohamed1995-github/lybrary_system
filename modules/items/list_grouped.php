<?php
/* modules/items/list_grouped.php */
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_GET['lang'] ?? 'ar';
$type = $_GET['type'] ?? 'book';

// Get items grouped by field
$stmt = $pdo->prepare("SELECT * FROM items WHERE lang = ? AND type = ? ORDER BY field, title");
$stmt->execute([$lang, $type]);
$allItems = $stmt->fetchAll();

// Group items by field
$groupedItems = [];
foreach ($allItems as $item) {
    $field = $item['field'] ?: 'General';
    if (!isset($groupedItems[$field])) {
        $groupedItems[$field] = [];
    }  
    $groupedItems[$field][] = $item;
}

// Field translations
$fieldTranslations = [
    'General' => ['ar' => 'عام', 'fr' => 'Général'],
    'Science' => ['ar' => 'علوم', 'fr' => 'Sciences'],
    'Technology' => ['ar' => 'تقنية', 'fr' => 'Technologie'],
    'Literature' => ['ar' => 'أدب', 'fr' => 'Littérature'],
    'History' => ['ar' => 'تاريخ', 'fr' => 'Histoire'],
    'Philosophy' => ['ar' => 'فلسفة', 'fr' => 'Philosophie'],
    'Religion' => ['ar' => 'دين', 'fr' => 'Religion'],
    'Arts' => ['ar' => 'فنون', 'fr' => 'Arts'],
    'Medicine' => ['ar' => 'طب', 'fr' => 'Médecine'],
    'Law' => ['ar' => 'قانون', 'fr' => 'Droit'],
    'Economics' => ['ar' => 'اقتصاد', 'fr' => 'Économie'],
    'Education' => ['ar' => 'تعليم', 'fr' => 'Éducation'],
    'Other' => ['ar' => 'أخرى', 'fr' => 'Autre']
];

// Field icons
$fieldIcons = [
    'General' => 'fas fa-book',
    'Science' => 'fas fa-flask',
    'Technology' => 'fas fa-microchip',
    'Literature' => 'fas fa-pen-fancy',
    'History' => 'fas fa-landmark',
    'Philosophy' => 'fas fa-brain',
    'Religion' => 'fas fa-pray',
    'Arts' => 'fas fa-palette',
    'Medicine' => 'fas fa-heartbeat',
    'Law' => 'fas fa-balance-scale',
    'Economics' => 'fas fa-chart-line',
    'Education' => 'fas fa-graduation-cap',
    'Other' => 'fas fa-ellipsis-h'
];
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'الكتب مصنفة حسب المجال' : 'Livres classés par domaine' ?> - Library System</title>
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
    color: var(--primary-color);
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(37 99 235 / 0.1);
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
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-hover);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
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

.view-toggle {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    justify-content: center;
}

.toggle-btn {
    padding: 0.5rem 1rem;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-radius: var(--radius-lg);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.toggle-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.toggle-btn:hover {
    transform: translateY(-1px);
}

.field-group {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.field-header {
    background: var(--bg-secondary);
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--border-color);
}

.field-header:hover {
    background: var(--bg-tertiary);
}

.field-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.field-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.field-icon {
    font-size: 1.5rem;
    color: var(--primary-color);
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(37 99 235 / 0.1);
    border-radius: var(--radius-lg);
}

.field-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}

.field-count {
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
}

.field-toggle {
    font-size: 1.25rem;
    color: var(--text-secondary);
    transition: transform 0.2s ease;
}

.field-toggle.expanded {
    transform: rotate(180deg);
}

.field-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.field-content.expanded {
    max-height: 2000px;
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    padding: 1.5rem;
}

.item-card {
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

.item-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.item-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.item-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.meta-item i {
    width: 1rem;
    color: var(--text-light);
}

.item-status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-available {
    background: rgb(16 185 129 / 0.1);
    color: var(--success-color);
}

.status-limited {
    background: rgb(245 158 11 / 0.1);
    color: var(--warning-color);
}

.status-unavailable {
    background: rgb(239 68 68 / 0.1);
    color: var(--error-color);
}

.item-actions {
    display: flex;
    gap: 0.5rem;
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
    background: rgb(59 130 246 / 0.1);
    color: var(--primary-color);
}

.action-edit:hover {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
}

.action-delete {
    background: rgb(239 68 68 / 0.1);
    color: var(--error-color);
}

.action-delete:hover {
    background: var(--error-color);
    color: white;
    transform: scale(1.1);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
}

.empty-icon {
    font-size: 4rem;
    color: var(--text-light);
    margin-bottom: 1rem;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.empty-description {
    font-size: 1rem;
    margin-bottom: 2rem;
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
    
    .action-btn {
        flex: 1;
        justify-content: center;
        min-width: 120px;
    }
    
    .items-grid {
        grid-template-columns: 1fr;
        padding: 1rem;
    }
    
    .field-header {
        padding: 1rem;
    }
    
    .field-info {
        gap: 0.75rem;
    }
    
    .field-icon {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1.25rem;
    }
    
    .field-title {
        font-size: 1.125rem;
    }
}
</style>
</head>
<body>
<div class="page-container">
    <div class="page-header fade-in">
        <div class="page-title">
            <div class="page-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'الكتب مصنفة حسب المجال' : 'Livres classés par domaine' ?> (<?= strtoupper($lang) ?>)</h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'تصفح الكتب منظمة حسب المجالات العلمية' : 'Parcourez les livres organisés par domaines scientifiques' ?>
                </p>
            </div>
        </div>
        
        <div class="page-actions">
            <a href="../../public/router.php?module=items&action=add_edit&lang=<?=$lang?>&type=<?=$type?>" class="action-btn btn-primary">
                <i class="fas fa-plus"></i>
                <?= $lang == 'ar' ? 'إضافة جديد' : 'Ajouter' ?>
            </a>
            <a href="../../public/router.php?module=items&action=list&lang=<?=$lang?>&type=<?=$type?>" class="action-btn btn-secondary">
                <i class="fas fa-list"></i>
                <?= $lang == 'ar' ? 'عرض عادي' : 'Vue normale' ?>
            </a>
            <a href="../../public/dashboard.php" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
            </a>
        </div>
    </div>

    <div class="view-toggle">
        <a href="../../public/router.php?module=items&action=list&lang=<?=$lang?>&type=<?=$type?>" class="toggle-btn">
            <i class="fas fa-list"></i>
            <?= $lang == 'ar' ? 'عرض عادي' : 'Vue normale' ?>
        </a>
        <a href="list_grouped.php?lang=<?=$lang?>&type=<?=$type?>" class="toggle-btn active">
            <i class="fas fa-layer-group"></i>
            <?= $lang == 'ar' ? 'عرض مصنف' : 'Vue classée' ?>
        </a>
    </div>

    <?php if (empty($groupedItems)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h3 class="empty-title">
                <?= $lang == 'ar' ? 'لا توجد كتب' : 'Aucun livre trouvé' ?>
            </h3>
            <p class="empty-description">
                <?= $lang == 'ar' ? 'لم يتم إضافة أي كتب بعد. ابدأ بإضافة أول كتاب.' : 'Aucun livre n\'a été ajouté encore. Commencez par ajouter le premier.' ?>
            </p>
            <a href="../../public/router.php?module=items&action=add_edit&lang=<?=$lang?>&type=<?=$type?>" class="action-btn btn-primary">
                <i class="fas fa-plus"></i>
                <?= $lang == 'ar' ? 'إضافة أول كتاب' : 'Ajouter le premier' ?>
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($groupedItems as $field => $items): ?>
            <div class="field-group fade-in">
                <div class="field-header" onclick="toggleField(this)">
                    <div class="field-header-content">
                        <div class="field-info">
                            <div class="field-icon">
                                <i class="<?= $fieldIcons[$field] ?? 'fas fa-book' ?>"></i>
                            </div>
                            <div>
                                <div class="field-title">
                                    <?= $fieldTranslations[$field][$lang] ?? $field ?>
                                </div>
                                <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                    <?= count($items) ?> <?= $lang == 'ar' ? 'كتاب' : 'livre' . (count($items) > 1 ? 's' : '') ?>
                                </div>
                            </div>
                        </div>
                        <div class="field-count">
                            <?= count($items) ?>
                        </div>
                        <div class="field-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
                <div class="field-content">
                    <div class="items-grid">
                        <?php foreach ($items as $item): ?>
                            <div class="item-card">
                                <div class="item-title">
                                    <?= htmlspecialchars($item['title']) ?>
                                </div>
                                <div class="item-meta">
                                    <?php if ($item['author']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-user"></i>
                                            <?= htmlspecialchars($item['author']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($item['publisher']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-building"></i>
                                            <?= htmlspecialchars($item['publisher']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($item['year_pub']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <?= htmlspecialchars($item['year_pub']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($item['classification']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-tags"></i>
                                            <?= htmlspecialchars($item['classification']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-status">
                                    <div>
                                        <?php
                                        $available = $item['copies_in'];
                                        $total = $item['copies_total'];
                                        $percentage = $total > 0 ? ($available / $total) * 100 : 0;
                                        
                                        if ($percentage >= 50) {
                                            $statusClass = 'status-available';
                                            $statusText = $lang == 'ar' ? 'متاح' : 'Disponible';
                                        } elseif ($percentage > 0) {
                                            $statusClass = 'status-limited';
                                            $statusText = $lang == 'ar' ? 'محدود' : 'Limité';
                                        } else {
                                            $statusClass = 'status-unavailable';
                                            $statusText = $lang == 'ar' ? 'غير متاح' : 'Indisponible';
                                        }
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <i class="fas fa-circle"></i>
                                            <?= $statusText ?>
                                        </span>
                                        <div style="font-size: 0.75rem; color: var(--text-light); margin-top: 0.25rem;">
                                            <?= $available ?>/<?= $total ?>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <a href="../../public/router.php?module=items&action=add_edit&id=<?=$item["id']?>" class="action-icon action-edit" 
                                           title="<?= $lang == 'ar' ? 'تعديل' : 'Modifier' ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../../public/router.php?module=items&action=delete&id=<?=$item["id']?>" class="action-icon action-delete"
                                           onclick="return confirm('<?= $lang == 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Êtes-vous sûr de vouloir supprimer ?' ?>')"
                                           title="<?= $lang == 'ar' ? 'حذف' : 'Supprimer' ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleField(header) {
    const content = header.nextElementSibling;
    const toggle = header.querySelector('.field-toggle');
    
    if (content.classList.contains('expanded')) {
        content.classList.remove('expanded');
        toggle.classList.remove('expanded');
    } else {
        content.classList.add('expanded');
        toggle.classList.add('expanded');
    }
}

// Auto-expand first field on page load
document.addEventListener('DOMContentLoaded', function() {
    const firstField = document.querySelector('.field-group');
    if (firstField) {
        const header = firstField.querySelector('.field-header');
        const content = firstField.querySelector('.field-content');
        const toggle = firstField.querySelector('.field-toggle');
        
        content.classList.add('expanded');
        toggle.classList.add('expanded');
    }
});
</script>
</body>
</html> 