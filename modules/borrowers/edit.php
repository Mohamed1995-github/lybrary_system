<?php
require_once '../../includes/auth.php';

$lang = $_SESSION['lang'] ?? 'ar';

// Get borrower ID from URL
$borrower_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$borrower_id) {
    header("Location: list.php");
    exit;
}

// Fetch borrower data
$stmt = $pdo->prepare("SELECT * FROM borrowers WHERE id = ?");
$stmt->execute([$borrower_id]);
$borrower = $stmt->fetch();

if (!$borrower) {
    header("Location: list.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $national_id = trim($_POST['national_id']);
    $phone = trim($_POST['phone']);
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = $lang == 'ar' ? 'اسم المستعير مطلوب' : 'Le nom de l\'emprunteur est requis';
    }
    
    if (empty($national_id)) {
        $errors[] = $lang == 'ar' ? 'رقم الهوية الوطنية مطلوب' : 'Le numéro d\'identité nationale est requis';
    }
    
    if (empty($phone)) {
        $errors[] = $lang == 'ar' ? 'رقم الهاتف مطلوب' : 'Le numéro de téléphone est requis';
    }
    
    // Check if national_id already exists (excluding current borrower)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM borrowers WHERE national_id = ? AND id != ?");
        $stmt->execute([$national_id, $borrower_id]);
        if ($stmt->fetch()) {
            $errors[] = $lang == 'ar' ? 'رقم الهوية الوطنية موجود مسبقاً' : 'Ce numéro d\'identité nationale existe déjà';
        }
    }
    
    // Update borrower if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE borrowers SET name = ?, national_id = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $national_id, $phone, $borrower_id]);
            
            $success_message = $lang == 'ar' ? 'تم تحديث المستعير بنجاح' : 'Emprunteur mis à jour avec succès';
            
            // Redirect to list page after successful update
            header("Location: list.php?success=1");
            exit;
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'تعديل المستعير' : 'Modifier l\'emprunteur' ?> - Library System</title>
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

.form-input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    font-size: 1rem;
    transition: all 0.2s ease;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
}

.form-input::placeholder {
    color: var(--text-light);
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

.btn-warning {
    background: #d97706;
}

.btn-warning:hover {
    background: #b45309;
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
                <i class="fas fa-user-edit"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'تعديل المستعير' : 'Modifier l\'emprunteur' ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'تعديل معلومات المستعير' : 'Modifier les informations de l\'emprunteur' ?>
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
                <i class="fas fa-user-edit"></i>
                <?= $lang == 'ar' ? 'معلومات المستعير' : 'Informations de l\'emprunteur' ?>
            </h2>
            <p class="form-subtitle">
                <?= $lang == 'ar' ? 'تعديل معلومات المستعير' : 'Modifier les informations de l\'emprunteur' ?>
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" id="borrowerForm">
            <div class="form-group">
                <label for="name" class="form-label">
                    <i class="fas fa-user"></i>
                    <?= $lang == 'ar' ? 'الاسم الكامل *' : 'Nom complet *' ?>
                </label>
                <input type="text" name="name" id="name" class="form-input" 
                       value="<?= htmlspecialchars($_POST['name'] ?? $borrower['name']) ?>" 
                       placeholder="<?= $lang == 'ar' ? 'أدخل الاسم الكامل' : 'Entrez le nom complet' ?>" required>
            </div>

            <div class="form-group">
                <label for="national_id" class="form-label">
                    <i class="fas fa-id-card"></i>
                    <?= $lang == 'ar' ? 'رقم الهوية الوطنية *' : 'Numéro d\'identité nationale *' ?>
                </label>
                <input type="text" name="national_id" id="national_id" class="form-input" 
                       value="<?= htmlspecialchars($_POST['national_id'] ?? $borrower['national_id']) ?>" 
                       placeholder="<?= $lang == 'ar' ? 'أدخل رقم الهوية الوطنية' : 'Entrez le numéro d\'identité nationale' ?>" required>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">
                    <i class="fas fa-phone"></i>
                    <?= $lang == 'ar' ? 'رقم الهاتف *' : 'Numéro de téléphone *' ?>
                </label>
                <input type="tel" name="phone" id="phone" class="form-input" 
                       value="<?= htmlspecialchars($_POST['phone'] ?? $borrower['phone']) ?>" 
                       placeholder="<?= $lang == 'ar' ? 'أدخل رقم الهاتف' : 'Entrez le numéro de téléphone' ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary btn-warning">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'حفظ التغييرات' : 'Enregistrer les modifications' ?>
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
document.getElementById('borrowerForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري الحفظ...' : 'Enregistrement...' ?>';
    submitBtn.disabled = true;
});
</script>
</body>
</html> 