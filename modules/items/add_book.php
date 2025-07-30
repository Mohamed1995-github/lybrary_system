<?php
/* modules/items/add_book.php */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';

if (!isset($_SESSION['uid'])) { 
    header('Location: ../../public/login.php'); 
    exit; 
}

// Vérification des permissions pour l'ajout de livres
$requiredPermissions = ['إدارة الكتب', 'Gestion des livres'];
$accessLevel = getEmployeeAccessLevel();

if (!hasAnyPermission($requiredPermissions) && $accessLevel !== 'admin') {
    $log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - UNAUTHORIZED_ACCESS to add_book.php - IP: {$_SERVER['REMOTE_ADDR']}\n";
    file_put_contents(__DIR__ . '/../../logs/security.log', $log_entry, FILE_APPEND | LOCK_EX);
    header('Location: ../../public/error.php?code=403&lang=' . $lang);
    exit;
}

// Log de l'accès autorisé
$log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - AUTHORIZED_ACCESS to add_book.php - IP: {$_SERVER['REMOTE_ADDR']}\n";
file_put_contents(__DIR__ . '/../../logs/access.log', $log_entry, FILE_APPEND | LOCK_EX);

$lang = $_GET['lang'] ?? 'ar';
$type = 'book'; // Fixed type for books

// Book-specific categories - Limited to specified categories
$book_categories = [
    'Administration' => ['ar' => 'إدارة', 'fr' => 'Administration'],
    'Economy' => ['ar' => 'اقتصاد', 'fr' => 'Économie'],
    'Law' => ['ar' => 'قانون', 'fr' => 'Droit'],
    'Diplomacy' => ['ar' => 'دبلوماسية', 'fr' => 'Diplomatie']
];

// Book format options
$format_options = [
    'hardcover' => ['ar' => 'غلاف صلب', 'fr' => 'Relié'],
    'paperback' => ['ar' => 'غلاف ورقي', 'fr' => 'Broché'],
    'ebook' => ['ar' => 'كتاب إلكتروني', 'fr' => 'Livre électronique'],
    'audiobook' => ['ar' => 'كتاب صوتي', 'fr' => 'Livre audio'],
    'pdf' => ['ar' => 'PDF', 'fr' => 'PDF'],
    'other' => ['ar' => 'أخرى', 'fr' => 'Autre']
];

// Book type options for general books
$book_type_options = [
    'reference' => ['ar' => 'مرجع', 'fr' => 'Référence'],
    'textbook' => ['ar' => 'كتاب مدرسي', 'fr' => 'Manuel scolaire'],
    'encyclopedia' => ['ar' => 'موسوعة', 'fr' => 'Encyclopédie'],
    'dictionary' => ['ar' => 'قاموس', 'fr' => 'Dictionnaire'],
    'atlas' => ['ar' => 'أطلس', 'fr' => 'Atlas'],
    'manual' => ['ar' => 'دليل', 'fr' => 'Manuel'],
    'guide' => ['ar' => 'دليل إرشادي', 'fr' => 'Guide'],
    'other' => ['ar' => 'أخرى', 'fr' => 'Autre']
];

$item = [
    'title' => '',
    'author' => '',
    'publisher' => '',
    'year_pub' => '',
    'classification' => '',
    'field' => 'Administration',
    'copies_total' => 1,
    'format' => 'hardcover',
    'isbn' => '',
    'pages' => '',
    'language' => $lang,
    'description' => '',
    'edition' => '',
    'series' => '',
    'book_type' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $year_pub = $_POST['year_pub'] ?? null;
    $classification = trim($_POST['classification'] ?? '');
    $field = $_POST['field'] ?? 'Administration';
    $copies_total = (int)$_POST['copies_total'];
    $format = $_POST['format'] ?? 'hardcover';
    $isbn = trim($_POST['isbn'] ?? '');
    $pages = trim($_POST['pages'] ?? '');
    $language = $_POST['language'] ?? $lang;
    $description = trim($_POST['description'] ?? '');
    $edition = trim($_POST['edition'] ?? '');
    $series = trim($_POST['series'] ?? '');
    $book_type = trim($_POST['book_type'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = $lang == 'ar' ? 'عنوان الكتاب مطلوب' : 'Le titre du livre est requis';
    }
    
    if (empty($author)) {
        $errors[] = $lang == 'ar' ? 'اسم المؤلف مطلوب' : 'Le nom de l\'auteur est requis';
    }
    
    if (empty($field)) {
        $errors[] = $lang == 'ar' ? 'يجب اختيار التصنيف' : 'La catégorie doit être sélectionnée';
    }
    
    if ($copies_total < 1) {
        $errors[] = $lang == 'ar' ? 'عدد النسخ يجب أن يكون 1 على الأقل' : 'Le nombre de copies doit être au moins 1';
    }
    
    // If no errors, insert the new book
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, author, publisher, year_pub, classification, field, copies_total, copies_in, format, isbn, pages, language, description, edition, series, book_type) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$lang, $type, $title, $author, $publisher, $year_pub, $classification, $field, $copies_total, $copies_total, $format, $isbn, $pages, $language, $description, $edition, $series, $book_type]);
            
            $success = $lang == 'ar' ? 'تم إضافة الكتاب بنجاح!' : 'Livre ajouté avec succès!';
            
            // Reset form after successful submission
            $item = [
                'title' => '',
                'author' => '',
                'publisher' => '',
                'year_pub' => '',
                'classification' => '',
                'field' => 'Administration',
                'copies_total' => 1,
                'format' => 'hardcover',
                'isbn' => '',
                'pages' => '',
                'language' => $lang,
                'description' => '',
                'edition' => '',
                'series' => '',
                'book_type' => ''
            ];
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
<title><?= $lang == 'ar' ? 'إضافة كتاب جديد' : 'Ajouter un nouveau livre' ?> - Library System</title>
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
    max-width: 900px;
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
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
}

.form-input::placeholder {
    color: var(--text-light);
}

.form-textarea {
    min-height: 120px;
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

.form-info {
    background: rgb(37 99 235 / 0.1);
    border: 1px solid rgb(37 99 235 / 0.2);
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

.required-field {
    color: var(--error-color);
    margin-left: 0.25rem;
}

.category-filter {
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
    margin-top: 1rem;
}

.category-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--bg-primary);
}

.category-option:hover {
    border-color: var(--primary-color);
    background: rgb(37 99 235 / 0.05);
}

.category-option.selected {
    border-color: var(--primary-color);
    background: rgb(37 99 235 / 0.1);
    color: var(--primary-color);
}

.category-option input[type="radio"] {
    display: none;
}

.category-icon {
    font-size: 1.25rem;
    width: 1.5rem;
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
    
    .category-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}
</style>
</head>
<body>
<div class="page-container">
    <div class="page-header fade-in">
        <div class="page-title">
            <div class="page-icon">
                <i class="fas fa-book"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'إضافة كتاب جديد' : 'Ajouter un nouveau livre' ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'إضافة كتاب جديد إلى المكتبة' : 'Ajouter un nouveau livre à la bibliothèque' ?>
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
                <i class="fas fa-book"></i>
                <?= $lang == 'ar' ? 'معلومات الكتاب الجديد' : 'Informations du nouveau livre' ?>
            </h2>
            <p class="form-subtitle">
                <?= $lang == 'ar' ? 'املأ النموذج أدناه لإضافة كتاب جديد إلى المكتبة' : 'Remplissez le formulaire ci-dessous pour ajouter un nouveau livre à la bibliothèque' ?>
            </p>
        </div>

        <div class="form-info">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong><?= $lang == 'ar' ? 'نوع المادة:' : 'Type de matériau:' ?></strong> 
                <?= $lang == 'ar' ? 'كتاب' : 'Livre' ?> | 
                <strong><?= $lang == 'ar' ? 'اللغة:' : 'Langue:' ?></strong> 
                <?= strtoupper($lang) ?>
            </div>
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

        <form method="post" id="bookForm">
            <!-- Category Filter Section -->
            <div class="category-filter">
                <h3 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1.1rem;">
                    <i class="fas fa-filter"></i>
                    <?= $lang == 'ar' ? 'اختر التصنيف' : 'Sélectionner la catégorie' ?>
                </h3>
                <div class="category-grid">
                    <?php foreach ($book_categories as $category_key => $category_names): ?>
                        <label class="category-option <?= $item['field'] == $category_key ? 'selected' : '' ?>">
                            <input type="radio" name="field" value="<?= $category_key ?>" 
                                   <?= $item['field'] == $category_key ? 'checked' : '' ?> required>
                            <div class="category-icon">
                                <?php
                                $category_icons = [
                                    'Administration' => 'fas fa-building',
                                    'Economy' => 'fas fa-chart-line',
                                    'Law' => 'fas fa-gavel',
                                    'Diplomacy' => 'fas fa-handshake'
                                ];
                                ?>
                                <i class="<?= $category_icons[$category_key] ?? 'fas fa-tag' ?>"></i>
                            </div>
                            <span><?= $category_names[$lang] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="title" class="form-label">
                        <i class="fas fa-heading"></i>
                        <?= $lang == 'ar' ? 'عنوان الكتاب' : 'Titre du livre' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="title" name="title" class="form-input" 
                           value="<?= htmlspecialchars($item['title']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل عنوان الكتاب' : 'Entrez le titre du livre' ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="author" class="form-label">
                        <i class="fas fa-user"></i>
                        <?= $lang == 'ar' ? 'المؤلف' : 'Auteur' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="author" name="author" class="form-input" 
                           value="<?= htmlspecialchars($item['author']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل اسم المؤلف' : 'Entrez le nom de l\'auteur' ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="publisher" class="form-label">
                        <i class="fas fa-building"></i>
                        <?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?>
                    </label>
                    <input type="text" id="publisher" name="publisher" class="form-input" 
                           value="<?= htmlspecialchars($item['publisher']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل اسم الناشر' : 'Entrez le nom de l\'éditeur' ?>">
                </div>

                <div class="form-group">
                    <label for="year_pub" class="form-label">
                        <i class="fas fa-calendar"></i>
                        <?= $lang == 'ar' ? 'سنة النشر' : 'Année de publication' ?>
                    </label>
                    <input type="number" id="year_pub" name="year_pub" class="form-input" 
                           value="<?= htmlspecialchars($item['year_pub']) ?>" 
                           min="1800" max="<?= date('Y') + 1 ?>" 
                           placeholder="<?= $lang == 'ar' ? 'مثال: 2023' : 'Ex: 2023' ?>">
                </div>

                <div class="form-group">
                    <label for="edition" class="form-label">
                        <i class="fas fa-layer-group"></i>
                        <?= $lang == 'ar' ? 'الطبعة' : 'Édition' ?>
                    </label>
                    <input type="text" id="edition" name="edition" class="form-input" 
                           value="<?= htmlspecialchars($item['edition']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'مثال: الطبعة الأولى' : 'Ex: Première édition' ?>">
                </div>

                <div class="form-group">
                    <label for="classification" class="form-label">
                        <i class="fas fa-tags"></i>
                        <?= $lang == 'ar' ? 'رقم التصنيف' : 'Numéro de classification' ?>
                    </label>
                    <input type="text" id="classification" name="classification" class="form-input" 
                           value="<?= htmlspecialchars($item['classification']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم التصنيف' : 'Entrez le numéro de classification' ?>">
                </div>

                <div class="form-group">
                    <label for="book_type" class="form-label">
                        <i class="fas fa-bookmark"></i>
                        <?= $lang == 'ar' ? 'نوع الكتاب' : 'Type du livre' ?>
                    </label>
                    <select id="book_type" name="book_type" class="form-input form-select">
                        <option value=""><?= $lang == 'ar' ? 'اختر نوع الكتاب' : 'Sélectionner le type de livre' ?></option>
                        <?php foreach ($book_type_options as $type_key => $type_names): ?>
                            <option value="<?= $type_key ?>" <?= $item['book_type'] == $type_key ? 'selected' : '' ?>>
                                <?= $type_names[$lang] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="isbn" class="form-label">
                        <i class="fas fa-barcode"></i>
                        <?= $lang == 'ar' ? 'رقم ISBN' : 'Numéro ISBN' ?>
                    </label>
                    <input type="text" id="isbn" name="isbn" class="form-input" 
                           value="<?= htmlspecialchars($item['isbn']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'مثال: 978-0-7475-3269-9' : 'Ex: 978-0-7475-3269-9' ?>">
                </div>

                <div class="form-group">
                    <label for="format" class="form-label">
                        <i class="fas fa-book-open"></i>
                        <?= $lang == 'ar' ? 'التنسيق' : 'Format' ?>
                    </label>
                    <select id="format" name="format" class="form-input form-select">
                        <?php foreach ($format_options as $format_key => $format_names): ?>
                            <option value="<?= $format_key ?>" <?= $item['format'] == $format_key ? 'selected' : '' ?>>
                                <?= $format_names[$lang] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="series" class="form-label">
                        <i class="fas fa-list-ol"></i>
                        <?= $lang == 'ar' ? 'السلسلة' : 'Série' ?>
                    </label>
                    <input type="text" id="series" name="series" class="form-input" 
                           value="<?= htmlspecialchars($item['series']) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل اسم السلسلة' : 'Entrez le nom de la série' ?>">
                </div>

                <div class="form-group">
                    <label for="pages" class="form-label">
                        <i class="fas fa-file"></i>
                        <?= $lang == 'ar' ? 'عدد الصفحات' : 'Nombre de pages' ?>
                    </label>
                    <input type="number" id="pages" name="pages" class="form-input" 
                           value="<?= htmlspecialchars($item['pages']) ?>" 
                           min="1" max="10000" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل عدد الصفحات' : 'Entrez le nombre de pages' ?>">
                </div>

                <div class="form-group">
                    <label for="copies_total" class="form-label">
                        <i class="fas fa-copy"></i>
                        <?= $lang == 'ar' ? 'عدد النسخ' : 'Nombre de copies' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="number" id="copies_total" name="copies_total" class="form-input" 
                           value="<?= htmlspecialchars($item['copies_total']) ?>" 
                           min="1" max="1000" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل عدد النسخ' : 'Entrez le nombre de copies' ?>" 
                           required>
                </div>

                <div class="form-group full-width">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left"></i>
                        <?= $lang == 'ar' ? 'الوصف' : 'Description' ?>
                    </label>
                    <textarea id="description" name="description" class="form-input form-textarea"
                              placeholder="<?= $lang == 'ar' ? 'أدخل وصف الكتاب' : 'Entrez la description du livre' ?>"><?= htmlspecialchars($item['description']) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'إضافة الكتاب' : 'Ajouter le livre' ?>
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
// Category selection enhancement
document.querySelectorAll('.category-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove selected class from all options
        document.querySelectorAll('.category-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        // Add selected class to the chosen option
        if (this.checked) {
            this.closest('.category-option').classList.add('selected');
        }
    });
});

// Form submission enhancement
document.getElementById('bookForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري الإضافة...' : 'Ajout en cours...' ?>';
    submitBtn.disabled = true;
    
    // Show success notification after a short delay
    setTimeout(() => {
        if (window.LibrarySystem) {
            window.LibrarySystem.showNotification(
                '<?= $lang == 'ar' ? 'تم إضافة الكتاب بنجاح!' : 'Livre ajouté avec succès!' ?>', 
                'success'
            );
        }
    }, 1000);
});
</script>
</body>
</html> 