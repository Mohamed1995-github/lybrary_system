<?php
/* modules/items/add_edit.php */
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$id = $_GET['id'] ?? null;
$lang = $_GET['lang'] ?? 'ar';
$type = $_GET['type'] ?? 'book';

// Rediriger vers les formulaires spécifiques selon le type
if (!$id) {
    // Pour l'ajout, rediriger vers les formulaires spécifiques
    switch ($type) {
        case 'book':
            header("Location: add_book.php?lang=$lang");
            exit;
        case 'magazine':
            header("Location: add_magazine.php?lang=$lang");
            exit;
        case 'newspaper':
            header("Location: add_newspaper.php?lang=$lang");
            exit;
        default:
            header("Location: add_book.php?lang=$lang");
            exit;
    }
}

// Pour l'édition, récupérer les données et afficher un formulaire adapté
$item = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) { 
        die($lang == 'ar' ? "العنصر غير موجود" : "Élément non trouvé"); 
    }
    $type = $item['type']; // Utiliser le type de l'élément récupéré
}

// Traitement du formulaire d'édition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $year_pub = $_POST['year_pub'] ?? null;
    $classification = trim($_POST['classification'] ?? '');
    $copies = (int)$_POST['copies'];
    
    // Champs spécifiques selon le type
    $edition = trim($_POST['edition'] ?? '');
    $book_type = trim($_POST['book_type'] ?? '');
    $magazine_name = trim($_POST['magazine_name'] ?? '');
    $volume_number = trim($_POST['volume_number'] ?? '');
    $newspaper_number = trim($_POST['newspaper_number'] ?? '');
    
    $errors = [];
    
    // Validation de base
    if (empty($title)) {
        $errors[] = $lang == 'ar' ? 'العنوان مطلوب' : 'Le titre est requis';
    }
    
    if ($copies < 1) {
        $errors[] = $lang == 'ar' ? 'عدد النسخ يجب أن يكون 1 على الأقل' : 'Le nombre de copies doit être au moins 1';
    }
    
    // Si pas d'erreurs, mettre à jour
    if (empty($errors)) {
        try {
            // Construire la requête selon le type
            if ($type == 'book') {
                $stmt = $pdo->prepare("UPDATE items SET title=?, author=?, publisher=?, year_pub=?, classification=?, copies=?, available_copies=?, edition=?, book_type=? WHERE id=?");
                $stmt->execute([$title, $author, $publisher, $year_pub, $classification, $copies, $copies, $edition, $book_type, $id]);
            } elseif ($type == 'magazine') {
                $stmt = $pdo->prepare("UPDATE items SET title=?, author=?, publisher=?, year_pub=?, classification=?, copies=?, available_copies=?, magazine_name=?, volume_number=? WHERE id=?");
                $stmt->execute([$title, $author, $publisher, $year_pub, $classification, $copies, $copies, $magazine_name, $volume_number, $id]);
            } elseif ($type == 'newspaper') {
                $stmt = $pdo->prepare("UPDATE items SET title=?, publisher=?, year_pub=?, classification=?, copies=?, available_copies=?, newspaper_number=? WHERE id=?");
                $stmt->execute([$title, $publisher, $year_pub, $classification, $copies, $copies, $newspaper_number, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE items SET title=?, author=?, publisher=?, year_pub=?, classification=?, copies=?, available_copies=? WHERE id=?");
                $stmt->execute([$title, $author, $publisher, $year_pub, $classification, $copies, $copies, $id]);
            }
            
            $success = $lang == 'ar' ? 'تم التحديث بنجاح!' : 'Mise à jour réussie!';
            
            // Rediriger vers la liste
            header("Location: list.php?lang=$lang&type=$type");
            exit;
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
        }
    }
}

// Définir les options selon le type
$book_categories = [
    'Administration' => ['ar' => 'إدارة', 'fr' => 'Administration'],
    'Economy' => ['ar' => 'اقتصاد', 'fr' => 'Économie'],
    'Law' => ['ar' => 'قانون', 'fr' => 'Droit'],
    'Diplomacy' => ['ar' => 'دبلوماسية', 'fr' => 'Diplomatie']
];

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

$newspaper_titles = [
    'Ech-Chaab' => ['ar' => 'الشعب', 'fr' => 'Ech-Chaab'],
    'Journal Officiel' => ['ar' => 'الجريدة الرسمية', 'fr' => 'Journal Officiel']
];

$type_info = [
    'book' => [
        'ar' => ['title' => 'الكتب', 'edit' => 'تعديل الكتاب', 'icon' => 'fas fa-book'],
        'fr' => ['title' => 'Livres', 'edit' => 'Modifier le livre', 'icon' => 'fas fa-book']
    ],
    'magazine' => [
        'ar' => ['title' => 'المجلات', 'edit' => 'تعديل المجلة', 'icon' => 'fas fa-newspaper'],
        'fr' => ['title' => 'Revues', 'edit' => 'Modifier la revue', 'icon' => 'fas fa-newspaper']
    ],
    'newspaper' => [
        'ar' => ['title' => 'الجرائد', 'edit' => 'تعديل الجريدة', 'icon' => 'fas fa-newspaper'],
        'fr' => ['title' => 'Journaux', 'edit' => 'Modifier le journal', 'icon' => 'fas fa-newspaper']
    ],
    'general' => [
        'ar' => ['title' => 'المصادر العامة', 'edit' => 'تعديل المصدر العام', 'icon' => 'fas fa-book-open'],
        'fr' => ['title' => 'Sources générales', 'edit' => 'Modifier la source générale', 'icon' => 'fas fa-book-open']
    ]
];
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $type_info[$type][$lang]['edit'] ?> - Library System</title>
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
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
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
                <i class="<?= $type_info[$type][$lang]['icon'] ?>"></i>
            </div>
            <div>
                <h1><?= $type_info[$type][$lang]['edit'] ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'تعديل ' . $type_info[$type][$lang]['title'] : 'Modifier ' . $type_info[$type][$lang]['title'] ?>
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
                <?= $type_info[$type][$lang]['edit'] ?>
            </h2>
            <p class="form-subtitle">
                <?= $lang == 'ar' ? 'تعديل معلومات ' . $type_info[$type][$lang]['title'] : 'Modifier les informations de ' . $type_info[$type][$lang]['title'] ?>
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

        <form method="post" id="editForm">
            <div class="form-grid">
                <?php if ($type == 'book'): ?>
                    <!-- Formulaire pour les livres -->
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
                        </label>
                        <input type="text" id="author" name="author" class="form-input" 
                               value="<?= htmlspecialchars($item['author'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل اسم المؤلف' : 'Entrez le nom de l\'auteur' ?>">
                    </div>

                    <div class="form-group">
                        <label for="publisher" class="form-label">
                            <i class="fas fa-building"></i>
                            <?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?>
                        </label>
                        <input type="text" id="publisher" name="publisher" class="form-input" 
                               value="<?= htmlspecialchars($item['publisher'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل اسم الناشر' : 'Entrez le nom de l\'éditeur' ?>">
                    </div>

                    <div class="form-group">
                        <label for="year_pub" class="form-label">
                            <i class="fas fa-calendar"></i>
                            <?= $lang == 'ar' ? 'سنة النشر' : 'Année de publication' ?>
                        </label>
                        <input type="number" id="year_pub" name="year_pub" class="form-input" 
                               value="<?= htmlspecialchars($item['year_pub'] ?? '') ?>" 
                               min="1800" max="<?= date('Y') + 1 ?>" 
                               placeholder="<?= $lang == 'ar' ? 'مثال: 2023' : 'Ex: 2023' ?>">
                    </div>

                    <div class="form-group">
                        <label for="edition" class="form-label">
                            <i class="fas fa-layer-group"></i>
                            <?= $lang == 'ar' ? 'الطبعة' : 'Édition' ?>
                        </label>
                        <input type="text" id="edition" name="edition" class="form-input" 
                               value="<?= htmlspecialchars($item['edition'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'مثال: الطبعة الأولى' : 'Ex: Première édition' ?>">
                    </div>

                    <div class="form-group">
                        <label for="classification" class="form-label">
                            <i class="fas fa-tags"></i>
                            <?= $lang == 'ar' ? 'رقم التصنيف' : 'Numéro de classification' ?>
                        </label>
                        <input type="text" id="classification" name="classification" class="form-input" 
                               value="<?= htmlspecialchars($item['classification'] ?? '') ?>" 
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
                                <option value="<?= $type_key ?>" <?= ($item['book_type'] ?? '') == $type_key ? 'selected' : '' ?>>
                                    <?= $type_names[$lang] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <?php elseif ($type == 'magazine'): ?>
                    <!-- Formulaire pour les revues -->
                    <div class="form-group">
                        <label for="magazine_name" class="form-label">
                            <i class="fas fa-heading"></i>
                            <?= $lang == 'ar' ? 'اسم المجلة' : 'Nom de la revue' ?>
                            <span class="required-field">*</span>
                        </label>
                        <input type="text" id="magazine_name" name="magazine_name" class="form-input" 
                               value="<?= htmlspecialchars($item['magazine_name'] ?? $item['title']) ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل اسم المجلة' : 'Entrez le nom de la revue' ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading"></i>
                            <?= $lang == 'ar' ? 'عنوان المقال' : 'Titre de l\'article' ?>
                        </label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="<?= htmlspecialchars($item['title']) ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل عنوان المقال' : 'Entrez le titre de l\'article' ?>">
                    </div>

                    <div class="form-group">
                        <label for="volume_number" class="form-label">
                            <i class="fas fa-layer-group"></i>
                            <?= $lang == 'ar' ? 'رقم المجلد' : 'Numéro du volume' ?>
                        </label>
                        <input type="text" id="volume_number" name="volume_number" class="form-input" 
                               value="<?= htmlspecialchars($item['volume_number'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم المجلد' : 'Entrez le numéro du volume' ?>">
                    </div>

                    <div class="form-group">
                        <label for="classification" class="form-label">
                            <i class="fas fa-tags"></i>
                            <?= $lang == 'ar' ? 'رقم التصنيف' : 'Numéro de classification' ?>
                        </label>
                        <input type="text" id="classification" name="classification" class="form-input" 
                               value="<?= htmlspecialchars($item['classification'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم التصنيف' : 'Entrez le numéro de classification' ?>">
                    </div>

                <?php elseif ($type == 'newspaper'): ?>
                    <!-- Formulaire pour les journaux -->
                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-newspaper"></i>
                            <?= $lang == 'ar' ? 'اسم الجريدة' : 'Nom du journal' ?>
                            <span class="required-field">*</span>
                        </label>
                        <select id="title" name="title" class="form-input form-select" required>
                            <?php foreach ($newspaper_titles as $title_key => $title_names): ?>
                                <option value="<?= $title_key ?>" <?= $item['title'] == $title_key ? 'selected' : '' ?>>
                                    <?= $title_names[$lang] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="newspaper_number" class="form-label">
                            <i class="fas fa-hashtag"></i>
                            <?= $lang == 'ar' ? 'رقم الجريدة' : 'Numéro du journal' ?>
                            <span class="required-field">*</span>
                        </label>
                        <input type="text" id="newspaper_number" name="newspaper_number" class="form-input" 
                               value="<?= htmlspecialchars($item['newspaper_number'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم الجريدة' : 'Entrez le numéro du journal' ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="classification" class="form-label">
                            <i class="fas fa-tags"></i>
                            <?= $lang == 'ar' ? 'التصنيف' : 'Classification' ?>
                        </label>
                        <input type="text" id="classification" name="classification" class="form-input" 
                               value="<?= htmlspecialchars($item['classification'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل التصنيف' : 'Entrez la classification' ?>">
                    </div>

                <?php else: ?>
                    <!-- Formulaire générique -->
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

                    <div class="form-group">
                        <label for="author" class="form-label">
                            <i class="fas fa-user"></i>
                            <?= $lang == 'ar' ? 'المؤلف' : 'Auteur' ?>
                        </label>
                        <input type="text" id="author" name="author" class="form-input" 
                               value="<?= htmlspecialchars($item['author'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل اسم المؤلف' : 'Entrez le nom de l\'auteur' ?>">
                    </div>

                    <div class="form-group">
                        <label for="classification" class="form-label">
                            <i class="fas fa-tags"></i>
                            <?= $lang == 'ar' ? 'التصنيف' : 'Classification' ?>
                        </label>
                        <input type="text" id="classification" name="classification" class="form-input" 
                               value="<?= htmlspecialchars($item['classification'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل التصنيف' : 'Entrez la classification' ?>">
                    </div>

                <?php endif; ?>

                <!-- Champs communs -->
                <div class="form-group">
                    <label for="publisher" class="form-label">
                        <i class="fas fa-building"></i>
                        <?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?>
                    </label>
                    <input type="text" id="publisher" name="publisher" class="form-input" 
                           value="<?= htmlspecialchars($item['publisher'] ?? '') ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل اسم الناشر' : 'Entrez le nom de l\'éditeur' ?>">
                </div>

                <div class="form-group">
                    <label for="year_pub" class="form-label">
                        <i class="fas fa-calendar"></i>
                        <?= $lang == 'ar' ? 'سنة النشر' : 'Année de publication' ?>
                    </label>
                    <input type="number" id="year_pub" name="year_pub" class="form-input" 
                           value="<?= htmlspecialchars($item['year_pub'] ?? '') ?>" 
                           min="1800" max="<?= date('Y') + 1 ?>" 
                           placeholder="<?= $lang == 'ar' ? 'مثال: 2023' : 'Ex: 2023' ?>">
                </div>

                <div class="form-group">
                    <label for="copies" class="form-label">
                        <i class="fas fa-copy"></i>
                        <?= $lang == 'ar' ? 'عدد النسخ' : 'Nombre de copies' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="number" id="copies" name="copies" class="form-input" 
                           value="<?= htmlspecialchars($item['copies']) ?>" 
                           min="1" max="1000" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل عدد النسخ' : 'Entrez le nombre de copies' ?>" 
                           required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'حفظ التغييرات' : 'Enregistrer les modifications' ?>
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
document.getElementById('editForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري الحفظ...' : 'Enregistrement...' ?>';
    submitBtn.disabled = true;
    
    // Show success notification after a short delay
    setTimeout(() => {
        if (window.LibrarySystem) {
            window.LibrarySystem.showNotification(
                '<?= $lang == 'ar' ? 'تم الحفظ بنجاح!' : 'Enregistrement réussi!' ?>', 
                'success'
            );
        }
    }, 1000);
});
</script>
</body>
</html>
