<?php
session_start();
require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__, 3) . '/includes/auth.php';

if (!isset($_SESSION['uid'])) {
    header('Location: ../../login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'ar';
$msg  = $_GET['msg'] ?? '';

$translations = [
  'ar' => [
    'title' => 'إضافة بحث جديد',
    'specialization' => 'التخصص',
    'academic_year' => 'السنة الدراسية',
    'research_title' => 'عنوان البحث',
    'researcher' => 'الباحث',
    'institution' => 'المؤسسة',
    'research_nature' => 'طبيعة البحث',
    'research_report' => 'بحث تقرير',
    'research_master' => 'بحث ماستر',
    'research_phd' => 'بحث دكتورا',
    'inventory_number' => 'رقم الجرد',
    'box_number' => 'رقم العلبة',
    'cabinet_number' => 'رقم الخزانة',
    'shelf_number' => 'رقم الرف',
    'drawer_number' => 'رقم الدرج',
    'registration_date' => 'تاريخ التسجيل',
    'modification_number' => 'رقم التعديل',
    'pdf' => 'ملف PDF (اختياري)',
    'admin' => 'إدارة عامة',
    'law' => 'القانون',
    'econ' => 'الاقتصاد',
    'diploma' => 'دبلوماسية',
    'media' => 'إعلام واتصالات',
    'submit' => 'إضافة',
    'back' => 'عودة',
    'required' => 'هذا الحقل مطلوب',
    'success' => 'تمت الإضافة بنجاح ✅',
    'invalid_pdf' => 'يجب أن يكون الملف بصيغة PDF',
    'file_too_large' => 'حجم الملف كبير جداً (الحد الأقصى 10 MB)',
    'upload_error' => 'خطأ في رفع الملف'
  ],
  'fr' => [
    'title' => 'Ajouter une Recherche',
    'specialization' => 'Spécialité',
    'academic_year' => 'Année Académique',
    'research_title' => 'Titre de la recherche',
    'researcher' => 'Chercheur',
    'institution' => 'Institution',
    'research_nature' => 'Nature de la recherche',
    'research_report' => 'Rapport de recherche',
    'research_master' => 'Recherche Master',
    'research_phd' => 'Recherche Doctorat',
    'inventory_number' => 'Numéro d\'inventaire',
    'box_number' => 'Numéro de boîte',
    'cabinet_number' => 'Numéro d\'armoire',
    'shelf_number' => 'Numéro d\'étagère',
    'drawer_number' => 'Numéro de tiroir',
    'registration_date' => 'Date d\'enregistrement',
    'modification_number' => 'Numéro de modification',
    'pdf' => 'Fichier PDF (optionnel)',
    'admin' => 'Administration',
    'law' => 'Droit',
    'econ' => 'Économie',
    'diploma' => 'Diplomatie',
    'media' => 'Médias et Communications',
    'submit' => 'Ajouter',
    'back' => 'Retour',
    'required' => 'Ce champ est obligatoire',
    'success' => 'Ajout réussi ✅',
    'invalid_pdf' => 'Le fichier doit être au format PDF',
    'file_too_large' => 'Le fichier est trop volumineux (max 10 MB)',
    'upload_error' => 'Erreur lors de l\'upload du fichier'
  ]
];
$t = $translations[$lang];

$error = '';
$uploadDir = dirname(__DIR__, 2) . '/uploads/research';

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialization = trim($_POST['specialization'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $title = trim($_POST['research_title'] ?? '');
    $researcher = trim($_POST['researcher'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $research_nature = trim($_POST['research_nature'] ?? '');
    $inventory_number = trim($_POST['inventory_number'] ?? '');
    $box_number = trim($_POST['box_number'] ?? '');
    $cabinet_number = trim($_POST['cabinet_number'] ?? '');
    $shelf_number = trim($_POST['shelf_number'] ?? '');
    $drawer_number = trim($_POST['drawer_number'] ?? '');
    $registration_date = trim($_POST['registration_date'] ?? '');
    $modification_number = trim($_POST['modification_number'] ?? '');
    $pdf_file = null;

    if (!$specialization || !$title || !$researcher) {
        $error = $t['required'];
    } else {
        // Handle PDF upload
        if (!empty($_FILES['pdf']['name'])) {
            $file = $_FILES['pdf'];
            $maxSize = 10 * 1024 * 1024; // 10 MB
            
            // Validate file type
            if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
                $error = $t['invalid_pdf'];
            } elseif ($file['size'] > $maxSize) {
                $error = $t['file_too_large'];
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $error = $t['upload_error'];
            } else {
                // Generate unique filename
                $pdf_file = 'research_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
                $uploadPath = $uploadDir . '/' . $pdf_file;
                
                if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $error = $t['upload_error'];
                    $pdf_file = null;
                }
            }
        }

        if (!$error) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO student_research (specialization, academic_year, title, researcher, institution, research_nature, inventory_number, box_number, cabinet_number, shelf_number, drawer_number, registration_date, modification_number, pdf_file)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$specialization, $academic_year, $title, $researcher, $institution, $research_nature, $inventory_number, $box_number, $cabinet_number, $shelf_number, $drawer_number, $registration_date, $modification_number, $pdf_file]);
                header("Location: listrecherche.php?lang=$lang&msg=success");
                exit;
            } catch (Exception $e) {
                $error = 'Erreur : ' . $e->getMessage();
                // Delete uploaded file if database insertion fails
                if ($pdf_file && file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
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
  <title><?= $t['title'] ?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 2rem;
    }
    .container {
      max-width: 700px;
      margin: 0 auto;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    h2 {
      color: #1f2937;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #374151;
    }
    input, textarea, select {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-family: inherit;
      font-size: 1rem;
      transition: border-color 0.2s;
    }
    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    textarea {
      resize: vertical;
      min-height: 100px;
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
      border: 2px dashed #2563eb;
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;
      background: #f0f9ff;
    }
    .file-input-label:hover {
      background: #e0f2fe;
      border-color: #1d4ed8;
    }
    .file-name {
      color: #059669;
      font-weight: 600;
      margin-top: 0.5rem;
    }
    .actions {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
    }
    .btn {
      flex: 1;
      padding: 0.75rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.2s;
    }
    .btn-primary {
      background: #2563eb;
      color: white;
    }
    .btn-primary:hover {
      background: #1d4ed8;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(37,99,235,0.3);
    }
    .btn-secondary {
      background: #6b7280;
      color: white;
    }
    .btn-secondary:hover {
      background: #4b5563;
    }
    .alert {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      border-left: 4px solid;
    }
    .alert-error {
      background: #fee2e2;
      color: #991b1b;
      border-color: #dc2626;
    }
    .required { color: #dc2626; }
    
    @media (max-width: 600px) {
      .container { padding: 1.5rem; }
      .actions { flex-direction: column; }
      .btn { width: 100%; }
    }
  </style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/professional-theme.css">
</head>
<body>
<div class="container">
  <h2>📝 <?= $t['title'] ?></h2>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label for="specialization"><?= $t['specialization'] ?> <span class="required">*</span></label>
      <select id="specialization" name="specialization" required>
        <option value="">-- <?= $lang === 'ar' ? 'اختر التخصص' : 'Sélectionner' ?> --</option>
        <option value="administration"><?= $t['admin'] ?></option>
        <option value="law"><?= $t['law'] ?></option>
        <option value="economics"><?= $t['econ'] ?></option>
        <option value="diplomacy"><?= $t['diploma'] ?></option>
        <option value="media"><?= $t['media'] ?></option>
      </select>
    </div>

    <div class="form-group">
      <label for="academic_year"><?= $t['academic_year'] ?></label>
      <input type="text" id="academic_year" name="academic_year" value="<?= htmlspecialchars($_POST['academic_year'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="research_title"><?= $t['research_title'] ?> <span class="required">*</span></label>
      <input type="text" id="research_title" name="research_title" required value="<?= htmlspecialchars($_POST['research_title'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="researcher"><?= $t['researcher'] ?> <span class="required">*</span></label>
      <input type="text" id="researcher" name="researcher" required value="<?= htmlspecialchars($_POST['researcher'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="institution"><?= $t['institution'] ?></label>
      <input type="text" id="institution" name="institution" value="<?= htmlspecialchars($_POST['institution'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="research_nature"><?= $t['research_nature'] ?></label>
      <select id="research_nature" name="research_nature">
        <option value="">-- <?= $lang === 'ar' ? 'اختر طبيعة البحث' : 'Sélectionner la nature' ?> --</option>
        <option value="report"><?= $t['research_report'] ?></option>
        <option value="master"><?= $t['research_master'] ?></option>
        <option value="phd"><?= $t['research_phd'] ?></option>
      </select>
    </div>

    <div class="form-group">
      <label for="inventory_number"><?= $t['inventory_number'] ?></label>
      <input type="text" id="inventory_number" name="inventory_number" value="<?= htmlspecialchars($_POST['inventory_number'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="box_number"><?= $t['box_number'] ?></label>
      <input type="text" id="box_number" name="box_number" value="<?= htmlspecialchars($_POST['box_number'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="cabinet_number"><?= $t['cabinet_number'] ?></label>
      <input type="text" id="cabinet_number" name="cabinet_number" value="<?= htmlspecialchars($_POST['cabinet_number'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="shelf_number"><?= $t['shelf_number'] ?></label>
      <input type="text" id="shelf_number" name="shelf_number" value="<?= htmlspecialchars($_POST['shelf_number'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="drawer_number"><?= $t['drawer_number'] ?></label>
      <input type="text" id="drawer_number" name="drawer_number" value="<?= htmlspecialchars($_POST['drawer_number'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="registration_date"><?= $t['registration_date'] ?></label>
      <input type="date" id="registration_date" name="registration_date" value="<?= htmlspecialchars($_POST['registration_date'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="modification_number"><?= $t['modification_number'] ?></label>
      <input type="text" id="modification_number" name="modification_number" value="<?= htmlspecialchars($_POST['modification_number'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="pdf"><?= $t['pdf'] ?></label>
      <div class="file-input-wrapper">
        <input type="file" id="pdf" name="pdf" accept=".pdf" onchange="updateFileName(this)">
        <label for="pdf" class="file-input-label">
          <div>📄 <?= $lang == 'ar' ? 'اضغط لاختيار ملف PDF أو اسحب الملف هنا' : 'Cliquez pour sélectionner un PDF' ?></div>
          <div id="fileName"></div>
        </label>
      </div>
    </div>

    <div class="actions">
      <button type="submit" class="btn btn-primary"><?= $t['submit'] ?></button>
      <a href="listrecherche.php?lang=<?= $lang ?>" class="btn btn-secondary"><?= $t['back'] ?></a>
    </div>
  </form>
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
const fileInput = document.getElementById('pdf');
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
    fileLabel.style.background = '#e0f2fe';
    fileLabel.style.borderColor = '#1d4ed8';
  }, false);
});

['dragleave', 'drop'].forEach(eventName => {
  fileLabel.addEventListener(eventName, () => {
    fileLabel.style.background = '#f0f9ff';
    fileLabel.style.borderColor = '#2563eb';
  }, false);
});

fileLabel.addEventListener('drop', (e) => {
  const dt = e.dataTransfer;
  const files = dt.files;
  fileInput.files = files;
  updateFileName(fileInput);
}, false);
</script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>
