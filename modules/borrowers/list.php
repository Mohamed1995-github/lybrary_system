<?php
require_once '../../includes/auth.php';

$lang = $_SESSION['lang'] ?? 'ar';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $borrower_id = $_GET['delete'];
    
    // Check if borrower has any active loans
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE borrower_id = ? AND return_date IS NULL");
        $stmt->execute([$borrower_id]);
        $active_loans = $stmt->fetchColumn();
    } catch (PDOException $e) {
        // If borrower_id column doesn't exist, allow deletion
        $active_loans = 0;
    }
    
    if ($active_loans > 0) {
        $error_message = $lang == 'ar' ? 'لا يمكن حذف المستعير لوجود إعارات نشطة' : 'Impossible de supprimer l\'emprunteur car il a des emprunts actifs';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM borrowers WHERE id = ?");
            $stmt->execute([$borrower_id]);
            $success_message = $lang == 'ar' ? 'تم حذف المستعير بنجاح' : 'Emprunteur supprimé avec succès';
        } catch (PDOException $e) {
            $error_message = $lang == 'ar' ? 'خطأ في حذف المستعير' : 'Erreur lors de la suppression de l\'emprunteur';
        }
    }
}

// Fetch all borrowers with their loan statistics
try {
    $borrowers = $pdo->query("
        SELECT 
            b.*,
            COUNT(l.id) as total_loans,
            COUNT(CASE WHEN l.return_date IS NULL THEN 1 END) as active_loans
        FROM borrowers b
        LEFT JOIN loans l ON b.id = l.borrower_id
        GROUP BY b.id
        ORDER BY b.name
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if borrower_id column doesn't exist yet
    $borrowers = $pdo->query("
        SELECT 
            b.*,
            0 as total_loans,
            0 as active_loans
        FROM borrowers b
        ORDER BY b.name
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'قائمة المستعيرين' : 'Liste des emprunteurs' ?> - Library System</title>
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
    border: none;
}

.btn-primary:hover {
    background: var(--primary-hover);
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

.btn-danger {
    background: #dc2626;
    color: white;
    border: none;
}

.btn-danger:hover {
    background: #b91c1c;
    transform: translateY(-1px);
}

.btn-warning {
    background: #d97706;
    color: white;
    border: none;
}

.btn-warning:hover {
    background: #b45309;
    transform: translateY(-1px);
}

.content-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.data-table th {
    background: var(--bg-secondary);
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.data-table tr:hover {
    background: var(--bg-secondary);
}

.data-table td {
    color: var(--text-primary);
}

.borrower-name {
    font-weight: 600;
    color: var(--text-primary);
}

.borrower-info {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.loan-stats {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-md);
    background: var(--bg-secondary);
    color: var(--text-secondary);
}

.stat-item.active {
    background: rgb(239 68 68 / 0.1);
    color: #dc2626;
}

.stat-item.total {
    background: rgb(59 130 246 / 0.1);
    color: var(--primary-color);
}

.actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-btn-small {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    text-decoration: none;
    border-radius: var(--radius-md);
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.alert {
    padding: 1rem;
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-success {
    background: rgb(16 185 129 / 0.1);
    border: 1px solid rgb(16 185 129 / 0.2);
    color: var(--success-color);
}

.alert-error {
    background: rgb(239 68 68 / 0.1);
    border: 1px solid rgb(239 68 68 / 0.2);
    color: #dc2626;
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
    
    .data-table th,
    .data-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .loan-stats {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-btn-small {
        justify-content: center;
    }
}
</style>
</head>
<body>
<div class="page-container">
    <div class="page-header fade-in">
        <div class="page-title">
            <div class="page-icon">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'قائمة المستعيرين' : 'Liste des emprunteurs' ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'إدارة المستعيرين المسجلين في النظام' : 'Gestion des emprunteurs enregistrés dans le système' ?>
                </p>
            </div>
        </div>
        
        <div class="page-actions">
            <a href="add.php" class="action-btn btn-primary">
                <i class="fas fa-plus"></i>
                <?= $lang == 'ar' ? 'إضافة مستعير جديد' : 'Nouvel emprunteur' ?>
            </a>
            <a href="../loans/borrow.php" class="action-btn btn-secondary">
                <i class="fas fa-handshake"></i>
                <?= $lang == 'ar' ? 'إعارة جديدة' : 'Nouvel emprunt' ?>
            </a>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success fade-in">
            <i class="fas fa-check-circle"></i>
            <div><?= htmlspecialchars($success_message) ?></div>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error fade-in">
            <i class="fas fa-exclamation-triangle"></i>
            <div><?= htmlspecialchars($error_message) ?></div>
        </div>
    <?php endif; ?>

    <div class="content-card fade-in">
        <div class="table-container">
            <?php if (empty($borrowers)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3><?= $lang == 'ar' ? 'لا يوجد مستعيرين' : 'Aucun emprunteur' ?></h3>
                    <p><?= $lang == 'ar' ? 'ابدأ بإضافة مستعير جديد' : 'Commencez par ajouter un nouvel emprunteur' ?></p>
                    <a href="add.php" class="action-btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة مستعير جديد' : 'Nouvel emprunteur' ?>
                    </a>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= $lang == 'ar' ? 'المستعير' : 'Emprunteur' ?></th>
                            <th><?= $lang == 'ar' ? 'رقم الهوية' : 'ID National' ?></th>
                            <th><?= $lang == 'ar' ? 'الهاتف' : 'Téléphone' ?></th>
                            <th><?= $lang == 'ar' ? 'الإعارات' : 'Emprunts' ?></th>
                            <th><?= $lang == 'ar' ? 'تاريخ التسجيل' : 'Date d\'inscription' ?></th>
                            <th><?= $lang == 'ar' ? 'الإجراءات' : 'Actions' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowers as $borrower): ?>
                            <tr>
                                <td>
                                    <div class="borrower-name"><?= htmlspecialchars($borrower['name']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($borrower['national_id']) ?></td>
                                <td><?= htmlspecialchars($borrower['phone']) ?></td>
                                <td>
                                    <div class="loan-stats">
                                        <span class="stat-item total">
                                            <i class="fas fa-book"></i>
                                            <?= $borrower['total_loans'] ?>
                                        </span>
                                        <?php if ($borrower['active_loans'] > 0): ?>
                                            <span class="stat-item active">
                                                <i class="fas fa-clock"></i>
                                                <?= $borrower['active_loans'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?= date('Y-m-d', strtotime($borrower['created_at'])) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="../../public/router.php?module=borrowers&action=edit&id=<?= $borrower["id'] ?>" class="action-btn-small btn-warning">
                                            <i class="fas fa-edit"></i>
                                            <?= $lang == 'ar' ? 'تعديل' : 'Modifier' ?>
                                        </a>
                                        <?php if ($borrower['active_loans'] == 0): ?>
                                            <a href="?delete=<?= $borrower['id'] ?>" 
                                               class="action-btn-small btn-danger"
                                               onclick="return confirm('<?= $lang == 'ar' ? 'هل أنت متأكد من حذف هذا المستعير؟' : 'Êtes-vous sûr de vouloir supprimer cet emprunteur ?' ?>')">
                                                <i class="fas fa-trash"></i>
                                                <?= $lang == 'ar' ? 'حذف' : 'Supprimer' ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html> 