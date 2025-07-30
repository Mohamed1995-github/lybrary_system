<?php
/* modules/items/add_magazine.php */
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_GET['lang'] ?? 'ar';
$type = 'magazine'; // Fixed type for magazines

$item = [
    'magazine_name' => '',
    'volume_number' => '',
    'classification' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $magazine_name = trim($_POST['magazine_name'] ?? '');
    $volume_number = trim($_POST['volume_number'] ?? '');
    $classification = trim($_POST['classification'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($magazine_name)) {
        $errors[] = $lang == 'ar' ? 'اسم المجلة مطلوب' : 'Le nom de la revue est requis';
    }
    
    if (empty($volume_number)) {
        $errors[] = $lang == 'ar' ? 'رقم المجلد مطلوب' : 'Le numéro du volume est requis';
    }
    
    if (empty($classification)) {
        $errors[] = $lang == 'ar' ? 'العدد مطلوب' : 'Le numéro est requis';
    }
    
    // If no errors, insert the new magazine
    if (empty($errors)) {
        try {
            // Utiliser le nom de la revue comme titre principal
            $title = $magazine_name;
            
            $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, classification, copies_total, copies_in, magazine_name, volume_number) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$lang, $type, $title, $classification, 1, 1, $magazine_name, $volume_number]);
            
            $success = $lang == 'ar' ? 'تم إضافة المجلة بنجاح!' : 'Revue ajoutée avec succès!';
            
            // Reset form after successful submission
            $item = [
                'magazine_name' => '',
                'volume_number' => '',
                'classification' => ''
            ];
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
        }
    }
}

$type_info = [
    'magazine' => [
        'ar' => ['title' => 'المجلات', 'add' => 'إضافة مجلة جديدة', 'icon' => 'fas fa-newspaper'],
        'fr' => ['title' => 'Revues', 'add' => 'Ajouter une nouvelle revue', 'icon' => 'fas fa-newspaper']
    ]
];
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $type_info[$type][$lang]['add'] ?> - Library System</title>
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
    color: var(--secondary-color);
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(5 150 105 / 0.1);
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

.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
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
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgb(5 150 105 / 0.1);
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
    background: var(--secondary-color);
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
    background: #059669;
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
                <i class="<?= $type_info[$type][$lang]['icon'] ?>"></i>
            </div>
            <div>
                <h1><?= $type_info[$type][$lang]['add'] ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'إضافة مجلة جديدة إلى المكتبة' : 'Ajouter une nouvelle revue à la bibliothèque' ?>
                </p>
            </div>
        </div>
        
        <div class="page-actions">
            <a href="../../public/router.php?module=items&action=list&lang=<?=$lang?>&type=<?=$type?>" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
            </a>
        </div>
    </div>

    <div class="form-card fade-in">
        <div class="form-header">
            <h2 class="form-title">
                <i class="<?= $type_info[$type][$lang]['icon'] ?>"></i>
                <?= $type_info[$type][$lang]['add'] ?>
            </h2>
            <p class="form-subtitle">
                <?= $lang == 'ar' ? 'املأ النموذج أدناه لإضافة مجلة جديدة' : 'Remplissez le formulaire ci-dessous pour ajouter une nouvelle revue' ?>
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

        <form method="post" id="magazineForm">
            <div class="form-grid">
                <!-- Nom de la revue -->
                <div class="form-group">
                    <label for="magazine_name" class="form-label">
                        <i class="fas fa-heading"></i>
                        <?= $lang == 'ar' ? 'اسم المجلة' : 'Nom de la revue' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="magazine_name" name="magazine_name" class="form-input" 
                           value="<?= htmlspecialchars($item['magazine_name']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل اسم المجلة' : 'Entrez le nom de la revue' ?>" 
                           required>
                </div>

                <!-- Numéro du volume -->
                <div class="form-group">
                    <label for="volume_number" class="form-label">
                        <i class="fas fa-layer-group"></i>
                        <?= $lang == 'ar' ? 'رقم المجلد' : 'Numéro du volume' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="volume_number" name="volume_number" class="form-input" 
                           value="<?= htmlspecialchars($item['volume_number']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم المجلد' : 'Entrez le numéro du volume' ?>" 
                           required>
                </div>

                <!-- Numéro/Édition -->
                <div class="form-group">
                    <label for="classification" class="form-label">
                        <i class="fas fa-hashtag"></i>
                        <?= $lang == 'ar' ? 'العدد' : 'Numéro' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="classification" name="classification" class="form-input" 
                           value="<?= htmlspecialchars($item['classification']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل العدد' : 'Entrez le numéro' ?>" 
                           required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'حفظ المجلة' : 'Enregistrer la revue' ?>
                </button>
                <a href="../../public/router.php?module=items&action=list&lang=<?=$lang?>&type=<?=$type?>" class="action-btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Form submission enhancement
document.getElementById('magazineForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري الحفظ...' : 'Enregistrement...' ?>';
    submitBtn.disabled = true;
    
    // Show success notification after a short delay
    setTimeout(() => {
        if (window.LibrarySystem) {
            window.LibrarySystem.showNotification(
                '<?= $lang == 'ar' ? 'تم حفظ المجلة بنجاح!' : 'Revue enregistrée avec succès!' ?>', 
                'success'
            );
        }
    }, 1000);
});
</script>
</body>
</html> 