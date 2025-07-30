<?php
require_once '../../includes/auth.php';

$lang = $_SESSION['lang'] ?? 'ar';

// Récupérer les items disponibles
$items = $pdo->query("SELECT id, title FROM items WHERE copies_in > 0")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les emprunteurs
$borrowers = $pdo->query("SELECT id, name FROM borrowers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'إضافة إعارة جديدة' : 'Nouvel emprunt' ?> - Library System</title>
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

.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 2px solid var(--border-color);
}

.btn-secondary:hover {
    background: var(--bg-tertiary);
    transform: translateY(-1px);
}

.form-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 2.5rem;
    border: 1px solid var(--border-color);
    max-width: 600px;
    margin: 0 auto;
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid var(--border-color);
}

.form-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.form-subtitle {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.form-input, .form-select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    font-size: 1rem;
    transition: all 0.2s ease;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.form-input:focus, .form-select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
}

.form-input::placeholder {
    color: var(--text-light);
}

.form-select {
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    padding-top: 2rem;
    border-top: 2px solid var(--border-color);
}

.btn-primary {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 150px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-primary:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.btn-success {
    background: var(--success-color);
}

.btn-success:hover {
    background: #059669;
}

.alert {
    padding: 1rem;
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-error {
    background: rgb(239 68 68 / 0.1);
    border: 1px solid rgb(239 68 68 / 0.2);
    color: #dc2626;
}

.form-info {
    background: rgb(59 130 246 / 0.1);
    border: 1px solid rgb(59 130 246 / 0.2);
    border-radius: var(--radius-lg);
    padding: 1rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--primary-color);
}

.form-info i {
    font-size: 1.25rem;
}

.available-items {
    background: rgb(16 185 129 / 0.1);
    border: 1px solid rgb(16 185 129 / 0.2);
    border-radius: var(--radius-lg);
    padding: 1rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--success-color);
}

.available-items i {
    font-size: 1.25rem;
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
    
    .form-card {
        padding: 1.5rem;
        margin: 0 1rem;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-primary {
        width: 100%;
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
                <h1><?= $lang == 'ar' ? 'إضافة إعارة جديدة' : 'Nouvel emprunt' ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'إعارة كتاب أو مادة من المكتبة' : 'Emprunter un livre ou un matériau de la bibliothèque' ?>
                </p>
            </div>
        </div>
        
        <div class="page-actions">
            <a href="list.php" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
            </a>
        </div>
    </div>

    <div class="form-card fade-in">
        <div class="form-header">
            <h2 class="form-title">
                <i class="fas fa-book"></i>
                <?= $lang == 'ar' ? 'إعارة جديدة' : 'Nouvel emprunt' ?>
            </h2>
            <p class="form-subtitle">
                <?= $lang == 'ar' ? 'اختر الكتاب والمستعير لإتمام عملية الإعارة' : 'Sélectionnez le livre et l\'emprunteur pour finaliser l\'emprunt' ?>
            </p>
        </div>

        <div class="form-info">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong><?= $lang == 'ar' ? 'ملاحظة:' : 'Note:' ?></strong> 
                <?= $lang == 'ar' ? 'سيتم تعيين تاريخ الاستحقاق تلقائياً بعد 14 يوم من تاريخ الإعارة' : 'La date d\'échéance sera automatiquement fixée à 14 jours après la date d\'emprunt' ?>
            </div>
        </div>

        <div class="available-items">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong><?= count($items) ?></strong> 
                <?= $lang == 'ar' ? 'مادة متاحة للإعارة' : 'matériau(x) disponible(s) pour l\'emprunt' ?>
            </div>
        </div>

        <?php if (empty($borrowers)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong><?= $lang == 'ar' ? 'تحذير:' : 'Attention:' ?></strong> 
                    <?= $lang == 'ar' ? 'لا يوجد مستعيرين مسجلين في النظام. يجب إضافة مستعيرين أولاً.' : 'Aucun emprunteur enregistré dans le système. Vous devez d\'abord ajouter des emprunteurs.' ?>
                    <br>
                    <a href="../borrowers/add.php" class="action-btn btn-primary" style="margin-top: 0.5rem; display: inline-block;">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة مستعير جديد' : 'Ajouter un emprunteur' ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="add.php" id="borrowForm">
            <div class="form-group">
                <label for="item_id" class="form-label">
                    <i class="fas fa-book"></i>
                    <?= $lang == 'ar' ? 'اختر المادة *' : 'Sélectionner le matériau *' ?>
                </label>
                <select name="item_id" id="item_id" class="form-select" required>
                    <option value=""><?= $lang == 'ar' ? '-- اختر المادة --' : '-- Sélectionner le matériau --' ?></option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?=$item['id']?>"><?=$item['title']?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="borrower_id" class="form-label">
                    <i class="fas fa-user"></i>
                    <?= $lang == 'ar' ? 'اختر المستعير *' : 'Sélectionner l\'emprunteur *' ?>
                </label>
                <select name="borrower_id" id="borrower_id" class="form-select" required <?= empty($borrowers) ? 'disabled' : '' ?>>
                    <option value=""><?= $lang == 'ar' ? '-- اختر المستعير --' : '-- Sélectionner l\'emprunteur --' ?></option>
                    <?php foreach ($borrowers as $borrower): ?>
                        <option value="<?=$borrower['id']?>"><?=$borrower['name']?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary btn-success">
                    <i class="fas fa-paper-plane"></i>
                    <?= $lang == 'ar' ? 'تأكيد الإعارة' : 'Confirmer l\'emprunt' ?>
                </button>
                <a href="list.php" class="action-btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Form submission enhancement
document.getElementById('borrowForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري المعالجة...' : 'Traitement...' ?>';
    submitBtn.disabled = true;
    
    // Show success notification after a short delay
    setTimeout(() => {
        if (window.LibrarySystem) {
            window.LibrarySystem.showNotification(
                '<?= $lang == 'ar' ? 'تم إنشاء الإعارة بنجاح!' : 'Emprunt créé avec succès!' ?>', 
                'success'
            );
        }
    }, 1000);
});
</script>
</body>
</html>
