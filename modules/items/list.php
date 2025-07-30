<?php
/* modules/items/list.php */
require_once __DIR__ . '/../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../public/login.php'); 
    exit; 
}

// Accès complet pour tous les utilisateurs authentifiés
$lang = $_GET['lang'] ?? 'ar';
$type = $_GET['type'] ?? 'book';

// استدعاء المواد حسب اللغة والنوع
$stmt = $pdo->prepare("SELECT * FROM items WHERE lang = ? AND type = ? ORDER BY title");
$stmt->execute([$lang, $type]);
$rows = $stmt->fetchAll();

// Get type-specific information
$type_info = [
    'book' => [
        'ar' => ['title' => 'الكتب', 'add' => 'إضافة كتاب جديد', 'icon' => 'fas fa-book'],
        'fr' => ['title' => 'Livres', 'add' => 'Ajouter un nouveau livre', 'icon' => 'fas fa-book']
    ],
    'magazine' => [
        'ar' => ['title' => 'المجلات', 'add' => 'إضافة مجلة جديدة', 'icon' => 'fas fa-newspaper'],
        'fr' => ['title' => 'Revues', 'add' => 'Ajouter une nouvelle revue', 'icon' => 'fas fa-newspaper']
    ],
    'newspaper' => [
        'ar' => ['title' => 'الجرائد', 'add' => 'إضافة جريدة جديدة', 'icon' => 'fas fa-newspaper'],
        'fr' => ['title' => 'Journaux', 'add' => 'Ajouter un nouveau journal', 'icon' => 'fas fa-newspaper']
    ],
    'general' => [
        'ar' => ['title' => 'المصادر العامة', 'add' => 'إضافة مصدر عام جديد', 'icon' => 'fas fa-book-open'],
        'fr' => ['title' => 'Sources générales', 'add' => 'Ajouter une nouvelle source générale', 'icon' => 'fas fa-book-open']
    ]
];

// Accès complet - tous les utilisateurs peuvent tout faire
$canAdd = true;
$canEdit = true;
$canDelete = true;
$canView = true;

// تسجيل الوصول
$log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - FULL_ACCESS to items list (type: {$type}) - IP: {$_SERVER['REMOTE_ADDR']}\n";
file_put_contents(__DIR__ . '/../../logs/access.log', $log_entry, FILE_APPEND | LOCK_EX);
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'قائمة المواد' : 'Liste des Matériaux' ?> - <?= $type_info[$type][$lang]['title'] ?></title>
<link rel="stylesheet" href="../../public/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

.data-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    border: 1px solid var(--border-color);
}

.table-container {
    overflow-x: auto;
}

table {
    margin: 0;
    border-radius: 0;
    box-shadow: none;
}

th {
    background: var(--bg-secondary);
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 1rem;
    border-bottom: 2px solid var(--border-color);
}

td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

tr:hover {
    background: var(--bg-secondary);
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

.status-available {
    background: rgb(34 197 94 / 0.1);
    color: rgb(34 197 94);
}

.status-limited {
    background: rgb(245 158 11 / 0.1);
    color: rgb(245 158 11);
}

.status-unavailable {
    background: rgb(239 68 68 / 0.1);
    color: rgb(239 68 68);
}

.table-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
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
    color: rgb(59 130 246);
}

.action-edit:hover {
    background: rgb(59 130 246);
    color: white;
    transform: scale(1.1);
}

.action-delete {
    background: rgb(239 68 68 / 0.1);
    color: rgb(239 68 68);
}

.action-delete:hover {
    background: rgb(239 68 68);
    color: white;
    transform: scale(1.1);
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.access-info {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 1rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.access-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.access-status.success {
    color: rgb(34 197 94);
}

.access-status.info {
    color: rgb(59 130 246);
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .table-container {
        font-size: 0.875rem;
    }
    
    th, td {
        padding: 0.75rem 0.5rem;
    }
}
</style>
</head>
<body>
<div class="page-container">
    <!-- معلومات الوصول -->
    <div class="access-info">
        <div class="access-status success">
            <i class="fas fa-check-circle"></i>
            <?= $lang == 'ar' ? '✅ وصول كامل لجميع الوظائف' : '✅ Accès complet à toutes les fonctionnalités' ?>
        </div>
        <div class="access-status info">
            <i class="fas fa-user"></i>
            <?= $lang == 'ar' ? 'المستخدم: ' . ($_SESSION['username'] ?? $_SESSION['uid']) : 'Utilisateur: ' . ($_SESSION['username'] ?? $_SESSION['uid']) ?>
        </div>
    </div>

    <!-- رأس الصفحة -->
    <div class="page-header">
        <div class="page-title">
            <div class="page-icon">
                <i class="<?= $type_info[$type][$lang]['icon'] ?>"></i>
            </div>
            <div>
                <h1><?= $type_info[$type][$lang]['title'] ?></h1>
                <p class="text-secondary"><?= $lang == 'ar' ? 'إدارة ' . $type_info[$type][$lang]['title'] : 'Gestion des ' . $type_info[$type][$lang]['title'] ?></p>
            </div>
        </div>
        
        <div class="page-actions">
            <?php if ($canAdd): ?>
                <?php if ($type == 'book'): ?>
                    <a href="../../public/router.php?module=items&action=add_book&lang=<?=$lang?>" class="action-btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة كتاب' : 'Ajouter un livre' ?>
                    </a>
                <?php elseif ($type == 'magazine'): ?>
                    <a href="../../public/router.php?module=items&action=add_magazine&lang=<?=$lang?>" class="action-btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة مجلة' : 'Ajouter une revue' ?>
                    </a>
                <?php elseif ($type == 'newspaper'): ?>
                    <a href="../../public/router.php?module=items&action=add_newspaper&lang=<?=$lang?>" class="action-btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة جريدة' : 'Ajouter un journal' ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <a href="../../public/dashboard.php" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
            </a>
        </div>
    </div>

    <!-- بطاقة البيانات -->
    <div class="data-card">
        <?php if (empty($rows)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3><?= $lang == 'ar' ? 'لا توجد ' . $type_info[$type][$lang]['title'] : 'Aucun ' . $type_info[$type][$lang]['title'] ?></h3>
                <p><?= $lang == 'ar' ? 'لم يتم إضافة أي ' . $type_info[$type][$lang]['title'] . ' بعد.' : 'Aucun ' . $type_info[$type][$lang]['title'] . ' n\'a été ajouté pour le moment.' ?></p>
                <?php if ($canAdd): ?>
                    <?php if ($type == 'book'): ?>
                        <a href="../../public/router.php?module=items&action=add_book&lang=<?=$lang?>" class="action-btn btn-primary">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة أول كتاب' : 'Ajouter le premier livre' ?>
                        </a>
                    <?php elseif ($type == 'magazine'): ?>
                        <a href="../../public/router.php?module=items&action=add_magazine&lang=<?=$lang?>" class="action-btn btn-primary">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة أول مجلة' : 'Ajouter la première revue' ?>
                        </a>
                    <?php elseif ($type == 'newspaper'): ?>
                        <a href="../../public/router.php?module=items&action=add_newspaper&lang=<?=$lang?>" class="action-btn btn-primary">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة أول جريدة' : 'Ajouter le premier journal' ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <?php if ($type == 'book'): ?>
                                <th><?= $lang == 'ar' ? 'العنوان' : 'Titre' ?></th>
                                <th><?= $lang == 'ar' ? 'النوع' : 'Type' ?></th>
                                <th><?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?></th>
                                <th><?= $lang == 'ar' ? 'الحالة' : 'Statut' ?></th>
                            <?php elseif ($type == 'magazine'): ?>
                                <th><?= $lang == 'ar' ? 'العنوان' : 'Titre' ?></th>
                                <th><?= $lang == 'ar' ? 'العدد' : 'Numéro' ?></th>
                                <th><?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?></th>
                                <th><?= $lang == 'ar' ? 'التكرار' : 'Fréquence' ?></th>
                                <th><?= $lang == 'ar' ? 'الحالة' : 'Statut' ?></th>
                            <?php elseif ($type == 'newspaper'): ?>
                                <th><?= $lang == 'ar' ? 'العنوان' : 'Titre' ?></th>
                                <th><?= $lang == 'ar' ? 'العدد' : 'Numéro' ?></th>
                                <th><?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?></th>
                                <th><?= $lang == 'ar' ? 'التكرار' : 'Fréquence' ?></th>
                                <th><?= $lang == 'ar' ? 'الحالة' : 'Statut' ?></th>
                            <?php else: ?>
                                <th><?= $lang == 'ar' ? 'العنوان' : 'Titre' ?></th>
                                <th><?= $lang == 'ar' ? 'النوع' : 'Type' ?></th>
                                <th><?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?></th>
                                <th><?= $lang == 'ar' ? 'الحالة' : 'Statut' ?></th>
                            <?php endif; ?>
                            
                            <?php if ($canEdit || $canDelete): ?>
                                <th><?= $lang == 'ar' ? 'الإجراءات' : 'Actions' ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php if ($type == 'book'): ?>
                                    <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['book_type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['publisher'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $available = $row['copies_in'];
                                        $total = $row['copies_total'];
                                        
                                        if ($available == 0) {
                                            echo '<span class="status-badge status-unavailable">';
                                            echo '<i class="fas fa-times"></i>';
                                            echo $lang == 'ar' ? 'غير متاح' : 'Indisponible';
                                            echo '</span>';
                                        } elseif ($available < $total) {
                                            echo '<span class="status-badge status-limited">';
                                            echo '<i class="fas fa-exclamation"></i>';
                                            echo $lang == 'ar' ? 'متاح جزئياً' : 'Partiellement disponible';
                                            echo '</span>';
                                        } else {
                                            echo '<span class="status-badge status-available">';
                                            echo '<i class="fas fa-check"></i>';
                                            echo $lang == 'ar' ? 'متاح' : 'Disponible';
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                <?php elseif ($type == 'magazine'): ?>
                                    <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['magazine_number'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['publisher'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['frequency'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $available = $row['copies_in'];
                                        $total = $row['copies_total'];
                                        
                                        if ($available == 0) {
                                            echo '<span class="status-badge status-unavailable">';
                                            echo '<i class="fas fa-times"></i>';
                                            echo $lang == 'ar' ? 'غير متاح' : 'Indisponible';
                                            echo '</span>';
                                        } elseif ($available < $total) {
                                            echo '<span class="status-badge status-limited">';
                                            echo '<i class="fas fa-exclamation"></i>';
                                            echo $lang == 'ar' ? 'متاح جزئياً' : 'Partiellement disponible';
                                            echo '</span>';
                                        } else {
                                            echo '<span class="status-badge status-available">';
                                            echo '<i class="fas fa-check"></i>';
                                            echo $lang == 'ar' ? 'متاح' : 'Disponible';
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                <?php elseif ($type == 'newspaper'): ?>
                                    <?php
                                    // ترجمة اسم الجريدة
                                    $newspaper_titles = [
                                        'Ech-Chaab' => ['ar' => 'الشعب', 'fr' => 'Ech-Chaab'],
                                        'Journal Officiel' => ['ar' => 'الجريدة الرسمية', 'fr' => 'Journal Officiel']
                                    ];
                                    $display_title = $newspaper_titles[$row['title']][$lang] ?? $row['title'];
                                    ?>
                                    <td><strong><?= htmlspecialchars($display_title) ?></strong></td>
                                    <td><?= htmlspecialchars($row['newspaper_number'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['publisher'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['frequency'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $available = $row['copies_in'];
                                        $total = $row['copies_total'];
                                        
                                        if ($available == 0) {
                                            echo '<span class="status-badge status-unavailable">';
                                            echo '<i class="fas fa-times"></i>';
                                            echo $lang == 'ar' ? 'غير متاح' : 'Indisponible';
                                            echo '</span>';
                                        } elseif ($available < $total) {
                                            echo '<span class="status-badge status-limited">';
                                            echo '<i class="fas fa-exclamation"></i>';
                                            echo $lang == 'ar' ? 'متاح جزئياً' : 'Partiellement disponible';
                                            echo '</span>';
                                        } else {
                                            echo '<span class="status-badge status-available">';
                                            echo '<i class="fas fa-check"></i>';
                                            echo $lang == 'ar' ? 'متاح' : 'Disponible';
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                <?php else: ?>
                                    <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['book_type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['publisher'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $available = $row['copies_in'];
                                        $total = $row['copies_total'];
                                        
                                        if ($available == 0) {
                                            echo '<span class="status-badge status-unavailable">';
                                            echo '<i class="fas fa-times"></i>';
                                            echo $lang == 'ar' ? 'غير متاح' : 'Indisponible';
                                            echo '</span>';
                                        } elseif ($available < $total) {
                                            echo '<span class="status-badge status-limited">';
                                            echo '<i class="fas fa-exclamation"></i>';
                                            echo $lang == 'ar' ? 'متاح جزئياً' : 'Partiellement disponible';
                                            echo '</span>';
                                        } else {
                                            echo '<span class="status-badge status-available">';
                                            echo '<i class="fas fa-check"></i>';
                                            echo $lang == 'ar' ? 'متاح' : 'Disponible';
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                <?php endif; ?>
                                
                                <?php if ($canEdit || $canDelete): ?>
                                    <td class="table-actions">
                                        <?php if ($canEdit): ?>
                                            <a href="../../public/router.php?module=items&action=add_edit&lang=<?=$lang?>&type=<?=$type?>&id=<?=$row['id']?>" class="action-icon action-edit" title="<?= $lang == 'ar' ? 'تعديل' : 'Modifier' ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($canDelete): ?>
                                            <a href="../../public/router.php?module=items&action=delete&lang=<?=$lang?>&type=<?=$type?>&id=<?=$row['id']?>" class="action-icon action-delete" title="<?= $lang == 'ar' ? 'حذف' : 'Supprimer' ?>" onclick="return confirm('<?= $lang == 'ar' ? 'هل أنت متأكد من حذف هذا العنصر؟' : 'Êtes-vous sûr de vouloir supprimer cet élément ?' ?>')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
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
