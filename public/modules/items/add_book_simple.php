<?php
/* modules/items/add_book_simple.php - Formulaire simplifié d'ajout de livres */
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';
$specialization = $_GET['specialization'] ?? 'administration';
$category = $_GET['category'] ?? 'specialized';

// Mapper les spécialisations
$specialization_map = [
    'administration' => [
        'ar' => 'الإدارة العامة',
        'fr' => 'Administration Publique',
        'icon' => 'fas fa-building'
    ],
    'law' => [
        'ar' => 'القانون',
        'fr' => 'Droit',
        'icon' => 'fas fa-gavel'
    ],
    'economics' => [
        'ar' => 'الاقتصاد',
        'fr' => 'Économie',
        'icon' => 'fas fa-chart-line'
    ],
    'diplomacy' => [
        'ar' => 'الدبلوماسية',
        'fr' => 'Diplomatie',
        'icon' => 'fas fa-handshake'
    ]
];

$current_specialization = $specialization_map[$specialization] ?? $specialization_map['administration'];

// Valeurs par défaut
$item = [
    'classification_number' => '',
    'title' => '',
    'author' => '',
    'publisher' => '',
    'publication_year' => '',
    'copies' => 1
];

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classification_number = trim($_POST['classification_number'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $publication_year = trim($_POST['publication_year'] ?? '');
    $copies = (int)($_POST['copies'] ?? 1);
    
    // Validation
    if (empty($classification_number)) {
        $errors[] = $lang == 'ar' ? 'رقم التصنيف مطلوب' : 'Le numéro de classification est requis';
    }
    
    if (empty($title)) {
        $errors[] = $lang == 'ar' ? 'عنوان الكتاب مطلوب' : 'Le titre du livre est requis';
    }
    
    if (empty($author)) {
        $errors[] = $lang == 'ar' ? 'اسم المؤلف مطلوب' : 'Le nom de l\'auteur est requis';
    }
    
    if (empty($publisher)) {
        $errors[] = $lang == 'ar' ? 'دار النشر مطلوبة' : 'La maison d\'édition est requise';
    }
    
    if (empty($publication_year)) {
        $errors[] = $lang == 'ar' ? 'سنة النشر مطلوبة' : 'L\'année de publication est requise';
    }
    
    if ($copies < 1) {
        $errors[] = $lang == 'ar' ? 'العدد يجب أن يكون 1 على الأقل' : 'Le nombre doit être au moins 1';
    }
    
    // Vérifier si le titre existe déjà (au lieu du numéro de classification)
    if (empty($errors)) {
        try {
            $check_stmt = $pdo->prepare("SELECT id FROM items WHERE title = ? AND type = 'book' AND lang = ?");
            $check_stmt->execute([$title, $lang]);
            if ($check_stmt->fetch()) {
                $errors[] = $lang == 'ar' ? 'عنوان الكتاب موجود بالفعل' : 'Le titre du livre existe déjà';
            }
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
        }
    }
    
    // Si pas d'erreurs, insérer le livre
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO items (lang, type, title, author, publisher, publication_year, copies, available_copies, field, isbn, created_at) 
                VALUES (?, 'book', ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $field_name = ucfirst($specialization);
            $stmt->execute([
                $lang, 
                $title, 
                $author, 
                $publisher, 
                $publication_year, 
                $copies, 
                $copies,
                $field_name,
                $classification_number
            ]);
            
            $success = $lang == 'ar' ? 'تم إضافة الكتاب بنجاح!' : 'Livre ajouté avec succès!';
            
            // Reset form
            $item = [
                'classification_number' => '',
                'title' => '',
                'author' => '',
                'publisher' => '',
                'publication_year' => '',
                'copies' => 1
            ];
            
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في إضافة الكتاب' : 'Erreur lors de l\'ajout du livre';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'إضافة كتاب جديد - ' . $current_specialization['ar'] : 'Ajouter un livre - ' . $current_specialization['fr'] ?> - Library System</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.form-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    text-align: center;
}

.form-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
}

.form-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.form-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.required-field {
    color: #ef4444;
}

.form-input {
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.alert-success {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a67d8;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #6b7280;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    margin-bottom: 2rem;
}

.back-button:hover {
    background: #4b5563;
    color: white;
    text-decoration: none;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
<link rel="stylesheet" href="assets/css/professional-theme.css">
</head>
<body>
    <div class="form-container">
        <a href="manage_books.php?specialization=<?=$specialization?>&category=<?=$category?>&lang=<?=$lang?>" class="back-button">
            <i class="fas fa-arrow-left"></i>
            <?= $lang == 'ar' ? 'العودة لإدارة الكتب' : 'Retour à la gestion des livres' ?>
        </a>

        <div class="form-header">
            <div style="font-size: 3rem; margin-bottom: 1rem;">
                <i class="<?= $current_specialization['icon'] ?>"></i>
            </div>
            <h1><?= $lang == 'ar' ? 'إضافة كتاب جديد' : 'Ajouter un nouveau livre' ?></h1>
            <p><?= $lang == 'ar' ? 'في تخصص ' . $current_specialization['ar'] : 'dans la spécialisation ' . $current_specialization['fr'] ?></p>
        </div>

        <div class="form-card">
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

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="classification_number" class="form-label">
                            <i class="fas fa-hashtag"></i>
                            <?= $lang == 'ar' ? 'رقم التصنيف' : 'Numéro de classification' ?>
                            <span class="required-field">*</span>
                        </label>
                        <input type="text" id="classification_number" name="classification_number" class="form-input" 
                               value="<?= htmlspecialchars($item['classification_number']) ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم التصنيف' : 'Entrez le numéro de classification' ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-book"></i>
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
                            <?= $lang == 'ar' ? 'دار النشر' : 'Maison d\'édition' ?>
                            <span class="required-field">*</span>
                        </label>
                        <input type="text" id="publisher" name="publisher" class="form-input" 
                               value="<?= htmlspecialchars($item['publisher']) ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل دار النشر' : 'Entrez la maison d\'édition' ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="publication_year" class="form-label">
                            <i class="fas fa-calendar"></i>
                            <?= $lang == 'ar' ? 'سنة النشر' : 'Année de publication' ?>
                            <span class="required-field">*</span>
                        </label>
                        <input type="number" id="publication_year" name="publication_year" class="form-input" 
                               value="<?= htmlspecialchars($item['publication_year']) ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل سنة النشر' : 'Entrez l\'année de publication' ?>" 
                               min="1900" max="<?= date('Y') + 1 ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="copies" class="form-label">
                            <i class="fas fa-copy"></i>
                            <?= $lang == 'ar' ? 'العدد' : 'Nombre d\'exemplaires' ?>
                            <span class="required-field">*</span>
                        </label>
                        <input type="number" id="copies" name="copies" class="form-input" 
                               value="<?= htmlspecialchars($item['copies']) ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل عدد النسخ' : 'Entrez le nombre d\'exemplaires' ?>" 
                               min="1" max="100" 
                               required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة الكتاب' : 'Ajouter le livre' ?>
                    </button>
                    
                    <a href="manage_books.php?specialization=<?=$specialization?>&category=<?=$category?>&lang=<?=$lang?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validation côté client
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const classificationInput = document.getElementById('classification_number');
            const yearInput = document.getElementById('publication_year');
            const copiesInput = document.getElementById('copies');
            
            // Validation du numéro de classification
            classificationInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            // Validation de l'année
            yearInput.addEventListener('input', function() {
                const year = parseInt(this.value);
                const currentYear = new Date().getFullYear();
                if (year > currentYear + 1) {
                    this.value = currentYear;
                }
            });
            
            // Validation du nombre d'exemplaires
            copiesInput.addEventListener('input', function() {
                if (this.value < 1) {
                    this.value = 1;
                }
            });
            
            // Soumission du formulaire
            form.addEventListener('submit', function(e) {
                const classification = classificationInput.value.trim();
                const title = document.getElementById('title').value.trim();
                const author = document.getElementById('author').value.trim();
                const publisher = document.getElementById('publisher').value.trim();
                const year = yearInput.value.trim();
                const copies = copiesInput.value.trim();
                
                if (!classification || !title || !author || !publisher || !year || !copies) {
                    e.preventDefault();
                    alert('<?= $lang == 'ar' ? 'يرجى ملء جميع الحقول المطلوبة' : 'Veuillez remplir tous les champs requis' ?>');
                }
            });
        });
    </script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>
