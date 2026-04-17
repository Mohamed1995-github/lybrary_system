<?php
session_start();
require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__, 3) . '/includes/auth.php';

if (!isset($_SESSION['uid'])) {
    header('Location: ../../login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'ar';

$t = [
    'ar' => [
        'title'               => 'إضافة بحث جديد',
        'subtitle'            => 'تسجيل بحث أكاديمي جديد في قاعدة بيانات المكتبة',
        'specialization'      => 'التخصص',
        'academic_year'       => 'السنة الدراسية',
        'research_title'      => 'عنوان البحث',
        'researcher'          => 'الباحث',
        'institution'         => 'المؤسسة',
        'research_nature'     => 'طبيعة البحث',
        'research_report'     => 'بحث تقرير',
        'research_master'     => 'بحث ماستر',
        'research_phd'        => 'بحث دكتورا',
        'inventory_number'    => 'رقم الجرد',
        'box_number'          => 'رقم العلبة',
        'cabinet_number'      => 'رقم الخزانة',
        'shelf_number'        => 'رقم الرف',
        'drawer_number'       => 'رقم الدرج',
        'registration_date'   => 'تاريخ التسجيل',
        'modification_number' => 'رقم التعديل',
        'pdf'                 => 'ملف PDF (اختياري)',
        'admin'               => 'الإدارة العامة',
        'law'                 => 'القانون',
        'econ'                => 'الاقتصاد',
        'diploma'             => 'الدبلوماسية',
        'media'               => 'الإعلام والاتصالات',
        'submit'              => 'حفظ البحث',
        'back'                => 'إلغاء / العودة',
        'required'            => 'هذا الحقل مطلوب',
        'invalid_pdf'         => 'يجب أن يكون الملف بصيغة PDF',
        'file_too_large'      => 'حجم الملف كبير جداً (الحد الأقصى 10 MB)',
        'upload_error'        => 'خطأ في رفع الملف',
        'sec_basic'           => 'المعلومات الأساسية',
        'sec_location'        => 'الموقع والتخزين',
        'sec_dates'           => 'التواريخ',
        'sec_file'            => 'الملف الرقمي',
        'choose_spec'         => 'اختر التخصص',
        'choose_nature'       => 'اختر طبيعة البحث',
        'click_pdf'           => 'اضغط لاختيار ملف PDF أو اسحبه هنا',
        'dashboard_back'      => 'العودة للوحة التحكم',
    ],
    'fr' => [
        'title'               => 'Ajouter une Recherche',
        'subtitle'            => 'Enregistrer une nouvelle recherche académique dans la bibliothèque',
        'specialization'      => 'Spécialité',
        'academic_year'       => 'Année Académique',
        'research_title'      => 'Titre de la recherche',
        'researcher'          => 'Chercheur',
        'institution'         => 'Institution',
        'research_nature'     => 'Nature de la recherche',
        'research_report'     => 'Rapport de recherche',
        'research_master'     => 'Recherche Master',
        'research_phd'        => 'Recherche Doctorat',
        'inventory_number'    => "Numéro d'inventaire",
        'box_number'          => 'Numéro de boîte',
        'cabinet_number'      => "Numéro d'armoire",
        'shelf_number'        => "Numéro d'étagère",
        'drawer_number'       => 'Numéro de tiroir',
        'registration_date'   => "Date d'enregistrement",
        'modification_number' => 'Numéro de modification',
        'pdf'                 => 'Fichier PDF (optionnel)',
        'admin'               => 'Administration Publique',
        'law'                 => 'Droit',
        'econ'                => 'Économie',
        'diploma'             => 'Diplomatie',
        'media'               => 'Médias et Communications',
        'submit'              => 'Enregistrer la recherche',
        'back'                => 'Annuler / Retour',
        'required'            => 'Ce champ est obligatoire',
        'invalid_pdf'         => 'Le fichier doit être au format PDF',
        'file_too_large'      => 'Le fichier est trop volumineux (max 10 MB)',
        'upload_error'        => "Erreur lors de l'upload",
        'sec_basic'           => 'Informations principales',
        'sec_location'        => 'Localisation et stockage',
        'sec_dates'           => 'Dates',
        'sec_file'            => 'Fichier numérique',
        'choose_spec'         => 'Sélectionner la spécialité',
        'choose_nature'       => 'Sélectionner la nature',
        'click_pdf'           => 'Cliquez pour sélectionner un PDF ou déposez-le ici',
        'dashboard_back'      => 'Retour au tableau de bord',
    ],
][$lang];

$error    = '';
$uploadDir = dirname(__DIR__, 2) . '/uploads/research';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialization      = trim($_POST['specialization']      ?? '');
    $academic_year       = trim($_POST['academic_year']       ?? '');
    $title               = trim($_POST['research_title']      ?? '');
    $researcher          = trim($_POST['researcher']          ?? '');
    $institution         = trim($_POST['institution']         ?? '');
    $research_nature     = trim($_POST['research_nature']     ?? '');
    $inventory_number    = trim($_POST['inventory_number']    ?? '');
    $box_number          = trim($_POST['box_number']          ?? '');
    $cabinet_number      = trim($_POST['cabinet_number']      ?? '');
    $shelf_number        = trim($_POST['shelf_number']        ?? '');
    $drawer_number       = trim($_POST['drawer_number']       ?? '');
    $registration_date   = trim($_POST['registration_date']   ?? '');
    $modification_number = trim($_POST['modification_number'] ?? '');
    $pdf_file            = null;

    if (!$specialization || !$title || !$researcher) {
        $error = $t['required'];
    } else {
        if (!empty($_FILES['pdf']['name'])) {
            $file = $_FILES['pdf'];
            $maxSize = 10 * 1024 * 1024;
            if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
                $error = $t['invalid_pdf'];
            } elseif ($file['size'] > $maxSize) {
                $error = $t['file_too_large'];
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $error = $t['upload_error'];
            } else {
                $pdf_file   = 'research_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
                $uploadPath = $uploadDir . '/' . $pdf_file;
                if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $error    = $t['upload_error'];
                    $pdf_file = null;
                }
            }
        }

        if (!$error) {
            try {
                $stmt = $pdo->prepare("INSERT INTO student_research (specialization, academic_year, title, researcher, institution, research_nature, inventory_number, box_number, cabinet_number, shelf_number, drawer_number, registration_date, modification_number, pdf_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$specialization, $academic_year, $title, $researcher, $institution, $research_nature, $inventory_number, $box_number, $cabinet_number, $shelf_number, $drawer_number, $registration_date, $modification_number, $pdf_file]);
                header("Location: listrecherche.php?lang=$lang&msg=success");
                exit;
            } catch (Exception $e) {
                $error = 'Erreur : ' . $e->getMessage();
                if ($pdf_file && file_exists($uploadPath)) unlink($uploadPath);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?> - نظام المكتبة</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:      #4f46e5;
            --primary-dark: #3730a3;
            --primary-bg:   rgba(79,70,229,0.08);
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
        .header-icon     { font-size:3rem; margin-bottom:.75rem; opacity:.9; display:block; }
        .header-title    { font-size:1.875rem; font-weight:700; margin-bottom:.35rem; }
        .header-subtitle { opacity:.88; font-size:.95rem; }

        /* ── Container ── */
        .container { max-width:860px; margin:0 auto; padding:2rem; }

        /* ── Back link ── */
        .back-link {
            display:inline-flex; align-items:center; gap:.5rem;
            color:var(--primary); text-decoration:none; font-weight:600;
            font-size:.875rem; margin-bottom:1.5rem; transition:opacity .2s;
        }
        .back-link:hover { opacity:.75; }

        /* ── Alerts ── */
        .alert { padding:1rem 1.25rem; border-radius:10px; margin-bottom:1.25rem; display:flex; align-items:flex-start; gap:.75rem; font-size:.925rem; }
        .alert i { margin-top:2px; flex-shrink:0; }
        .alert-error { background:var(--error-bg); color:var(--error-fg); border:1px solid var(--error-br); }

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

        .form-input, .form-select {
            width:100%; padding:.7rem .9rem;
            border:1.5px solid var(--gray-200); border-radius:8px;
            font-size:.95rem; color:var(--gray-800); background:var(--white);
            transition:border-color .2s, box-shadow .2s; font-family:inherit;
        }
        .form-input:focus, .form-select:focus {
            outline:none; border-color:var(--primary);
            box-shadow:0 0 0 3px var(--primary-bg);
        }
        .form-input::placeholder { color:var(--gray-300); }

        /* ── File upload ── */
        .file-wrapper {
            position:relative; border:2px dashed var(--primary);
            border-radius:10px; background:var(--primary-bg);
            transition:background .2s; overflow:hidden;
        }
        .file-wrapper:hover { background:rgba(79,70,229,0.14); }
        .file-wrapper input[type="file"] {
            position:absolute; inset:0; width:100%; height:100%;
            opacity:0; cursor:pointer; z-index:2;
        }
        .file-label {
            display:flex; align-items:center; justify-content:center;
            gap:.75rem; padding:1.1rem; color:var(--primary);
            font-weight:500; font-size:.9rem;
            pointer-events:none; position:relative; z-index:1;
        }
        .file-label i { font-size:1.2rem; }
        #fileName { font-size:.85rem; color:var(--primary); font-weight:600; margin-top:.4rem; text-align:center; padding-bottom:.5rem; }

        /* ── Buttons ── */
        .form-actions { display:flex; gap:1rem; justify-content:center; margin-top:2rem; padding-top:1.5rem; border-top:1.5px solid var(--gray-200); flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.75rem 1.75rem; border:none; border-radius:8px; font-weight:600; font-size:.95rem; cursor:pointer; text-decoration:none; transition:all .2s ease; }
        .btn-primary   { background:var(--primary); color:var(--white); }
        .btn-primary:hover { background:var(--primary-dark); transform:translateY(-1px); box-shadow:0 4px 12px rgba(79,70,229,.3); }
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
    <span class="header-icon">📝</span>
    <h1 class="header-title"><?= $t['title'] ?></h1>
    <p class="header-subtitle"><?= $t['subtitle'] ?></p>
</div>

<div class="container">

    <a href="../../dashboard.php?lang=<?= $lang ?>&modal=addResourcesMenu" class="back-link">
        <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
        <?= $t['dashboard_back'] ?>
    </a>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">

            <!-- ── المعلومات الأساسية ── -->
            <div class="section-title"><i class="fas fa-info-circle"></i><?= $t['sec_basic'] ?></div>
            <div class="form-grid">

                <div class="form-group full">
                    <label class="form-label" for="specialization">
                        <i class="fas fa-sitemap"></i>
                        <?= $t['specialization'] ?> <span class="required">*</span>
                    </label>
                    <select id="specialization" name="specialization" class="form-select" required>
                        <option value="">-- <?= $t['choose_spec'] ?> --</option>
                        <option value="administration" <?= ($_POST['specialization']??'')==='administration'?'selected':'' ?>><?= $t['admin'] ?></option>
                        <option value="law"            <?= ($_POST['specialization']??'')==='law'?'selected':'' ?>><?= $t['law'] ?></option>
                        <option value="economics"      <?= ($_POST['specialization']??'')==='economics'?'selected':'' ?>><?= $t['econ'] ?></option>
                        <option value="diplomacy"      <?= ($_POST['specialization']??'')==='diplomacy'?'selected':'' ?>><?= $t['diploma'] ?></option>
                        <option value="media"          <?= ($_POST['specialization']??'')==='media'?'selected':'' ?>><?= $t['media'] ?></option>
                    </select>
                </div>

                <div class="form-group full">
                    <label class="form-label" for="research_title">
                        <i class="fas fa-heading"></i>
                        <?= $t['research_title'] ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="research_title" name="research_title" class="form-input" required
                           placeholder="<?= $lang=='ar' ? 'أدخل عنوان البحث' : 'Entrez le titre de la recherche' ?>"
                           value="<?= htmlspecialchars($_POST['research_title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="researcher">
                        <i class="fas fa-user-graduate"></i>
                        <?= $t['researcher'] ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="researcher" name="researcher" class="form-input" required
                           placeholder="<?= $lang=='ar' ? 'اسم الباحث' : 'Nom du chercheur' ?>"
                           value="<?= htmlspecialchars($_POST['researcher'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="institution">
                        <i class="fas fa-university"></i>
                        <?= $t['institution'] ?>
                    </label>
                    <input type="text" id="institution" name="institution" class="form-input"
                           placeholder="<?= $lang=='ar' ? 'اسم المؤسسة' : 'Nom de l\'institution' ?>"
                           value="<?= htmlspecialchars($_POST['institution'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="academic_year">
                        <i class="fas fa-calendar-alt"></i>
                        <?= $t['academic_year'] ?>
                    </label>
                    <input type="text" id="academic_year" name="academic_year" class="form-input"
                           placeholder="<?= $lang=='ar' ? 'مثال: 2023/2024' : 'Ex: 2023/2024' ?>"
                           value="<?= htmlspecialchars($_POST['academic_year'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="research_nature">
                        <i class="fas fa-flask"></i>
                        <?= $t['research_nature'] ?>
                    </label>
                    <select id="research_nature" name="research_nature" class="form-select">
                        <option value="">-- <?= $t['choose_nature'] ?> --</option>
                        <option value="report" <?= ($_POST['research_nature']??'')==='report'?'selected':'' ?>><?= $t['research_report'] ?></option>
                        <option value="master" <?= ($_POST['research_nature']??'')==='master'?'selected':'' ?>><?= $t['research_master'] ?></option>
                        <option value="phd"    <?= ($_POST['research_nature']??'')==='phd'?'selected':'' ?>><?= $t['research_phd'] ?></option>
                    </select>
                </div>

            </div>

            <!-- ── الموقع والتخزين ── -->
            <div class="section-title"><i class="fas fa-archive"></i><?= $t['sec_location'] ?></div>
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="inventory_number">
                        <i class="fas fa-hashtag"></i>
                        <?= $t['inventory_number'] ?>
                    </label>
                    <input type="text" id="inventory_number" name="inventory_number" class="form-input"
                           placeholder="<?= $lang=='ar' ? 'رقم الجرد' : 'N° inventaire' ?>"
                           value="<?= htmlspecialchars($_POST['inventory_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="box_number">
                        <i class="fas fa-box"></i>
                        <?= $t['box_number'] ?>
                    </label>
                    <input type="text" id="box_number" name="box_number" class="form-input"
                           placeholder="<?= $lang=='ar' ? 'رقم العلبة' : 'N° boîte' ?>"
                           value="<?= htmlspecialchars($_POST['box_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="cabinet_number">
                        <i class="fas fa-archive"></i>
                        <?= $t['cabinet_number'] ?>
                    </label>
                    <input type="text" id="cabinet_number" name="cabinet_number" class="form-input"
                           placeholder="<?= $lang=='ar' ? 'رقم الخزانة' : 'N° armoire' ?>"
                           value="<?= htmlspecialchars($_POST['cabinet_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="shelf_number">
                        <i class="fas fa-layer-group"></i>
                        <?= $t['shelf_number'] ?>
                    </label>
                    <input type="text" id="shelf_number" name="shelf_number" class="form-input"
                           placeholder="<?= $lang=='ar' ? 'رقم الرف' : 'N° étagère' ?>"
                           value="<?= htmlspecialchars($_POST['shelf_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="drawer_number">
                        <i class="fas fa-box-open"></i>
                        <?= $t['drawer_number'] ?>
                    </label>
                    <input type="text" id="drawer_number" name="drawer_number" class="form-input"
                           placeholder="<?= $lang=='ar' ? 'رقم الدرج' : 'N° tiroir' ?>"
                           value="<?= htmlspecialchars($_POST['drawer_number'] ?? '') ?>">
                </div>

            </div>

            <!-- ── التواريخ ── -->
            <div class="section-title"><i class="fas fa-calendar"></i><?= $t['sec_dates'] ?></div>
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="registration_date">
                        <i class="fas fa-calendar-plus"></i>
                        <?= $t['registration_date'] ?>
                    </label>
                    <input type="date" id="registration_date" name="registration_date" class="form-input"
                           value="<?= htmlspecialchars($_POST['registration_date'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="modification_number">
                        <i class="fas fa-edit"></i>
                        <?= $t['modification_number'] ?>
                    </label>
                    <input type="text" id="modification_number" name="modification_number" class="form-input"
                           placeholder="<?= $lang=='ar' ? 'رقم التعديل' : 'N° modification' ?>"
                           value="<?= htmlspecialchars($_POST['modification_number'] ?? '') ?>">
                </div>

            </div>

            <!-- ── الملف الرقمي ── -->
            <div class="section-title"><i class="fas fa-upload"></i><?= $t['sec_file'] ?></div>
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-file-pdf"></i>
                    <?= $t['pdf'] ?>
                </label>
                <div class="file-wrapper">
                    <input type="file" id="pdf" name="pdf" accept=".pdf" onchange="updateFileName(this)">
                    <div class="file-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <?= $t['click_pdf'] ?>
                    </div>
                    <div id="fileName"></div>
                </div>
                <small style="color:var(--gray-500);font-size:.8rem;margin-top:.35rem;">
                    <?= $lang=='ar' ? 'PDF فقط — الحد الأقصى 10 MB' : 'PDF uniquement — max 10 MB' ?>
                </small>
            </div>

            <!-- ── الأزرار ── -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $t['submit'] ?>
                </button>
                <a href="../../dashboard.php?lang=<?= $lang ?>&modal=addResourcesMenu" class="btn btn-secondary">
                    <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
                    <?= $t['back'] ?>
                </a>
            </div>

        </form>
    </div>
</div>

<script>
function updateFileName(input) {
    const div = document.getElementById('fileName');
    div.innerHTML = input.files && input.files[0]
        ? '<span style="color:var(--primary);font-weight:600;">✓ ' + input.files[0].name + '</span>'
        : '';
}

// Drag & drop
const fileInput   = document.getElementById('pdf');
const fileWrapper = fileInput.closest('.file-wrapper');

['dragenter','dragover','dragleave','drop'].forEach(ev =>
    fileWrapper.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); }));

['dragenter','dragover'].forEach(ev =>
    fileWrapper.addEventListener(ev, () => fileWrapper.style.background = 'rgba(79,70,229,0.18)'));
['dragleave','drop'].forEach(ev =>
    fileWrapper.addEventListener(ev, () => fileWrapper.style.background = ''));

fileWrapper.addEventListener('drop', e => {
    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].name.toLowerCase().endsWith('.pdf')) {
        const dt = new DataTransfer();
        dt.items.add(files[0]);
        fileInput.files = dt.files;
        updateFileName(fileInput);
    }
});

// Fade-in
document.addEventListener('DOMContentLoaded', () => {
    const card = document.querySelector('.form-card');
    card.style.opacity = '0';
    card.style.transform = 'translateY(18px)';
    setTimeout(() => {
        card.style.transition = 'opacity .5s ease, transform .5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 100);
});
</script>
</body>
</html>
