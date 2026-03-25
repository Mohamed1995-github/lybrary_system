<?php
/* modules/items/add_magazine.php */
require_once __DIR__ . '/../../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_GET['lang'] ?? 'ar';
$type = 'magazine'; // Fixed type for magazines

$item = [
    'title' => '',
    'magazine_type' => '',
    'issue_number' => '',
    'publication_date' => '',
    'publisher' => '',
    'publication_place' => '',
    'issn' => '',
    'classification_number' => '',
    'magazine_number' => '',
    'cabinet_number' => '',
    'shelf_number' => '',
    'drawer_number' => '',
    'registration_date' => '',
    'modification_date' => '',
    'periodical_file' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $magazine_type = trim($_POST['magazine_type'] ?? '');
    $issue_number = trim($_POST['issue_number'] ?? '');
    $publication_date = trim($_POST['publication_date'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $publication_place = trim($_POST['publication_place'] ?? '');
    $issn = trim($_POST['issn'] ?? '');
    $classification_number = trim($_POST['classification_number'] ?? '');
    $magazine_number = trim($_POST['magazine_number'] ?? '');
    $cabinet_number = trim($_POST['cabinet_number'] ?? '');
    $shelf_number = trim($_POST['shelf_number'] ?? '');
    $drawer_number = trim($_POST['drawer_number'] ?? '');
    $registration_date = trim($_POST['registration_date'] ?? '');
    $modification_date = trim($_POST['modification_date'] ?? '');
    $periodical_file = null;

    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = $lang == 'ar' ? 'العنوان مطلوب' : 'Le titre est requis';
    }
    
    if (empty($issue_number)) {
        $errors[] = $lang == 'ar' ? 'العدد مطلوب' : 'Le numéro est requis';
    }
    
    // Handle file upload
    $uploadDir = dirname(__DIR__, 2) . '/uploads/magazines';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (!empty($_FILES['periodical_file']['name'])) {
        $file = $_FILES['periodical_file'];
        $maxSize = 10 * 1024 * 1024; // 10 MB
        
        // Validate file type
        $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExt, $allowedTypes)) {
            $errors[] = $lang == 'ar' ? 'نوع الملف غير مدعوم (PDF, DOC, DOCX, JPG, PNG فقط)' : 'Type de fichier non supporté (PDF, DOC, DOCX, JPG, PNG seulement)';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = $lang == 'ar' ? 'حجم الملف كبير جداً (الحد الأقصى 10 MB)' : 'Le fichier est trop volumineux (max 10 MB)';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = $lang == 'ar' ? 'خطأ في رفع الملف' : 'Erreur lors du téléchargement du fichier';
        } else {
            // Generate unique filename
            $periodical_file = 'magazine_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
            $uploadPath = $uploadDir . '/' . $periodical_file;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $errors[] = $lang == 'ar' ? 'خطأ في رفع الملف' : 'Erreur lors du téléchargement du fichier';
                $periodical_file = null;
            }
        }
    }
    
    // If no errors, insert the new magazine
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, magazine_type, issue_number, publication_date, publisher, publication_place, issn, classification_number, magazine_number, cabinet_number, shelf_number, drawer_number, registration_date, modification_date, periodical_file, copies, available_copies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)");
            $stmt->execute([$lang, $type, $title, $magazine_type, $issue_number, $publication_date, $publisher, $publication_place, $issn, $classification_number, $magazine_number, $cabinet_number, $shelf_number, $drawer_number, $registration_date, $modification_date, $periodical_file]);
            
            $success = $lang == 'ar' ? 'تم إضافة الدورية بنجاح!' : 'Périodique ajouté avec succès!';
            
            // Reset form after successful submission
            $item = [
                'title' => '',
                'magazine_type' => '',
                'issue_number' => '',
                'publication_date' => '',
                'publisher' => '',
                'publication_place' => '',
                'issn' => '',
                'classification_number' => '',
                'magazine_number' => '',
                'cabinet_number' => '',
                'shelf_number' => '',
                'drawer_number' => '',
                'registration_date' => '',
                'modification_date' => '',
                'periodical_file' => ''
            ];
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
            // Delete uploaded file if database insertion fails
            if ($periodical_file && file_exists($uploadPath)) {
                unlink($uploadPath);
            }
        }
    }
}

$type_info = [
    'magazine' => [
        'ar' => ['title' => 'الدوريات', 'add' => 'إضافة الدوريات', 'icon' => 'fas fa-newspaper'],
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

.file-input-wrapper {
    position: relative;
    overflow: hidden;
}

.file-input-wrapper input[type="file"] {
    position: absolute;
    left: -9999px;
}

.file-input-label {
    display: block;
    padding: 1rem;
    border: 2px dashed var(--secondary-color);
    border-radius: var(--radius-lg);
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: rgb(5 150 105 / 0.05);
}

.file-input-label:hover {
    background: rgb(5 150 105 / 0.1);
    border-color: #059669;
}

.file-name {
    color: #059669;
    font-weight: 600;
    margin-top: 0.5rem;
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
<link rel="stylesheet" href="assets/css/professional-theme.css">
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

        <form method="post" enctype="multipart/form-data" id="magazineForm">
            <div class="form-grid">
                <!-- العنوان -->
                <div class="form-group">
                    <label for="title" class="form-label">
                        <i class="fas fa-heading"></i>
                        <?= $lang == 'ar' ? 'العنوان' : 'Titre' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="title" name="title" class="form-input" 
                           value="<?= htmlspecialchars($item['title']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل العنوان' : 'Entrez le titre' ?>" 
                           required>
                </div>

                <!-- النوع -->
                <div class="form-group">
                    <label for="magazine_type" class="form-label">
                        <i class="fas fa-tag"></i>
                        <?= $lang == 'ar' ? 'النوع' : 'Type' ?>
                    </label>
                    <input type="text" id="magazine_type" name="magazine_type" class="form-input" 
                           value="<?= htmlspecialchars($item['magazine_type']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل النوع' : 'Entrez le type' ?>">
                </div>

                <!-- العدد -->
                <div class="form-group">
                    <label for="issue_number" class="form-label">
                        <i class="fas fa-hashtag"></i>
                        <?= $lang == 'ar' ? 'العدد' : 'Numéro' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="issue_number" name="issue_number" class="form-input" 
                           value="<?= htmlspecialchars($item['issue_number']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل العدد' : 'Entrez le numéro' ?>" 
                           required>
                </div>

                <!-- تاريخ النشر -->
                <div class="form-group">
                    <label for="publication_date" class="form-label">
                        <i class="fas fa-calendar"></i>
                        <?= $lang == 'ar' ? 'تاريخ النشر' : 'Date de publication' ?>
                    </label>
                    <input type="date" id="publication_date" name="publication_date" class="form-input" 
                           value="<?= htmlspecialchars($item['publication_date']) ?>">
                </div>

                <!-- دار النشر -->
                <div class="form-group">
                    <label for="publisher" class="form-label">
                        <i class="fas fa-building"></i>
                        <?= $lang == 'ar' ? 'دار النشر' : 'Éditeur' ?>
                    </label>
                    <input type="text" id="publisher" name="publisher" class="form-input" 
                           value="<?= htmlspecialchars($item['publisher']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل دار النشر' : 'Entrez l\'éditeur' ?>">
                </div>

                <!-- مكان النشر -->
                <div class="form-group">
                    <label for="publication_place" class="form-label">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= $lang == 'ar' ? 'مكان النشر' : 'Lieu de publication' ?>
                    </label>
                    <input type="text" id="publication_place" name="publication_place" class="form-input" 
                           value="<?= htmlspecialchars($item['publication_place']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل مكان النشر' : 'Entrez le lieu de publication' ?>">
                </div>

                <!-- الرقم المعياري (ISSN) -->
                <div class="form-group">
                    <label for="issn" class="form-label">
                        <i class="fas fa-barcode"></i>
                        <?= $lang == 'ar' ? 'الرقم المعياري (ISSN)' : 'Numéro standard (ISSN)' ?>
                    </label>
                    <input type="text" id="issn" name="issn" class="form-input" 
                           value="<?= htmlspecialchars($item['issn']) ?>" 
                           placeholder="ISSN-XXXX-XXXX">
                </div>

                <!-- رقم التصنيف -->
                <div class="form-group">
                    <label for="classification_number" class="form-label">
                        <i class="fas fa-list-ol"></i>
                        <?= $lang == 'ar' ? 'رقم التصنيف' : 'Numéro de classification' ?>
                    </label>
                    <input type="text" id="classification_number" name="classification_number" class="form-input" 
                           value="<?= htmlspecialchars($item['classification_number']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم التصنيف' : 'Entrez le numéro de classification' ?>">
                </div>

                <!-- رقم المجلة -->
                <div class="form-group">
                    <label for="magazine_number" class="form-label">
                        <i class="fas fa-book"></i>
                        <?= $lang == 'ar' ? 'رقم المجلة' : 'Numéro de la revue' ?>
                    </label>
                    <input type="text" id="magazine_number" name="magazine_number" class="form-input" 
                           value="<?= htmlspecialchars($item['magazine_number']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم المجلة' : 'Entrez le numéro de la revue' ?>">
                </div>

                <!-- رقم الخزانة -->
                <div class="form-group">
                    <label for="cabinet_number" class="form-label">
                        <i class="fas fa-archive"></i>
                        <?= $lang == 'ar' ? 'رقم الخزانة' : 'Numéro d\'armoire' ?>
                    </label>
                    <input type="text" id="cabinet_number" name="cabinet_number" class="form-input" 
                           value="<?= htmlspecialchars($item['cabinet_number']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم الخزانة' : 'Entrez le numéro d\'armoire' ?>">
                </div>

                <!-- رقم الرف -->
                <div class="form-group">
                    <label for="shelf_number" class="form-label">
                        <i class="fas fa-layer-group"></i>
                        <?= $lang == 'ar' ? 'رقم الرف' : 'Numéro d\'étagère' ?>
                    </label>
                    <input type="text" id="shelf_number" name="shelf_number" class="form-input" 
                           value="<?= htmlspecialchars($item['shelf_number']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم الرف' : 'Entrez le numéro d\'étagère' ?>">
                </div>

                <!-- رقم الدرج -->
                <div class="form-group">
                    <label for="drawer_number" class="form-label">
                        <i class="fas fa-box"></i>
                        <?= $lang == 'ar' ? 'رقم الدرج' : 'Numéro de tiroir' ?>
                    </label>
                    <input type="text" id="drawer_number" name="drawer_number" class="form-input" 
                           value="<?= htmlspecialchars($item['drawer_number']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم الدرج' : 'Entrez le numéro de tiroir' ?>">
                </div>

                <!-- تاريخ التسجيل -->
                <div class="form-group">
                    <label for="registration_date" class="form-label">
                        <i class="fas fa-calendar-plus"></i>
                        <?= $lang == 'ar' ? 'تاريخ التسجيل' : 'Date d\'enregistrement' ?>
                    </label>
                    <input type="date" id="registration_date" name="registration_date" class="form-input" 
                           value="<?= htmlspecialchars($item['registration_date']) ?>">
                </div>

                <!-- تاريخ التعديل -->
                <div class="form-group">
                    <label for="modification_date" class="form-label">
                        <i class="fas fa-calendar-check"></i>
                        <?= $lang == 'ar' ? 'تاريخ التعديل' : 'Date de modification' ?>
                    </label>
                    <input type="date" id="modification_date" name="modification_date" class="form-input" 
                           value="<?= htmlspecialchars($item['modification_date']) ?>">
                </div>

                <!-- تحميل الدورية -->
                <div class="form-group">
                    <label for="periodical_file" class="form-label">
                        <i class="fas fa-upload"></i>
                        <?= $lang == 'ar' ? 'تحميل الدورية' : 'Télécharger la périodique' ?>
                    </label>
                    <div class="file-input-wrapper">
                        <input type="file" id="periodical_file" name="periodical_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" onchange="updateFileName(this)">
                        <label for="periodical_file" class="file-input-label">
                            <div>📄 <?= $lang == 'ar' ? 'اضغط لاختيار ملف أو اسحب الملف هنا' : 'Cliquez pour sélectionner un fichier' ?></div>
                            <div id="fileName"></div>
                        </label>
                    </div>
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
function updateFileName(input) {
  const fileNameDiv = document.getElementById('fileName');
  if (input.files && input.files[0]) {
    fileNameDiv.innerHTML = '<div class="file-name">✓ ' + input.files[0].name + '</div>';
  } else {
    fileNameDiv.innerHTML = '';
  }
}

// Drag and drop
const fileInput = document.getElementById('periodical_file');
const fileLabel = fileInput.previousElementSibling;

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
  fileLabel.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
  e.preventDefault();
  e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
  fileLabel.addEventListener(eventName, () => {
    fileLabel.style.background = 'rgb(5 150 105 / 0.1)';
    fileLabel.style.borderColor = '#059669';
  }, false);
});

['dragleave', 'drop'].forEach(eventName => {
  fileLabel.addEventListener(eventName, () => {
    fileLabel.style.background = 'rgb(5 150 105 / 0.05)';
    fileLabel.style.borderColor = 'var(--secondary-color)';
  }, false);
});

fileLabel.addEventListener('drop', (e) => {
  const dt = e.dataTransfer;
  const files = dt.files;
  fileInput.files = files;
  updateFileName(fileInput);
}, false);

// Form submission enhancement
document.getElementById('magazineForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري الحفظ...' : 'Enregistrement...' ?>';
    submitBtn.disabled = true;
    
    // Show success notification after a short delay
    setTimeout(() => {
        if (window.LibrarySystem) {
            window.LibrarySystem.showNotification(
                '<?= $lang == 'ar' ? 'تم حفظ الدورية بنجاح!' : 'Périodique enregistrée avec succès!' ?>', 
                'success'
            );
        }
    }, 1000);
});
</script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html> 