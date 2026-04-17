<?php
require_once '../../../config/db.php';
require_once '../../../includes/auth.php';

if (!isset($_SESSION['uid'])) {
    header('Location: ../../../public/login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'ar';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $newspaper_name    = $_POST['newspaper_name']    ?? '';
        $issue_from        = $_POST['issue_from']        ?? '';
        $issue_to          = $_POST['issue_to']          ?? '';
        $newspaper_date    = $_POST['newspaper_date']    ?? '';
        $missing_issues    = $_POST['missing_issues']    ?? '';
        $box_number        = $_POST['box_number']        ?? '';
        $cabinet_number    = $_POST['cabinet_number']    ?? '';
        $shelf_number      = $_POST['shelf_number']      ?? '';
        $drawer_number     = $_POST['drawer_number']     ?? '';
        $registration_date = $_POST['registration_date'] ?? '';
        $modification_date = $_POST['modification_date'] ?? '';

        if (empty($newspaper_name)) {
            $error = $lang == 'ar' ? 'اسم الجريدة مطلوب' : 'Le nom du journal est requis';
        } elseif (empty($issue_from) || empty($issue_to)) {
            $error = $lang == 'ar' ? 'الأعداد مطلوبة' : 'Les numéros sont requis';
        } else {
            $check = $pdo->prepare("SELECT id FROM items WHERE title = ? AND lang = ? AND type = 'newspaper' AND issue_from = ? AND issue_to = ?");
            $check->execute([$newspaper_name, $lang, $issue_from, $issue_to]);
            if ($check->fetch()) {
                $error = $lang == 'ar' ? 'هذه الإصدارة موجودة بالفعل' : 'Cette édition existe déjà';
            } else {
                $stmt = $pdo->prepare("INSERT INTO items (lang, type, title, issue_from, issue_to, newspaper_date, missing_issues, box_number, cabinet_number, shelf_number, drawer_number, registration_date, modification_date, field, newspaper_type, created_at) VALUES (?, 'newspaper', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'General', 'popular', NOW())");
                $stmt->execute([$lang, $newspaper_name, $issue_from, $issue_to, $newspaper_date, $missing_issues, $box_number, $cabinet_number, $shelf_number, $drawer_number, $registration_date, $modification_date]);
                $success = $lang == 'ar' ? 'تم إضافة الجريدة بنجاح!' : 'Journal ajouté avec succès!';
                $_POST = [];
            }
        }
    } catch (PDOException $e) {
        $error = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
    }
}

$newspaper_options = [
    'ar' => ['shaab' => 'جريدة الشعب'],
    'fr' => ['shaab' => 'Journal du Peuple'],
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'إضافة جريدة الشعب' : 'Ajouter Journal du Peuple' ?> - نظام المكتبة</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:      #92400e;
            --primary-dark: #78350f;
            --primary-bg:   rgba(146,64,14,0.08);
            --gray-50:  #f8fafc; --gray-100:#f1f5f9; --gray-200:#e2e8f0;
            --gray-300: #cbd5e1; --gray-500:#64748b; --gray-600:#475569;
            --gray-800: #1e293b; --white:#ffffff;
            --success-bg:rgba(34,197,94,.1); --success-fg:#166534; --success-br:rgba(34,197,94,.2);
            --error-bg:rgba(239,68,68,.1);   --error-fg:#991b1b;   --error-br:rgba(239,68,68,.2);
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
            background:var(--gray-50); color:var(--gray-800); line-height:1.6;
        }

        /* ── Header ── */
        .header {
            background:linear-gradient(135deg,var(--primary),var(--primary-dark));
            color:var(--white); padding:2rem; text-align:center;
        }
        .header-icon    { font-size:3rem; margin-bottom:.75rem; opacity:.9; display:block; }
        .header-title   { font-size:1.875rem; font-weight:700; margin-bottom:.35rem; }
        .header-subtitle{ opacity:.88; font-size:.95rem; }

        /* ── Container ── */
        .container { max-width:820px; margin:0 auto; padding:2rem; }

        /* ── Back link ── */
        .back-link {
            display:inline-flex; align-items:center; gap:.5rem;
            color:var(--primary); text-decoration:none; font-weight:600;
            font-size:.875rem; margin-bottom:1.5rem;
            transition:opacity .2s;
        }
        .back-link:hover { opacity:.75; }

        /* ── Alerts ── */
        .alert { padding:1rem 1.25rem; border-radius:10px; margin-bottom:1.25rem; display:flex; align-items:flex-start; gap:.75rem; font-size:.925rem; }
        .alert i { margin-top:2px; flex-shrink:0; }
        .alert-success { background:var(--success-bg); color:var(--success-fg); border:1px solid var(--success-br); }
        .alert-error   { background:var(--error-bg);   color:var(--error-fg);   border:1px solid var(--error-br); }

        /* ── Card ── */
        .form-card { background:var(--white); border-radius:14px; padding:2rem 2.25rem; box-shadow:0 4px 16px rgba(0,0,0,.08); border:1px solid var(--gray-200); }

        /* ── Section titles ── */
        .section-title {
            font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em;
            color:var(--primary); margin:1.75rem 0 1rem;
            padding-bottom:.5rem; border-bottom:2px solid var(--primary-bg);
            display:flex; align-items:center; gap:.5rem;
        }
        .section-title:first-child { margin-top:0; }

        /* ── Grid ── */
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.1rem; }
        .form-grid .full { grid-column:1/-1; }

        /* ── Form elements ── */
        .form-group { display:flex; flex-direction:column; gap:.35rem; }
        .form-label { font-size:.875rem; font-weight:600; color:var(--gray-800); display:flex; align-items:center; gap:.4rem; }
        .form-label i { color:var(--primary); font-size:.8rem; }
        .required { color:#ef4444; }

        .form-input, .form-select, .form-textarea {
            width:100%; padding:.7rem .9rem;
            border:1.5px solid var(--gray-200); border-radius:8px;
            font-size:.95rem; color:var(--gray-800); background:var(--white);
            transition:border-color .2s, box-shadow .2s;
            font-family:inherit;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline:none; border-color:var(--primary);
            box-shadow:0 0 0 3px var(--primary-bg);
        }
        .form-input::placeholder, .form-textarea::placeholder { color:var(--gray-300); }
        .form-textarea { resize:vertical; min-height:80px; }

        /* ── Buttons ── */
        .form-actions { display:flex; gap:1rem; justify-content:center; margin-top:2rem; padding-top:1.5rem; border-top:1.5px solid var(--gray-200); flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.75rem 1.75rem; border:none; border-radius:8px; font-weight:600; font-size:.95rem; cursor:pointer; text-decoration:none; transition:all .2s ease; }
        .btn-primary   { background:var(--primary); color:var(--white); }
        .btn-primary:hover { background:var(--primary-dark); transform:translateY(-1px); box-shadow:0 4px 12px rgba(146,64,14,.3); }
        .btn-secondary { background:var(--gray-100); color:var(--gray-600); border:1.5px solid var(--gray-200); }
        .btn-secondary:hover { background:var(--gray-200); }

        /* ── Responsive ── */
        @media (max-width:640px) {
            .container { padding:1rem; }
            .form-card  { padding:1.25rem; }
            .form-grid  { grid-template-columns:1fr; }
            .form-grid .full { grid-column:1; }
            .btn { width:100%; justify-content:center; }
        }
    </style>
</head>
<body>

<div class="header">
    <span class="header-icon">📰</span>
    <h1 class="header-title"><?= $lang == 'ar' ? 'إضافة جريدة الشعب' : 'Ajouter Journal du Peuple' ?></h1>
    <p class="header-subtitle"><?= $lang == 'ar' ? 'تسجيل مجموعة أعداد من جريدة الشعب' : 'Enregistrer une série de numéros du Journal du Peuple' ?></p>
</div>

<div class="container">

    <a href="../../dashboard.php?lang=<?= $lang ?>&modal=addResourcesMenu" class="back-link">
        <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
        <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
    </a>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">

            <!-- ── معلومات أساسية ── -->
            <div class="section-title"><i class="fas fa-info-circle"></i><?= $lang == 'ar' ? 'المعلومات الأساسية' : 'Informations principales' ?></div>
            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label" for="newspaper_name">
                        <i class="fas fa-newspaper"></i>
                        <?= $lang == 'ar' ? 'اسم الجريدة' : 'Nom du journal' ?> <span class="required">*</span>
                    </label>
                    <select id="newspaper_name" name="newspaper_name" class="form-select" required>
                        <option value=""><?= $lang == 'ar' ? 'اختر الجريدة' : 'Choisissez le journal' ?></option>
                        <option value="<?= $newspaper_options[$lang]['shaab'] ?>"
                            <?= ($_POST['newspaper_name'] ?? '') == $newspaper_options[$lang]['shaab'] ? 'selected' : '' ?>>
                            <?= $newspaper_options[$lang]['shaab'] ?>
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="issue_from">
                        <i class="fas fa-sort-numeric-up-alt"></i>
                        <?= $lang == 'ar' ? 'الأعداد من' : 'Numéros de' ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="issue_from" name="issue_from" class="form-input" required
                           placeholder="<?= $lang == 'ar' ? 'من العدد' : 'Du numéro' ?>"
                           value="<?= htmlspecialchars($_POST['issue_from'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="issue_to">
                        <i class="fas fa-sort-numeric-down-alt"></i>
                        <?= $lang == 'ar' ? 'إلى' : 'À' ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="issue_to" name="issue_to" class="form-input" required
                           placeholder="<?= $lang == 'ar' ? 'إلى العدد' : 'Au numéro' ?>"
                           value="<?= htmlspecialchars($_POST['issue_to'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="newspaper_date">
                        <i class="fas fa-calendar-alt"></i>
                        <?= $lang == 'ar' ? 'التاريخ' : 'Date' ?>
                    </label>
                    <input type="date" id="newspaper_date" name="newspaper_date" class="form-input"
                           value="<?= htmlspecialchars($_POST['newspaper_date'] ?? '') ?>">
                </div>

                <div class="form-group full">
                    <label class="form-label" for="missing_issues">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= $lang == 'ar' ? 'الأعداد الناقصة' : 'Numéros manquants' ?>
                    </label>
                    <textarea id="missing_issues" name="missing_issues" class="form-textarea"
                              placeholder="<?= $lang == 'ar' ? 'أدخل الأعداد الناقصة إن وجدت' : 'Entrez les numéros manquants si applicable' ?>"><?= htmlspecialchars($_POST['missing_issues'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- ── الموقع ── -->
            <div class="section-title"><i class="fas fa-archive"></i><?= $lang == 'ar' ? 'الموقع والتخزين' : 'Localisation et stockage' ?></div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="box_number">
                        <i class="fas fa-box"></i>
                        <?= $lang == 'ar' ? 'رقم العلبة' : 'Numéro de boîte' ?>
                    </label>
                    <input type="text" id="box_number" name="box_number" class="form-input"
                           placeholder="<?= $lang == 'ar' ? 'رقم العلبة' : 'N° boîte' ?>"
                           value="<?= htmlspecialchars($_POST['box_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="cabinet_number">
                        <i class="fas fa-archive"></i>
                        <?= $lang == 'ar' ? 'رقم الخزانة' : 'Numéro d\'armoire' ?>
                    </label>
                    <input type="text" id="cabinet_number" name="cabinet_number" class="form-input"
                           placeholder="<?= $lang == 'ar' ? 'رقم الخزانة' : 'N° armoire' ?>"
                           value="<?= htmlspecialchars($_POST['cabinet_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="shelf_number">
                        <i class="fas fa-layer-group"></i>
                        <?= $lang == 'ar' ? 'رقم الرف' : 'Numéro d\'étagère' ?>
                    </label>
                    <input type="text" id="shelf_number" name="shelf_number" class="form-input"
                           placeholder="<?= $lang == 'ar' ? 'رقم الرف' : 'N° étagère' ?>"
                           value="<?= htmlspecialchars($_POST['shelf_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="drawer_number">
                        <i class="fas fa-box-open"></i>
                        <?= $lang == 'ar' ? 'رقم الدرج' : 'Numéro de tiroir' ?>
                    </label>
                    <input type="text" id="drawer_number" name="drawer_number" class="form-input"
                           placeholder="<?= $lang == 'ar' ? 'رقم الدرج' : 'N° tiroir' ?>"
                           value="<?= htmlspecialchars($_POST['drawer_number'] ?? '') ?>">
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

            <!-- ── الأزرار ── -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'حفظ الجريدة' : 'Enregistrer' ?>
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
document.addEventListener('DOMContentLoaded', function () {
    const card = document.querySelector('.form-card');
    card.style.opacity = '0';
    card.style.transform = 'translateY(18px)';
    setTimeout(function () {
        card.style.transition = 'opacity .5s ease, transform .5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 100);
});
</script>
</body>
</html>
