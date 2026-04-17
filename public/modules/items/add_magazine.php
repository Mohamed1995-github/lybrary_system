<?php
/* modules/items/add_magazine.php */
require_once __DIR__ . '/../../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../login.php'); exit; }

$lang = $_GET['lang'] ?? 'ar';
$type = 'magazine';

$item = [
    'title' => '', 'magazine_type' => '', 'issue_number' => '',
    'publication_date' => '', 'publisher' => '', 'publication_place' => '',
    'issn' => '', 'classification_number' => '', 'magazine_number' => '',
    'cabinet_number' => '', 'shelf_number' => '', 'drawer_number' => '',
    'registration_date' => '', 'modification_date' => '', 'periodical_file' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title              = trim($_POST['title'] ?? '');
    $magazine_type      = trim($_POST['magazine_type'] ?? '');
    $issue_number       = trim($_POST['issue_number'] ?? '');
    $publication_date   = trim($_POST['publication_date'] ?? '');
    $publisher          = trim($_POST['publisher'] ?? '');
    $publication_place  = trim($_POST['publication_place'] ?? '');
    $issn               = trim($_POST['issn'] ?? '');
    $classification_number = trim($_POST['classification_number'] ?? '');
    $magazine_number    = trim($_POST['magazine_number'] ?? '');
    $cabinet_number     = trim($_POST['cabinet_number'] ?? '');
    $shelf_number       = trim($_POST['shelf_number'] ?? '');
    $drawer_number      = trim($_POST['drawer_number'] ?? '');
    $registration_date  = trim($_POST['registration_date'] ?? '');
    $modification_date  = trim($_POST['modification_date'] ?? '');
    $periodical_file    = null;

    $errors = [];

    if (empty($title))
        $errors[] = $lang == 'ar' ? 'العنوان مطلوب' : 'Le titre est requis';
    if (empty($issue_number))
        $errors[] = $lang == 'ar' ? 'العدد مطلوب' : 'Le numéro est requis';

    $uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'magazines';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $errors[] = $lang == 'ar' ? 'فشل إنشاء مجلد الرفع' : 'Erreur de création de dossier';
        }
    }
    if (!is_writable($uploadDir)) {
        $errors[] = $lang == 'ar' ? 'مجلد الرفع غير قابل للكتابة' : 'Dossier non accessible';
    }

    $uploadedFilePath = null;
    if (!empty($_FILES['periodical_file']['name']) && empty($errors)) {
        $file = $_FILES['periodical_file'];
        $maxSize = 10 * 1024 * 1024;
        
        $allowedMimes = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => 'png'
        ];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = $lang == 'ar' ? 'خطأ في رفع الملف' : 'Erreur lors du téléchargement';
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = $lang == 'ar' ? 'ملف غير صحيح' : 'Fichier invalide';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = $lang == 'ar' ? 'حجم الملف كبير جداً (الحد الأقصى 10 MB)' : 'Fichier trop volumineux (max 10 MB)';
        } elseif ($file['size'] === 0) {
            $errors[] = $lang == 'ar' ? 'الملف فارغ' : 'Le fichier est vide';
        } else {
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!array_key_exists($mimeType, $allowedMimes)) {
                $errors[] = $lang == 'ar' ? 'نوع الملف غير مدعوم' : 'Type de fichier non supporté';
            } else {
                $allowedExtsForMime = $allowedMimes[$mimeType];
                if (is_string($allowedExtsForMime)) {
                    $allowedExtsForMime = [$allowedExtsForMime];
                }
                
                if (!in_array($fileExt, $allowedExtsForMime, true)) {
                    $errors[] = $lang == 'ar' ? 'عدم تطابق نوع الملف' : 'Type de fichier invalide';
                } else {
                    $secureExt = $allowedExtsForMime[0];
                    $periodical_file = 'magazine_' . bin2hex(random_bytes(16)) . '.' . $secureExt;
                    $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $periodical_file;
                    $uploadedFilePath = $uploadPath;
                    
                    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $errors[] = $lang == 'ar' ? 'فشل حفظ الملف' : 'Erreur lors de la sauvegarde';
                        $periodical_file = null;
                        $uploadedFilePath = null;
                    } else {
                        chmod($uploadPath, 0644);
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, magazine_type, issue_number, publication_date, publisher, publication_place, issn, classification_number, magazine_number, cabinet_number, shelf_number, drawer_number, registration_date, modification_date, periodical_file, copies, available_copies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)");
            $stmt->execute([$lang, $type, $title, $magazine_type, $issue_number, $publication_date, $publisher, $publication_place, $issn, $classification_number, $magazine_number, $cabinet_number, $shelf_number, $drawer_number, $registration_date, $modification_date, $periodical_file]);
            $success = $lang == 'ar' ? 'تم إضافة الدورية بنجاح!' : 'Périodique ajoutée avec succès!';
            $_POST = [];
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
            if ($uploadedFilePath && file_exists($uploadedFilePath)) unlink($uploadedFilePath);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'إضافة دورية / مجلة' : 'Ajouter une Revue' ?> - نظام المكتبة</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0891b2;
            --primary-dark: #0e7490;
            --primary-light: #67e8f9;
            --primary-bg: rgba(8, 145, 178, 0.08);
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --white: #ffffff;
            --success-bg: rgba(34,197,94,0.1);
            --success-fg: #166534;
            --success-border: rgba(34,197,94,0.2);
            --error-bg: rgba(239,68,68,0.1);
            --error-fg: #991b1b;
            --error-border: rgba(239,68,68,0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.6;
        }

        /* ===== Header ===== */
        .header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }
        .header-icon { font-size: 3rem; margin-bottom: 0.75rem; opacity: 0.9; }
        .header-title { font-size: 1.875rem; font-weight: 700; margin-bottom: 0.35rem; }
        .header-subtitle { opacity: 0.88; font-size: 0.95rem; }

        /* ===== Container ===== */
        .container { max-width: 860px; margin: 0 auto; padding: 2rem; }

        /* ===== Alerts ===== */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.925rem;
        }
        .alert i { margin-top: 2px; flex-shrink: 0; }
        .alert-success { background: var(--success-bg); color: var(--success-fg); border: 1px solid var(--success-border); }
        .alert-error   { background: var(--error-bg);   color: var(--error-fg);   border: 1px solid var(--error-border); }
        .alert-error ul { margin: 0; padding-inline-start: 1.25rem; }

        /* ===== Card ===== */
        .form-card {
            background: var(--white);
            border-radius: 14px;
            padding: 2rem 2.25rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border: 1px solid var(--gray-200);
        }

        /* ===== Section headings ===== */
        .section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary);
            margin: 1.75rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-bg);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .section-title:first-child { margin-top: 0; }

        /* ===== Grid ===== */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.1rem;
        }
        .form-grid .full { grid-column: 1 / -1; }

        /* ===== Form elements ===== */
        .form-group { display: flex; flex-direction: column; gap: 0.35rem; }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .form-label i { color: var(--primary); font-size: 0.8rem; }
        .required { color: #ef4444; }

        .form-input {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border: 1.5px solid var(--gray-200);
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--gray-800);
            background: var(--white);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(8,145,178,0.12);
        }
        .form-input::placeholder { color: var(--gray-300); }

        /* ===== File upload ===== */
        .file-wrapper {
            position: relative;
            border: 2px dashed var(--primary);
            border-radius: 10px;
            background: var(--primary-bg);
            transition: background 0.2s;
            overflow: hidden;
        }
        .file-wrapper:hover { background: rgba(8,145,178,0.14); }
        .file-wrapper input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }
        .file-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1.1rem;
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 0.9rem;
            pointer-events: none;
            position: relative;
            z-index: 1;
        }
        .file-label i { font-size: 1.2rem; }
        #fileName { font-size: 0.85rem; color: var(--primary-dark); font-weight: 600; margin-top: 0.4rem; text-align: center; padding-bottom: 0.5rem; }

        /* ===== Buttons ===== */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1.5px solid var(--gray-200);
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(8,145,178,0.3); }
        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-600);
            border: 1.5px solid var(--gray-200);
        }
        .btn-secondary:hover { background: var(--gray-200); }

        /* ===== Responsive ===== */
        @media (max-width: 640px) {
            .container { padding: 1rem; }
            .form-card { padding: 1.25rem; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid .full { grid-column: 1; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-icon">📰</div>
    <h1 class="header-title"><?= $lang == 'ar' ? 'إضافة دورية / مجلة' : 'Ajouter une Revue / Périodique' ?></h1>
    <p class="header-subtitle"><?= $lang == 'ar' ? 'تسجيل مجلة أو دورية جديدة في المكتبة' : 'Enregistrer une nouvelle revue dans la bibliothèque' ?></p>
</div>

<div class="container">

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data" id="magazineForm">

            <!-- ── معلومات أساسية ── -->
            <div class="section-title"><i class="fas fa-info-circle"></i><?= $lang == 'ar' ? 'المعلومات الأساسية' : 'Informations principales' ?></div>
            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label" for="title">
                        <i class="fas fa-heading"></i>
                        <?= $lang == 'ar' ? 'العنوان' : 'Titre' ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="title" name="title" class="form-input"
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'أدخل عنوان الدورية' : 'Entrez le titre de la revue' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="magazine_type">
                        <i class="fas fa-tag"></i>
                        <?= $lang == 'ar' ? 'النوع' : 'Type' ?>
                    </label>
                    <input type="text" id="magazine_type" name="magazine_type" class="form-input"
                           value="<?= htmlspecialchars($_POST['magazine_type'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'نوع الدورية' : 'Type de revue' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="issue_number">
                        <i class="fas fa-hashtag"></i>
                        <?= $lang == 'ar' ? 'العدد' : 'Numéro' ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="issue_number" name="issue_number" class="form-input"
                           value="<?= htmlspecialchars($_POST['issue_number'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'رقم العدد' : 'Numéro du volume' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="issn">
                        <i class="fas fa-barcode"></i>
                        <?= $lang == 'ar' ? 'الرقم المعياري (ISSN)' : 'Numéro standard (ISSN)' ?>
                    </label>
                    <input type="text" id="issn" name="issn" class="form-input"
                           value="<?= htmlspecialchars($_POST['issn'] ?? '') ?>"
                           placeholder="XXXX-XXXX">
                </div>

                <div class="form-group">
                    <label class="form-label" for="publication_date">
                        <i class="fas fa-calendar-alt"></i>
                        <?= $lang == 'ar' ? 'تاريخ النشر' : 'Date de publication' ?>
                    </label>
                    <input type="date" id="publication_date" name="publication_date" class="form-input"
                           value="<?= htmlspecialchars($_POST['publication_date'] ?? '') ?>">
                </div>
            </div>

            <!-- ── بيانات الناشر ── -->
            <div class="section-title"><i class="fas fa-building"></i><?= $lang == 'ar' ? 'بيانات الناشر' : 'Informations éditeur' ?></div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="publisher">
                        <i class="fas fa-building"></i>
                        <?= $lang == 'ar' ? 'دار النشر' : 'Éditeur' ?>
                    </label>
                    <input type="text" id="publisher" name="publisher" class="form-input"
                           value="<?= htmlspecialchars($_POST['publisher'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'اسم دار النشر' : 'Nom de l\'éditeur' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="publication_place">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= $lang == 'ar' ? 'مكان النشر' : 'Lieu de publication' ?>
                    </label>
                    <input type="text" id="publication_place" name="publication_place" class="form-input"
                           value="<?= htmlspecialchars($_POST['publication_place'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'مدينة / بلد النشر' : 'Ville / pays de publication' ?>">
                </div>
            </div>

            <!-- ── التصنيف والموقع ── -->
            <div class="section-title"><i class="fas fa-archive"></i><?= $lang == 'ar' ? 'التصنيف والموقع' : 'Classification et localisation' ?></div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="classification_number">
                        <i class="fas fa-list-ol"></i>
                        <?= $lang == 'ar' ? 'رقم التصنيف' : 'Numéro de classification' ?>
                    </label>
                    <input type="text" id="classification_number" name="classification_number" class="form-input"
                           value="<?= htmlspecialchars($_POST['classification_number'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'رقم التصنيف' : 'Cote de classification' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="magazine_number">
                        <i class="fas fa-newspaper"></i>
                        <?= $lang == 'ar' ? 'رقم المجلة' : 'Numéro de la revue' ?>
                    </label>
                    <input type="text" id="magazine_number" name="magazine_number" class="form-input"
                           value="<?= htmlspecialchars($_POST['magazine_number'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'الرقم الداخلي للمجلة' : 'Numéro interne de la revue' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="cabinet_number">
                        <i class="fas fa-archive"></i>
                        <?= $lang == 'ar' ? 'رقم الخزانة' : 'Numéro d\'armoire' ?>
                    </label>
                    <input type="text" id="cabinet_number" name="cabinet_number" class="form-input"
                           value="<?= htmlspecialchars($_POST['cabinet_number'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'رقم الخزانة' : 'N° armoire' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="shelf_number">
                        <i class="fas fa-layer-group"></i>
                        <?= $lang == 'ar' ? 'رقم الرف' : 'Numéro d\'étagère' ?>
                    </label>
                    <input type="text" id="shelf_number" name="shelf_number" class="form-input"
                           value="<?= htmlspecialchars($_POST['shelf_number'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'رقم الرف' : 'N° étagère' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="drawer_number">
                        <i class="fas fa-box"></i>
                        <?= $lang == 'ar' ? 'رقم الدرج' : 'Numéro de tiroir' ?>
                    </label>
                    <input type="text" id="drawer_number" name="drawer_number" class="form-input"
                           value="<?= htmlspecialchars($_POST['drawer_number'] ?? '') ?>"
                           placeholder="<?= $lang == 'ar' ? 'رقم الدرج' : 'N° tiroir' ?>">
                </div>
            </div>

            <!-- ── التواريخ ── -->
            <div class="section-title"><i class="fas fa-calendar"></i><?= $lang == 'ar' ? 'التواريخ' : 'Dates' ?></div>
            <div class="form-grid">
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

            <!-- ── الملف ── -->
            <div class="section-title"><i class="fas fa-upload"></i><?= $lang == 'ar' ? 'رفع الملف' : 'Fichier numérique' ?></div>
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-file-alt"></i>
                    <?= $lang == 'ar' ? 'ملف الدورية (اختياري)' : 'Fichier de la revue (optionnel)' ?>
                </label>
                <div class="file-wrapper">
                    <input type="file" id="periodical_file" name="periodical_file"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                           onchange="updateFileName(this)">
                    <label for="periodical_file" class="file-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <?= $lang == 'ar' ? 'اضغط لاختيار ملف أو اسحبه هنا' : 'Cliquez pour sélectionner ou déposer un fichier' ?>
                    </label>
                    <div id="fileName"></div>
                </div>
                <small style="color:var(--gray-500);font-size:0.8rem;">
                    <?= $lang == 'ar' ? 'PDF, DOC, DOCX, JPG, PNG — الحد الأقصى 10 MB' : 'PDF, DOC, DOCX, JPG, PNG — max 10 MB' ?>
                </small>
            </div>

            <!-- ── الأزرار ── -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'حفظ الدورية' : 'Enregistrer la revue' ?>
                </button>
                <a href="../../dashboard.php?lang=<?= $lang ?>&modal=addResourcesMenu" class="btn btn-secondary">
                    <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
                    <?= $lang == 'ar' ? 'إلغاء / العودة' : 'Annuler / Retour' ?>
                </a>
            </div>

        </form>
    </div>
</div>

<script>
function validateFile(file) {
    const maxSize = 10 * 1024 * 1024; // 10 MB
    const allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    const langCode = '<?= $lang ?>';
    const fileExt = file.name.split('.').pop().toLowerCase();
    
    if (!allowedExts.includes(fileExt)) {
        alert(langCode === 'ar' 
            ? 'نوع الملف غير مدعوم (PDF, DOC, DOCX, JPG, PNG فقط)'
            : 'Type de fichier non supporté');
        return false;
    }
    if (file.size > maxSize) {
        alert(langCode === 'ar' 
            ? 'حجم الملف كبير جداً (الحد الأقصى 10 MB)'
            : 'Fichier trop volumineux (max 10 MB)');
        return false;
    }
    return true;
}

function updateFileName(input) {
    const div = document.getElementById('fileName');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (!validateFile(file)) {
            input.value = '';
            div.innerHTML = '';
            return;
        }
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        div.innerHTML = '<span style="color:var(--primary-dark);font-weight:600;">✓ ' + 
                       file.name + ' (' + sizeMB + ' MB)</span>';
    } else {
        div.innerHTML = '';
    }
}

// Drag & drop on the wrapper zone
const fileInput   = document.getElementById('periodical_file');
const fileWrapper = document.querySelector('.file-wrapper');

['dragenter','dragover','dragleave','drop'].forEach(ev =>
    fileWrapper.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); }));

['dragenter','dragover'].forEach(ev =>
    fileWrapper.addEventListener(ev, () => fileWrapper.style.background = 'rgba(8,145,178,0.18)'));
['dragleave','drop'].forEach(ev =>
    fileWrapper.addEventListener(ev, () => fileWrapper.style.background = ''));

fileWrapper.addEventListener('drop', e => {
    const files = e.dataTransfer.files;
    if (files.length > 0 && validateFile(files[0])) {
        const dt = new DataTransfer();
        dt.items.add(files[0]);
        fileInput.files = dt.files;
        updateFileName(fileInput);
    }
});

// Fade-in animation
document.addEventListener('DOMContentLoaded', () => {
    const card = document.querySelector('.form-card');
    card.style.opacity = '0';
    card.style.transform = 'translateY(18px)';
    setTimeout(() => {
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 100);
});
</script>
</body>
</html>
