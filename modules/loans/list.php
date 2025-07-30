<?php
/* modules/loans/list.php */
require_once __DIR__.'/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_SESSION['lang'] ?? 'ar';

// عرض الإعارات النشطة فقط
$sql = "SELECT l.*, i.title, b.name AS borrower_name,
        DATEDIFF(CURDATE(), l.due_date) AS late
        FROM loans l
        JOIN items i ON i.id = l.item_id
        JOIN borrowers b ON b.id = l.borrowers_id
        WHERE l.return_date IS NULL
        ORDER BY l.due_date ASC";
$rows = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'إدارة الإعارة' : 'Gestion des Emprunts' ?> - Library System</title>
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
    padding: 1.25rem 1rem;
}

td {
    padding: 1.25rem 1rem;
    vertical-align: middle;
}

tr.late {
    background: rgb(239 68 68 / 0.05);
    border-left: 4px solid var(--error-color);
}

tr.late:hover {
    background: rgb(239 68 68 / 0.1);
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

.status-active {
    background: rgb(59 130 246 / 0.1);
    color: var(--primary-color);
}

.status-late {
    background: rgb(239 68 68 / 0.1);
    color: var(--error-color);
}

.status-returned {
    background: rgb(16 185 129 / 0.1);
    color: var(--success-color);
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
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.action-return {
    background: rgb(16 185 129 / 0.1);
    color: var(--success-color);
}

.action-return:hover {
    background: var(--success-color);
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    text-align: center;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
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
    
    .table-container {
        font-size: 0.875rem;
    }
    
    th, td {
        padding: 0.75rem 0.5rem;
    }
    
    .table-actions {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .action-icon {
        width: 2rem;
        height: 2rem;
        font-size: 0.875rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>
<div class="page-container">
    <div class="page-header fade-in">
        <div class="page-title">
            <div class="page-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'إدارة الإعارة' : 'Gestion des Emprunts' ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'تتبع الإعارات النشطة والمتأخرة' : 'Suivi des emprunts actifs et en retard' ?>
                </p>
            </div>
        </div>
        
        <div class="page-actions">
            <a href="borrow.php" class="action-btn btn-primary">
                <i class="fas fa-plus"></i>
                <?= $lang == 'ar' ? 'إعارة جديدة' : 'Nouvel emprunt' ?>
            </a>
            <a href="/library_system/public/dashboard.php" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
            </a>
        </div>
    </div>

    <?php
    $totalLoans = count($rows);
    $lateLoans = count(array_filter($rows, function($r) { return $r['late'] > 0; }));
    $activeLoans = $totalLoans - $lateLoans;
    ?>

    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="stat-number"><?= $totalLoans ?></div>
            <div class="stat-label"><?= $lang == 'ar' ? 'إجمالي الإعارات' : 'Total des emprunts' ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: var(--success-color);"><?= $activeLoans ?></div>
            <div class="stat-label"><?= $lang == 'ar' ? 'إعارات نشطة' : 'Emprunts actifs' ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: var(--error-color);"><?= $lateLoans ?></div>
            <div class="stat-label"><?= $lang == 'ar' ? 'إعارات متأخرة' : 'Emprunts en retard' ?></div>
        </div>
    </div>

    <div class="data-card fade-in">
        <?php if (empty($rows)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="empty-title">
                    <?= $lang == 'ar' ? 'لا توجد إعارات نشطة' : 'Aucun emprunt actif' ?>
                </h3>
                <p class="empty-description">
                    <?= $lang == 'ar' ? 'جميع الكتب تم إرجاعها أو لم يتم إعارة أي كتاب بعد.' : 'Tous les livres ont été retournés ou aucun livre n\'a encore été emprunté.' ?>
                </p>
                <a href="borrow.php" class="action-btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إعارة أول كتاب' : 'Premier emprunt' ?>
                </a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?= $lang == 'ar' ? 'العنوان' : 'Titre' ?></th>
                            <th><?= $lang == 'ar' ? 'المستعير' : 'Emprunteur' ?></th>
                            <th><?= $lang == 'ar' ? 'تاريخ الإعارة' : 'Date d\'emprunt' ?></th>
                            <th><?= $lang == 'ar' ? 'تاريخ الاستحقاق' : 'Date d\'échéance' ?></th>
                            <th><?= $lang == 'ar' ? 'الحالة' : 'Statut' ?></th>
                            <th><?= $lang == 'ar' ? 'الإجراءات' : 'Actions' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rows as $r): ?>
                            <tr class="<?=$r['late']>0?'late':''?>">
                                <td><strong>#<?=$r['id']?></strong></td>
                                <td>
                                    <div style="font-weight: 500; color: var(--text-primary);">
                                        <?=$r['title']?>
                                    </div>
                                </td>
                                <td><?=$r['borrower_name']?></td>
                                <td><?=date('d/m/Y', strtotime($r['borrow_date']))?></td>
                                <td>
                                    <div style="font-weight: 500;">
                                        <?=date('d/m/Y', strtotime($r['due_date']))?>
                                    </div>
                                    <?php if ($r['late'] > 0): ?>
                                        <div style="font-size: 0.75rem; color: var(--error-color); margin-top: 0.25rem;">
                                            <?= $lang == 'ar' ? 'متأخر بـ ' . $r['late'] . ' يوم' : 'En retard de ' . $r['late'] . ' jour(s)' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['late'] > 0): ?>
                                        <span class="status-badge status-late">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <?= $lang == 'ar' ? 'متأخر' : 'En retard' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-active">
                                            <i class="fas fa-clock"></i>
                                            <?= $lang == 'ar' ? 'نشط' : 'Actif' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="../../public/router.php?module=loans&action=return&id=<?=$r["id']?>" class="action-icon action-return"
                                           title="<?= $lang == 'ar' ? 'إرجاع الكتاب' : 'Retourner le livre' ?>">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    </div>
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
