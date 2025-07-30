<?php
/* modules/items/add_newspaper.php */
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_GET['lang'] ?? 'ar';
$type = 'newspaper'; // Fixed type for newspapers

// Newspaper titles - Limited to specified titles
$newspaper_titles = [
    'Ech-Chaab' => ['ar' => 'الشعب', 'fr' => 'Ech-Chaab'],
    'Journal Officiel' => ['ar' => 'الجريدة الرسمية', 'fr' => 'Journal Officiel']
];

$item = [
    'title' => 'Ech-Chaab',
    'newspaper_number' => '',
    'classification' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $newspaper_number = trim($_POST['newspaper_number'] ?? '');
    $classification = trim($_POST['classification'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = $lang == 'ar' ? 'اسم الجريدة مطلوب' : 'Le nom du journal est requis';
    }
    
    if (!in_array($title, array_keys($newspaper_titles))) {
        $errors[] = $lang == 'ar' ? 'يجب اختيار جريدة من القائمة المحددة' : 'Vous devez sélectionner un journal de la liste spécifiée';
    }
    
    if (empty($newspaper_number)) {
        $errors[] = $lang == 'ar' ? 'رقم الجريدة مطلوب' : 'Le numéro du journal est requis';
    }
    
    if (empty($classification)) {
        $errors[] = $lang == 'ar' ? 'العدد مطلوب' : 'Le numéro est requis';
    }
    
    // If no errors, insert the new newspaper
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, classification, copies_total, copies_in, newspaper_number) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$lang, $type, $title, $classification, 1, 1, $newspaper_number]);
            
            $success = $lang == 'ar' ? 'تم إضافة الجريدة بنجاح!' : 'Journal ajouté avec succès!';
            
            // Reset form after successful submission
            $item = [
                'title' => 'Ech-Chaab',
                'newspaper_number' => '',
                'classification' => ''
            ];
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
        }
    }
}

$type_info = [
    'newspaper' => [
        'ar' => ['title' => 'الجرائد', 'add' => 'إضافة جريدة جديدة', 'icon' => 'fas fa-newspaper'],
        'fr' => ['title' => 'Journaux', 'add' => 'Ajouter un nouveau journal', 'icon' => 'fas fa-newspaper']
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
    color: var(--warning-color);
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(245 158 11 / 0.1);
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
    border-color: var(--warning-color);
    box-shadow: 0 0 0 3px rgb(245 158 11 / 0.1);
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
    background: var(--warning-color);
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
    background: #d97706;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.required-field {
    color: var(--error-color);
    margin-left: 0.25rem;
}

.newspaper-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.newspaper-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--bg-primary);
}

.newspaper-option:hover {
    border-color: var(--warning-color);
    background: rgb(245 158 11 / 0.05);
}

.newspaper-option.selected {
    border-color: var(--warning-color);
    background: rgb(245 158 11 / 0.1);
    color: var(--warning-color);
}

.newspaper-option input[type="radio"] {
    display: none;
}

.newspaper-icon {
    font-size: 1.5rem;
    width: 2rem;
    text-align: center;
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
    
    .newspaper-options {
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
                <i class="<?= $type_info[$type][$lang]['icon'] ?>"></i>
            </div>
            <div>
                <h1><?= $type_info[$type][$lang]['add'] ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'إضافة جريدة جديدة إلى المكتبة' : 'Ajouter un nouveau journal à la bibliothèque' ?>
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
                <?= $lang == 'ar' ? 'املأ النموذج أدناه لإضافة جريدة جديدة' : 'Remplissez le formulaire ci-dessous pour ajouter un nouveau journal' ?>
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

        <form method="post" id="newspaperForm">
            <div class="form-grid">
                <!-- Sélection de la gériade -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-newspaper"></i>
                        <?= $lang == 'ar' ? 'الجرائد' : 'Journaux' ?>
                        <span class="required-field">*</span>
                    </label>
                    <div class="newspaper-options">
                        <?php foreach ($newspaper_titles as $title_key => $title_names): ?>
                            <label class="newspaper-option <?= $item['title'] == $title_key ? 'selected' : '' ?>">
                                <input type="radio" name="title" value="<?= $title_key ?>" 
                                       <?= $item['title'] == $title_key ? 'checked' : '' ?> required>
                                <div class="newspaper-icon">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <span><?= $title_names[$lang] ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Numéro de la gériade -->
                <div class="form-group">
                    <label for="newspaper_number" class="form-label">
                        <i class="fas fa-hashtag"></i>
                        <?= $lang == 'ar' ? 'رقم الجريدة' : 'Numéro du journal' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="newspaper_number" name="newspaper_number" class="form-input" 
                           value="<?= htmlspecialchars($item['newspaper_number']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم الجريدة' : 'Entrez le numéro du journal' ?>" 
                           required>
                </div>

                <!-- العدد -->
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
                    <?= $lang == 'ar' ? 'حفظ الجريدة' : 'Enregistrer le journal' ?>
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
// Newspaper selection enhancement
document.querySelectorAll('.newspaper-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove selected class from all options
        document.querySelectorAll('.newspaper-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        // Add selected class to the chosen option
        if (this.checked) {
            this.closest('.newspaper-option').classList.add('selected');
        }
    });
});

// Form submission enhancement
document.getElementById('newspaperForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري الحفظ...' : 'Enregistrement...' ?>';
    submitBtn.disabled = true;
    
    // Show success notification after a short delay
    setTimeout(() => {
        if (window.LibrarySystem) {
            window.LibrarySystem.showNotification(
                '<?= $lang == 'ar' ? 'تم حفظ الجريدة بنجاح!' : 'Journal enregistré avec succès!' ?>', 
                'success'
            );
        }
    }, 1000);
});
</script>
</body>
</html> 