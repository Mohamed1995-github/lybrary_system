<?php
/**
 * صفحة إضافة كتاب متخصص - نسخة محسنة
 */

session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) {
    header('Location: ../../login.php');
    exit;
}

// الاتصال بقاعدة البيانات
try {
    require_once __DIR__ . '/../../../config/db.php';
    $db_connected = true;
} catch (Exception $e) {
    $db_connected = false;
    $db_error = $e->getMessage();
}

$lang = $_GET['lang'] ?? 'ar';
$errors = [];
$success = '';

// معالجة النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db_connected) {
    $main_title = trim($_POST['main_title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $short_title = trim($_POST['short_title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $publication_place = trim($_POST['publication_place'] ?? '');
    $publication_year = $_POST['publication_year'] ?? '';
    $classification_number = trim($_POST['classification_number'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $field = $_POST['field'] ?? 'Administration';
    $copies = max(1, (int)($_POST['copies'] ?? 1));
    $book_lang = $_POST['book_lang'] ?? $lang;
    $shelf_number = trim($_POST['shelf_number'] ?? '');
    $drawer_number = trim($_POST['drawer_number'] ?? '');
    $parts = max(1, (int)($_POST['parts'] ?? 1));
    $registration_date = $_POST['registration_date'] ?? '';
    $modification_date = $_POST['modification_date'] ?? '';
    
    // التحقق من صحة البيانات
    if (empty($main_title)) {
        $errors[] = $lang == 'ar' ? 'العنوان الرئيسي مطلوب' : 'Le titre principal est requis';
    }
    if (empty($author)) {
        $errors[] = $lang == 'ar' ? 'المؤلف مطلوب' : 'L\'auteur est requis';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO items (main_title, subtitle, short_title, author, publisher, publication_place, publication_year, classification_number, isbn, type, field, copies, available_copies, shelf_number, drawer_number, parts, registration_date, modification_date, lang, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'book', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $main_title, $subtitle, $short_title, $author, $publisher, $publication_place, $publication_year, 
                $classification_number, $isbn, $field, $copies, $copies, $shelf_number, $drawer_number, $parts, $registration_date, $modification_date, $book_lang
            ]);
            
            $success = $lang == 'ar' ? 'تم إضافة الكتاب بنجاح!' : 'Livre ajouté avec succès!';
            
            // إعادة تعيين النموذج
            $_POST = [];
            
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات: ' . $e->getMessage() : 'Erreur de base de données: ' . $e->getMessage();
        }
    }
}

// التخصصات المتاحة
$fields = [
    'Administration' => ['ar' => 'الإدارة العامة', 'fr' => 'Administration Publique'],
    'Law' => ['ar' => 'القانون', 'fr' => 'Droit'],
    'Economics' => ['ar' => 'الاقتصاد', 'fr' => 'Économie'],
    'Diplomacy' => ['ar' => 'الدبلوماسية', 'fr' => 'Diplomatie'],
    'Media' => ['ar' => 'الإعلام والاتصالات', 'fr' => 'Médias et Communications']
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'إضافة كتاب متخصص' : 'Ajouter un Livre Spécialisé' ?> - نظام المكتبة</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --white: #ffffff;
            --success: #22c55e;
            --error: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .header-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header-subtitle {
            opacity: 0.9;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .form-card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-200);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .form-input, .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-600);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #166534;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-icon">📖</div>
        <h1 class="header-title"><?= $lang == 'ar' ? 'إضافة كتاب متخصص' : 'Ajouter un Livre Spécialisé' ?></h1>
        <p class="header-subtitle"><?= $lang == 'ar' ? 'إضافة كتاب أكاديمي متخصص للمكتبة' : 'Ajouter un livre académique spécialisé à la bibliothèque' ?></p>
    </div>

    <div class="container">
        <!-- رسائل النجاح والخطأ -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

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

        <?php if (!$db_connected): ?>
            <div class="alert alert-error">
                <i class="fas fa-database"></i>
                <?= $lang == 'ar' ? 'خطأ في الاتصال بقاعدة البيانات' : 'Erreur de connexion à la base de données' ?>
            </div>
        <?php endif; ?>

        <!-- النموذج -->
        <div class="form-card">
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="main_title">
                            <i class="fas fa-book"></i>
                            <?= $lang == 'ar' ? 'العنوان الرئيسي *' : 'Titre principal *' ?>
                        </label>
                        <input type="text" id="main_title" name="main_title" class="form-input" 
                               value="<?= htmlspecialchars($_POST['main_title'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل العنوان الرئيسي' : 'Entrez le titre principal' ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subtitle">
                            <i class="fas fa-book-open"></i>
                            <?= $lang == 'ar' ? 'العنوان الفرعي' : 'Sous-titre' ?>
                        </label>
                        <input type="text" id="subtitle" name="subtitle" class="form-input" 
                               value="<?= htmlspecialchars($_POST['subtitle'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل العنوان الفرعي' : 'Entrez le sous-titre' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="short_title">
                            <i class="fas fa-tag"></i>
                            <?= $lang == 'ar' ? 'العنوان المختصر' : 'Titre abrégé' ?>
                        </label>
                        <input type="text" id="short_title" name="short_title" class="form-input" 
                               value="<?= htmlspecialchars($_POST['short_title'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل العنوان المختصر' : 'Entrez le titre abrégé' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="author">
                            <i class="fas fa-user"></i>
                            <?= $lang == 'ar' ? 'المؤلف *' : 'Auteur *' ?>
                        </label>
                        <input type="text" id="author" name="author" class="form-input" 
                               value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل اسم المؤلف' : 'Entrez le nom de l\'auteur' ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="publisher">
                            <i class="fas fa-building"></i>
                            <?= $lang == 'ar' ? 'دار النشر' : 'Éditeur' ?>
                        </label>
                        <input type="text" id="publisher" name="publisher" class="form-input" 
                               value="<?= htmlspecialchars($_POST['publisher'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل دار النشر' : 'Entrez l\'éditeur' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="publication_place">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= $lang == 'ar' ? 'مكان النشر' : 'Lieu de publication' ?>
                        </label>
                        <input type="text" id="publication_place" name="publication_place" class="form-input" 
                               value="<?= htmlspecialchars($_POST['publication_place'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل مكان النشر' : 'Entrez le lieu de publication' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="publication_year">
                            <i class="fas fa-calendar"></i>
                            <?= $lang == 'ar' ? 'سنة النشر' : 'Année de publication' ?>
                        </label>
                        <input type="number" id="publication_year" name="publication_year" class="form-input" 
                               value="<?= htmlspecialchars($_POST['publication_year'] ?? '') ?>" 
                               min="1900" max="<?= date('Y') + 1 ?>"
                               placeholder="<?= date('Y') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="classification_number">
                            <i class="fas fa-hashtag"></i>
                            <?= $lang == 'ar' ? 'رقم التصنيف' : 'Numéro de classification' ?>
                        </label>
                        <input type="text" id="classification_number" name="classification_number" class="form-input" 
                               value="<?= htmlspecialchars($_POST['classification_number'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم التصنيف' : 'Entrez le numéro de classification' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="isbn">
                            <i class="fas fa-barcode"></i>
                            <?= $lang == 'ar' ? 'الرقم المعياري (ردمك)' : 'Numéro standard (ISBN)' ?>
                        </label>
                        <input type="text" id="isbn" name="isbn" class="form-input" 
                               value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>" 
                               placeholder="<?= $lang == 'ar' ? 'الرقم المعياري (ردمك)' : 'Numéro standard (ISBN)' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="field">
                            <i class="fas fa-tag"></i>
                            <?= $lang == 'ar' ? 'التخصص' : 'Spécialisation' ?>
                        </label>
                        <select id="field" name="field" class="form-select">
                            <?php foreach ($fields as $field_key => $field_names): ?>
                                <option value="<?= $field_key ?>" <?= ($_POST['field'] ?? 'Administration') === $field_key ? 'selected' : '' ?>>
                                    <?= $field_names[$lang] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="copies">
                            <i class="fas fa-copy"></i>
                            <?= $lang == 'ar' ? 'عدد النسخ' : 'Nombre de copies' ?>
                        </label>
                        <input type="number" id="copies" name="copies" class="form-input" 
                               value="<?= htmlspecialchars($_POST['copies'] ?? '1') ?>" 
                               min="1" max="100">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="book_lang">
                            <i class="fas fa-language"></i>
                            <?= $lang == 'ar' ? 'لغة الكتاب' : 'Langue du livre' ?>
                        </label>
                        <select id="book_lang" name="book_lang" class="form-select">
                            <option value="ar" <?= ($_POST['book_lang'] ?? $lang) === 'ar' ? 'selected' : '' ?>>
                                🇸🇦 <?= $lang == 'ar' ? 'العربية' : 'Arabe' ?>
                            </option>
                            <option value="fr" <?= ($_POST['book_lang'] ?? $lang) === 'fr' ? 'selected' : '' ?>>
                                🇫🇷 <?= $lang == 'ar' ? 'الفرنسية' : 'Français' ?>
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="shelf_number">
                            <i class="fas fa-warehouse"></i>
                            <?= $lang == 'ar' ? 'رقم الرف' : 'Numéro d\'étagère' ?>
                        </label>
                        <input type="text" id="shelf_number" name="shelf_number" class="form-input"
                               value="<?= htmlspecialchars($_POST['shelf_number'] ?? '') ?>"
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم الرف' : 'Entrez le numéro d\'étagère' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="drawer_number">
                            <i class="fas fa-drawer"></i>
                            <?= $lang == 'ar' ? 'رقم الدرج' : 'Numéro du tiroir' ?>
                        </label>
                        <input type="text" id="drawer_number" name="drawer_number" class="form-input"
                               value="<?= htmlspecialchars($_POST['drawer_number'] ?? '') ?>"
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم الدرج' : 'Entrez le numéro du tiroir' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="parts">
                            <i class="fas fa-layer-group"></i>
                            <?= $lang == 'ar' ? 'عدد الأجزاء' : 'Nombre de parties' ?>
                        </label>
                        <input type="number" id="parts" name="parts" class="form-input"
                               value="<?= htmlspecialchars($_POST['parts'] ?? '1') ?>" min="1" max="1000">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="registration_date">
                            <i class="fas fa-calendar-plus"></i>
                            <?= $lang == 'ar' ? 'تاريخ التسجيل' : 'Date d\'enregistrement' ?>
                        </label>
                        <input type="date" id="registration_date" name="registration_date" class="form-input"
                               value="<?= htmlspecialchars($_POST['registration_date'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="modification_date">
                            <i class="fas fa-calendar-edit"></i>
                            <?= $lang == 'ar' ? 'تاريخ التعديل' : 'Date de modification' ?>
                        </label>
                        <input type="date" id="modification_date" name="modification_date" class="form-input"
                               value="<?= htmlspecialchars($_POST['modification_date'] ?? '') ?>">
                    </div>
                </div>

                <!-- أزرار الإجراءات -->
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" <?= !$db_connected ? 'disabled' : '' ?>>
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة الكتاب' : 'Ajouter le livre' ?>
                    </button>
                    
                        <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        <i class="fas fa-times"></i>
                        <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- روابط سريعة -->
        <div style="margin-top: 2rem; text-align: center;">
            <h3 style="color: var(--gray-600); margin-bottom: 1rem;">
                <?= $lang == 'ar' ? 'إضافة مصادر أخرى' : 'Ajouter d\'autres ressources' ?>
            </h3>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="add_general_book.php?lang=<?=$lang?>" class="btn btn-secondary">
                    📚 <?= $lang == 'ar' ? 'كتاب عام' : 'Livre Général' ?>
                </a>
                <a href="add_magazine.php?lang=<?=$lang?>" class="btn btn-secondary">
                    📰 <?= $lang == 'ar' ? 'مجلة متخصصة' : 'Revue Spécialisée' ?>
                </a>
                <a href="add_newspaper.php?lang=<?=$lang?>" class="btn btn-secondary">
                    🗞️ <?= $lang == 'ar' ? 'جريدة' : 'Journal' ?>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Animation للنموذج
            const formCard = document.querySelector('.form-card');
            formCard.style.opacity = '0';
            formCard.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                formCard.style.transition = 'all 0.6s ease';
                formCard.style.opacity = '1';
                formCard.style.transform = 'translateY(0)';
            }, 200);
            
            // تركيز على الحقل الأول
            document.getElementById('main_title').focus();
            
            console.log('✅ صفحة إضافة الكتاب المتخصص (محسنة) محملة');
        });
    </script>
</body>
</html>


