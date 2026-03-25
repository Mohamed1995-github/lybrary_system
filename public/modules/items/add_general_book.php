<?php
/**
 * صفحة إضافة كتاب عام - نسخة محسنة
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
    $book_type = $_POST['book_type'] ?? 'general';
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
                INSERT INTO items (main_title, subtitle, short_title, author, publisher, publication_place, publication_year, classification_number, isbn, type, book_type, copies, available_copies, shelf_number, drawer_number, parts, registration_date, modification_date, lang, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'book', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $main_title, $subtitle, $short_title, $author, $publisher, $publication_place, $publication_year, 
                $classification_number, $isbn, $book_type, $copies, $copies, $shelf_number, $drawer_number, $parts, $registration_date, $modification_date, $book_lang
            ]);
            
            $success = $lang == 'ar' ? 'تم إضافة الكتاب بنجاح!' : 'Livre ajouté avec succès!';
            
            // إعادة تعيين النموذج
            $_POST = [];
            
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات: ' . $e->getMessage() : 'Erreur de base de données: ' . $e->getMessage();
        }
    }
}

// أنواع الكتب المتاحة
$book_types = [
    'novel' => ['ar' => '📖 رواية', 'fr' => '📖 Roman'],
    'poetry' => ['ar' => '✍️ شعر', 'fr' => '✍️ Poésie'],
    'biography' => ['ar' => '👤 سيرة ذاتية', 'fr' => '👤 Biographie'],
    'history' => ['ar' => '🏛️ تاريخ', 'fr' => '🏛️ Histoire'],
    'philosophy' => ['ar' => '🤔 فلسفة', 'fr' => '🤔 Philosophie'],
    'science' => ['ar' => '🔬 علوم', 'fr' => '🔬 Sciences'],
    'art' => ['ar' => '🎨 فنون', 'fr' => '🎨 Arts'],
    'general' => ['ar' => '📚 عام', 'fr' => '📚 Général'],
    'reference' => ['ar' => '📖 مرجع', 'fr' => '📖 Référence'],
    'other' => ['ar' => '❓ أخرى', 'fr' => '❓ Autre']
];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'إضافة كتاب عام' : 'Ajouter un livre général' ?> - نظام المكتبة</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #5a67d8;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        .header {
            background: transparent;
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .header-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.95;
        }

        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(<?= $lang == 'ar' ? '5px' : '-5px' ?>);
        }

        .form-card {
            background: var(--white);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255,255,255,0.2);
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-section-title i {
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--primary-color);
            font-size: 1rem;
        }

        .required {
            color: var(--error);
        }

        .form-input, .form-select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--gray-50);
            font-family: inherit;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-input:hover, .form-select:hover {
            border-color: var(--gray-300);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%);
            color: #991b1b;
            border-left: 4px solid var(--error);
        }

        .alert i {
            font-size: 1.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-600);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 2px solid var(--gray-200);
        }

        .info-box {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid #3b82f6;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .info-box i {
            color: #1e40af;
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }

        .info-box-content {
            flex: 1;
        }

        .info-box-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 0.25rem;
        }

        .info-box-text {
            color: #1e3a8a;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem 0.5rem;
            }

            .header-title {
                font-size: 1.75rem;
            }

            .container {
                padding: 1rem 0.5rem;
            }

            .form-card {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-icon">📚</div>
        <h1 class="header-title"><?= $lang == 'ar' ? 'إضافة كتاب عام' : 'Ajouter un livre général' ?></h1>
        <p class="header-subtitle"><?= $lang == 'ar' ? 'أضف كتاباً عاماً جديداً إلى المكتبة' : 'Ajoutez un nouveau livre général à la bibliothèque' ?></p>
    </div>

    <div class="container">
        <a href="../../dashboard.php?lang=<?=$lang?>" class="back-link">
            <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
            <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
        </a>

        <!-- رسائل النجاح والخطأ -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <div>• <?= htmlspecialchars($error) ?></div>
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

        <div class="form-card">
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <div class="info-box-content">
                    <div class="info-box-title">
                        <?= $lang == 'ar' ? 'ملاحظة مهمة' : 'Remarque importante' ?>
                    </div>
                    <div class="info-box-text">
                        <?= $lang == 'ar' 
                            ? 'الحقول المميزة بعلامة (*) إلزامية. يرجى ملء جميع المعلومات المتاحة لتسهيل البحث والإدارة.' 
                            : 'Les champs marqués d\'un (*) sont obligatoires. Veuillez remplir toutes les informations disponibles pour faciliter la recherche et la gestion.' 
                        ?>
                    </div>
                </div>
            </div>

            <form method="POST" action="">
                <!-- المعلومات الأساسية -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-book"></i>
                        <?= $lang == 'ar' ? 'المعلومات الأساسية' : 'Informations de base' ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="main_title">
                                <i class="fas fa-heading"></i>
                                <?= $lang == 'ar' ? 'العنوان الرئيسي' : 'Titre principal' ?>
                                <span class="required">*</span>
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
                    </div>
                </div>

                <!-- المؤلف والناشر -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-pen-fancy"></i>
                        <?= $lang == 'ar' ? 'المؤلف والناشر' : 'Auteur et Éditeur' ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="author">
                                <i class="fas fa-user-edit"></i>
                                <?= $lang == 'ar' ? 'المؤلف' : 'Auteur' ?>
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="author" name="author" class="form-input" 
                                   value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" 
                                   placeholder="<?= $lang == 'ar' ? 'أدخل اسم المؤلف' : 'Entrez le nom de l\'auteur' ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="publisher">
                                <i class="fas fa-industry"></i>
                                <?= $lang == 'ar' ? 'دار النشر' : 'Maison d\'édition' ?>
                            </label>
                            <input type="text" id="publisher" name="publisher" class="form-input" 
                                   value="<?= htmlspecialchars($_POST['publisher'] ?? '') ?>" 
                                   placeholder="<?= $lang == 'ar' ? 'أدخل دار النشر' : 'Entrez la maison d\'édition' ?>">
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
                    </div>
                </div>

                <!-- معلومات النشر والتصنيف -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-list-check"></i>
                        <?= $lang == 'ar' ? 'معلومات النشر والتصنيف' : 'Informations de publication' ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="publication_year">
                                <i class="fas fa-calendar-alt"></i>
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
                                <?= $lang == 'ar' ? 'الرقم المعياري (ردمك)' : 'ISBN' ?>
                            </label>
                            <input type="text" id="isbn" name="isbn" class="form-input" 
                                   value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>" 
                                   placeholder="<?= $lang == 'ar' ? 'الرقم المعياري (ردمك)' : 'Numéro d\'ISBN' ?>">
                        </div>
                    </div>
                </div>

                <!-- النسخ والتخزين -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-warehouse"></i>
                        <?= $lang == 'ar' ? 'النسخ والتخزين' : 'Exemplaires et stockage' ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="copies">
                                <i class="fas fa-copy"></i>
                                <?= $lang == 'ar' ? 'عدد النسخ' : 'Nombre de copies' ?>
                            </label>
                            <input type="number" id="copies" name="copies" class="form-input" 
                                   value="<?= htmlspecialchars($_POST['copies'] ?? '1') ?>" 
                                   min="1" max="1000">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="parts">
                                <i class="fas fa-layer-group"></i>
                                <?= $lang == 'ar' ? 'عدد الأجزاء' : 'Nombre de parties' ?>
                            </label>
                            <input type="number" id="parts" name="parts" class="form-input" 
                                   value="<?= htmlspecialchars($_POST['parts'] ?? '1') ?>" 
                                   min="1" max="1000">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="shelf_number">
                                <i class="fas fa-square"></i>
                                <?= $lang == 'ar' ? 'رقم الرف' : 'Numéro d\'étagère' ?>
                            </label>
                            <input type="text" id="shelf_number" name="shelf_number" class="form-input"
                                   value="<?= htmlspecialchars($_POST['shelf_number'] ?? '') ?>"
                                   placeholder="<?= $lang == 'ar' ? 'أدخل رقم الرف' : 'Entrez le numéro d\'étagère' ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="drawer_number">
                                <i class="fas fa-inbox"></i>
                                <?= $lang == 'ar' ? 'رقم الدرج' : 'Numéro du tiroir' ?>
                            </label>
                            <input type="text" id="drawer_number" name="drawer_number" class="form-input"
                                   value="<?= htmlspecialchars($_POST['drawer_number'] ?? '') ?>"
                                   placeholder="<?= $lang == 'ar' ? 'أدخل رقم الدرج' : 'Entrez le numéro du tiroir' ?>">
                        </div>
                    </div>
                </div>

                <!-- اللغة والنوعية -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-tags"></i>
                        <?= $lang == 'ar' ? 'اللغة والنوعية' : 'Langue et Type' ?>
                    </div>

                    <div class="form-row">
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
                            <label class="form-label" for="book_type">
                                <i class="fas fa-layer-group"></i>
                                <?= $lang == 'ar' ? 'نوعية الكتاب' : 'Type de livre' ?>
                            </label>
                            <select id="book_type" name="book_type" class="form-select">
                                <?php foreach ($book_types as $type_key => $type_names): ?>
                                    <option value="<?= $type_key ?>" <?= ($_POST['book_type'] ?? 'general') === $type_key ? 'selected' : '' ?>>
                                        <?= $type_names[$lang] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- التواريخ -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-calendar"></i>
                        <?= $lang == 'ar' ? 'التواريخ' : 'Dates' ?>
                    </div>

                    <div class="form-row">
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
                                <i class="fas fa-calendar-check"></i>
                                <?= $lang == 'ar' ? 'تاريخ التعديل' : 'Date de modification' ?>
                            </label>
                            <input type="date" id="modification_date" name="modification_date" class="form-input"
                                   value="<?= htmlspecialchars($_POST['modification_date'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- أزرار الإجراءات -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" <?= !$db_connected ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i>
                        <?= $lang == 'ar' ? 'حفظ الكتاب' : 'Enregistrer le livre' ?>
                    </button>

                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        <i class="fas fa-times"></i>
                        <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // تركيز على الحقل الأول
            document.getElementById('main_title').focus();
            console.log('✅ صفحة إضافة الكتاب العام محملة');
        });
    </script>
</body>
</html>
