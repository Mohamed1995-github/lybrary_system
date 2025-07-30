<?php
/* modules/acquisitions/edit.php */
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_GET['lang'] ?? 'ar';
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: list.php?lang=$lang");
    exit;
}

// Options pour le type de source
$source_types = [
    'book' => ['ar' => 'كتب', 'fr' => 'Livres'],
    'magazine' => ['ar' => 'مجلات', 'fr' => 'Revues / Magazines'],
    'other' => ['ar' => 'مصادر أخرى', 'fr' => 'Autre source']
];

// Options pour la méthode d'approvisionnement
$acquisition_methods = [
    'purchase' => ['ar' => 'شراء', 'fr' => 'Achat'],
    'exchange' => ['ar' => 'تبادل', 'fr' => 'Échange'],
    'donation' => ['ar' => 'إهداء', 'fr' => 'Don']
];

// Options pour le type de fournisseur
$supplier_types = [
    'institution' => ['ar' => 'مؤسسة', 'fr' => 'Institution'],
    'publisher' => ['ar' => 'دار النشر', 'fr' => 'Maison d\'édition'],
    'person' => ['ar' => 'شخص', 'fr' => 'Personne']
];

// Récupérer l'acquisition
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source_type = $_POST['source_type'] ?? 'book';
    $acquisition_method = $_POST['acquisition_method'] ?? 'purchase';
    $supplier_type = $_POST['supplier_type'] ?? 'publisher';
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $supplier_phone = trim($_POST['supplier_phone'] ?? '');
    $supplier_address = trim($_POST['supplier_address'] ?? '');
    $cost = (float)($_POST['cost'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $acquired_date = $_POST['acquired_date'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($supplier_name)) {
        $errors[] = $lang == 'ar' ? 'اسم المزود مطلوب' : 'Le nom du fournisseur est requis';
    }
    
    if ($quantity < 1) {
        $errors[] = $lang == 'ar' ? 'الكمية يجب أن تكون 1 على الأقل' : 'La quantité doit être au moins 1';
    }
    
    if ($cost < 0) {
        $errors[] = $lang == 'ar' ? 'التكلفة لا يمكن أن تكون سالبة' : 'Le coût ne peut pas être négatif';
    }
    
    // Si pas d'erreurs, mettre à jour l'acquisition
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE acquisitions SET source_type=?, acquisition_method=?, supplier_type=?, supplier_name=?, supplier_phone=?, supplier_address=?, cost=?, quantity=?, acquired_date=?, notes=? WHERE id=?");
            $stmt->execute([$source_type, $acquisition_method, $supplier_type, $supplier_name, $supplier_phone, $supplier_address, $cost, $quantity, $acquired_date, $notes, $id]);
            
            $success = $lang == 'ar' ? 'تم تحديث الاستحواذ بنجاح!' : 'Acquisition mise à jour avec succès!';
            
            // Recharger les données
            $stmt = $pdo->prepare("SELECT * FROM acquisitions WHERE id = ?");
            $stmt->execute([$id]);
            $acquisition = $stmt->fetch();
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'تعديل الاستحواذ' : 'Modifier l\'acquisition' ?> - Library System</title>
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
    background: rgb(59 130 246 / 0.1);
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
    max-width: 800px;
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

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
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
    box-shadow: 0 0 0 3px rgb(59 130 246 / 0.1);
}

.form-input::placeholder {
    color: var(--text-light);
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
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

.required-field {
    color: var(--error-color);
    margin-left: 0.25rem;
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
    
    .form-grid {
        grid-template-columns: 1fr;
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
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'تعديل التزويد' : 'Modifier l\'approvisionnement' ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'تعديل معلومات التزويد' : 'Modifier les informations de l\'approvisionnement' ?>
                </p>
            </div>
        </div>
        
        <div class="page-actions">
            <a href="../../public/router.php?module=acquisitions&action=list&lang=<?=$lang?>" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
            </a>
        </div>
    </div>

    <div class="form-card fade-in">
        <div class="form-header">
            <h2 class="form-title">
                <i class="fas fa-edit"></i>
                <?= $lang == 'ar' ? 'تعديل معلومات الاستحواذ' : 'Modifier les informations de l\'acquisition' ?>
            </h2>
            <p class="form-subtitle">
                <?= $lang == 'ar' ? 'قم بتعديل المعلومات أدناه' : 'Modifiez les informations ci-dessous' ?>
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <ul style="margin: 0; padding-right: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="post" id="editForm">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <div class="form-grid">
                <!-- Type de source -->
                <div class="form-group">
                    <label for="source_type" class="form-label">
                        <i class="fas fa-book"></i>
                        <?= $lang == 'ar' ? 'نوع المصدر' : 'Type de source' ?>
                        <span class="required-field">*</span>
                    </label>
                    <select id="source_type" name="source_type" class="form-input form-select" required>
                        <?php foreach ($source_types as $type_key => $type_names): ?>
                            <option value="<?= $type_key ?>" <?= $acquisition['source_type'] == $type_key ? 'selected' : '' ?>>
                                <?= $type_names[$lang] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Méthode d'approvisionnement -->
                <div class="form-group">
                    <label for="acquisition_method" class="form-label">
                        <i class="fas fa-shopping-cart"></i>
                        <?= $lang == 'ar' ? 'طريقة التزويد' : 'Méthode d\'approvisionnement' ?>
                        <span class="required-field">*</span>
                    </label>
                    <select id="acquisition_method" name="acquisition_method" class="form-input form-select" required>
                        <?php foreach ($acquisition_methods as $method_key => $method_names): ?>
                            <option value="<?= $method_key ?>" <?= $acquisition['acquisition_method'] == $method_key ? 'selected' : '' ?>>
                                <?= $method_names[$lang] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Type de fournisseur -->
                <div class="form-group">
                    <label for="supplier_type" class="form-label">
                        <i class="fas fa-building"></i>
                        <?= $lang == 'ar' ? 'جهة التزويد' : 'Entité fournisseur' ?>
                        <span class="required-field">*</span>
                    </label>
                    <select id="supplier_type" name="supplier_type" class="form-input form-select" required>
                        <?php foreach ($supplier_types as $supplier_key => $supplier_names): ?>
                            <option value="<?= $supplier_key ?>" <?= $acquisition['supplier_type'] == $supplier_key ? 'selected' : '' ?>>
                                <?= $supplier_names[$lang] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Nom du fournisseur -->
                <div class="form-group">
                    <label for="supplier_name" class="form-label">
                        <i class="fas fa-user"></i>
                        <?= $lang == 'ar' ? 'الاسم' : 'Nom' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="supplier_name" name="supplier_name" class="form-input" 
                           value="<?= htmlspecialchars($acquisition['supplier_name']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل اسم المزود' : 'Entrez le nom du fournisseur' ?>" 
                           required>
                </div>

                <!-- Numéro de téléphone -->
                <div class="form-group">
                    <label for="supplier_phone" class="form-label">
                        <i class="fas fa-phone"></i>
                        <?= $lang == 'ar' ? 'رقم الهاتف' : 'Numéro de téléphone' ?>
                    </label>
                    <input type="tel" id="supplier_phone" name="supplier_phone" class="form-input" 
                           value="<?= htmlspecialchars($acquisition['supplier_phone']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم الهاتف' : 'Entrez le numéro de téléphone' ?>">
                </div>

                <!-- Adresse du fournisseur -->
                <div class="form-group full-width">
                    <label for="supplier_address" class="form-label">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= $lang == 'ar' ? 'عنوان المزود' : 'Adresse du fournisseur' ?>
                    </label>
                    <textarea id="supplier_address" name="supplier_address" class="form-input form-textarea" 
                              placeholder="<?= $lang == 'ar' ? 'أدخل عنوان المزود' : 'Entrez l\'adresse du fournisseur' ?>"><?= htmlspecialchars($acquisition['supplier_address']) ?></textarea>
                </div>

                <!-- Coût -->
                <div class="form-group">
                    <label for="cost" class="form-label">
                        <i class="fas fa-money-bill"></i>
                        <?= $lang == 'ar' ? 'التكلفة' : 'Coût' ?>
                    </label>
                    <input type="number" id="cost" name="cost" class="form-input" 
                           value="<?= htmlspecialchars($acquisition['cost']) ?>" 
                           min="0" step="0.01" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل التكلفة' : 'Entrez le coût' ?>">
                </div>

                <!-- Quantité -->
                <div class="form-group">
                    <label for="quantity" class="form-label">
                        <i class="fas fa-copy"></i>
                        <?= $lang == 'ar' ? 'الكمية' : 'Quantité' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="number" id="quantity" name="quantity" class="form-input" 
                           value="<?= htmlspecialchars($acquisition['quantity']) ?>" 
                           min="1" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل الكمية' : 'Entrez la quantité' ?>" 
                           required>
                </div>

                <!-- Date d'acquisition -->
                <div class="form-group">
                    <label for="acquired_date" class="form-label">
                        <i class="fas fa-calendar"></i>
                        <?= $lang == 'ar' ? 'تاريخ الاستحواذ' : 'Date d\'acquisition' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="date" id="acquired_date" name="acquired_date" class="form-input" 
                           value="<?= htmlspecialchars($acquisition['acquired_date']) ?>" 
                           required>
                </div>

                <!-- Notes -->
                <div class="form-group full-width">
                    <label for="notes" class="form-label">
                        <i class="fas fa-sticky-note"></i>
                        <?= $lang == 'ar' ? 'ملاحظات' : 'Notes' ?>
                    </label>
                    <textarea id="notes" name="notes" class="form-input form-textarea" 
                              placeholder="<?= $lang == 'ar' ? 'أدخل ملاحظات إضافية' : 'Entrez des notes supplémentaires' ?>"><?= htmlspecialchars($acquisition['notes']) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'حفظ التعديلات' : 'Enregistrer les modifications' ?>
                </button>
                <a href="../../public/router.php?module=acquisitions&action=list&lang=<?=$lang?>" class="action-btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Form submission enhancement
document.getElementById('editForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري الحفظ...' : 'Enregistrement...' ?>';
    submitBtn.disabled = true;
    
    // Show success notification after a short delay
    setTimeout(() => {
        if (window.LibrarySystem) {
            window.LibrarySystem.showNotification(
                '<?= $lang == 'ar' ? 'تم حفظ التعديلات بنجاح!' : 'Modifications enregistrées avec succès!' ?>', 
                'success'
            );
        }
    }, 1000);
});
</script>
</body>
</html> 