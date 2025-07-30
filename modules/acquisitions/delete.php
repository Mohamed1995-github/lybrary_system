<?php
/* modules/acquisitions/delete.php */
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_GET['lang'] ?? 'ar';
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: list.php?lang=$lang");
    exit;
}

// Vérifier si l'acquisition existe
try {
    $stmt = $pdo->prepare("SELECT * FROM acquisitions WHERE id = ? AND lang = ?");
    $stmt->execute([$id, $lang]);
    $acquisition = $stmt->fetch();
    
    if (!$acquisition) {
        header("Location: list.php?lang=$lang");
        exit;
    }
} catch (PDOException $e) {
    header("Location: list.php?lang=$lang");
    exit;
}

// Supprimer l'acquisition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM acquisitions WHERE id = ?");
        $stmt->execute([$id]);
        
        $success = $lang == 'ar' ? 'تم حذف الاستحواذ بنجاح!' : 'Acquisition supprimée avec succès!';
        
        // Rediriger après 2 secondes
        header("refresh:2;url=list.php?lang=$lang");
    } catch (PDOException $e) {
        $error = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'حذف الاستحواذ' : 'Supprimer l\'acquisition' ?> - Library System</title>
<link rel="stylesheet" href="../../public/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../../public/assets/js/script.js"></script>
<style>
.page-container {
    min-height: 100vh;
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.confirmation-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 3rem;
    border: 1px solid var(--border-color);
    max-width: 500px;
    width: 100%;
    text-align: center;
}

.confirmation-icon {
    font-size: 4rem;
    color: var(--error-color);
    margin-bottom: 1.5rem;
}

.confirmation-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.confirmation-message {
    color: var(--text-secondary);
    margin-bottom: 2rem;
    line-height: 1.6;
}

.acquisition-details {
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 2rem;
    text-align: left;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color);
}

.detail-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.detail-label {
    font-weight: 500;
    color: var(--text-secondary);
}

.detail-value {
    font-weight: 600;
    color: var(--text-primary);
}

.confirmation-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
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
    border: none;
    cursor: pointer;
}

.btn-danger {
    background: var(--error-color);
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
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

.alert {
    padding: 1rem;
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: rgb(34 197 94 / 0.1);
    color: var(--success-color);
    border: 1px solid rgb(34 197 94 / 0.2);
}

.alert-error {
    background: rgb(239 68 68 / 0.1);
    color: var(--error-color);
    border: 1px solid rgb(239 68 68 / 0.2);
}

@media (max-width: 768px) {
    .page-container {
        padding: 1rem;
    }
    
    .confirmation-card {
        padding: 2rem;
        margin: 0 1rem;
    }
    
    .confirmation-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>
<div class="page-container">
    <div class="confirmation-card fade-in">
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
            <p style="color: var(--text-secondary);">
                <?= $lang == 'ar' ? 'سيتم توجيهك إلى قائمة الاستحواذات...' : 'Vous serez redirigé vers la liste des acquisitions...' ?>
            </p>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>
            <div class="confirmation-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h1 class="confirmation-title">
                <?= $lang == 'ar' ? 'تأكيد الحذف' : 'Confirmer la suppression' ?>
            </h1>
            
            <p class="confirmation-message">
                <?= $lang == 'ar' ? 'هل أنت متأكد من حذف هذا الاستحواذ؟ هذا الإجراء لا يمكن التراجع عنه.' : 'Êtes-vous sûr de vouloir supprimer cette acquisition ? Cette action ne peut pas être annulée.' ?>
            </p>
            
            <div class="acquisition-details">
                <div class="detail-item">
                    <span class="detail-label"><?= $lang == 'ar' ? 'المزود:' : 'Fournisseur:' ?></span>
                    <span class="detail-value"><?= htmlspecialchars($acquisition['supplier_name']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><?= $lang == 'ar' ? 'التكلفة:' : 'Coût:' ?></span>
                    <span class="detail-value">
                        <?php if ($acquisition['cost'] > 0): ?>
                            <?= number_format($acquisition['cost'], 2) ?> <?= $lang == 'ar' ? 'د.ج' : 'DA' ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><?= $lang == 'ar' ? 'الكمية:' : 'Quantité:' ?></span>
                    <span class="detail-value"><?= $acquisition['quantity'] ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><?= $lang == 'ar' ? 'التاريخ:' : 'Date:' ?></span>
                    <span class="detail-value"><?= date('d/m/Y', strtotime($acquisition['acquired_date'])) ?></span>
                </div>
            </div>
            
            <form method="post" class="confirmation-actions">
                <input type="hidden" name="lang" value="<?= $lang ?>">
                <button type="submit" class="action-btn btn-danger">
                    <i class="fas fa-trash"></i>
                    <?= $lang == 'ar' ? 'نعم، احذف' : 'Oui, supprimer' ?>
                </button>
                <a href="list.php?lang=<?=$lang?>" class="action-btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                </a>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html> 