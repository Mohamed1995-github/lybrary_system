<?php
require_once '../../../config/db.php';
require_once '../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $newspaper_name = $_POST['newspaper_name'] ?? '';
        $issue_from = $_POST['issue_from'] ?? '';
        $issue_to = $_POST['issue_to'] ?? '';
        $newspaper_date = $_POST['newspaper_date'] ?? '';
        $missing_issues = $_POST['missing_issues'] ?? '';
        $box_number = $_POST['box_number'] ?? '';
        $cabinet_number = $_POST['cabinet_number'] ?? '';
        $shelf_number = $_POST['shelf_number'] ?? '';
        $drawer_number = $_POST['drawer_number'] ?? '';
        $registration_date = $_POST['registration_date'] ?? '';
        $modification_date = $_POST['modification_date'] ?? '';
    
    // Validation
        if (empty($newspaper_name)) {
            $error = $lang == 'ar' ? 'اسم الجريدة مطلوب' : 'Le nom du journal est requis';
        } elseif (empty($issue_from) || empty($issue_to)) {
            $error = $lang == 'ar' ? 'الأعداد مطلوبة' : 'Les numéros sont requis';
        } else {
            // Vérifier si cette édition existe déjà
            $check_stmt = $pdo->prepare("SELECT id FROM items WHERE title = ? AND lang = ? AND type = 'newspaper' AND issue_from = ? AND issue_to = ?");
            $check_stmt->execute([$newspaper_name, $lang, $issue_from, $issue_to]);
            
            if ($check_stmt->fetch()) {
                $error = $lang == 'ar' ? 'هذه الإصدارة موجودة بالفعل' : 'Cette édition existe déjà';
            } else {
                // Insérer le nouveau journal avec newspaper_type='official'
                $stmt = $pdo->prepare("
                    INSERT INTO items (lang, type, title, issue_from, issue_to, newspaper_date, missing_issues, box_number, cabinet_number, shelf_number, drawer_number, registration_date, modification_date, field, newspaper_type, created_at) 
                    VALUES (?, 'newspaper', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'General', 'official', NOW())
                ");
                $stmt->execute([
                    $lang, 
                    $newspaper_name, 
                    $issue_from,
                    $issue_to,
                    $newspaper_date,
                    $missing_issues,
                    $box_number,
                    $cabinet_number,
                    $shelf_number,
                    $drawer_number,
                    $registration_date,
                    $modification_date
                ]);
                
                $success = $lang == 'ar' ? 'تم إضافة الجريدة بنجاح' : 'Journal ajouté avec succès';
            }
        }
        } catch (PDOException $e) {
        $error = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
    }
}

// Options des journaux
$newspaper_options = [
    'ar' => [
        'shaab' => 'الجريدة الرسمية '
    ],
    'fr' => [
        'shaab' => 'Journal officiel'
    ]
];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'إضافة جريدة' : 'Ajouter un journal' ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
    text-align: center;
}
        .header h1 {
            margin: 0;
    font-size: 1.5rem;
        }
        .form-container {
            padding: 2rem;
        }
.form-group {
    margin-bottom: 1.5rem;
}
        .form-group label {
    display: block;
    margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
}
        .form-group input,
        .form-group select {
    width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
    font-size: 1rem;
            transition: border-color 0.2s;
}
        .form-group textarea {
    width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
    font-size: 1rem;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            transition: border-color 0.2s;
}
        .form-group textarea:focus {
    outline: none;
            border-color: #667eea;
        }
        .form-group input:focus,
        .form-group select:focus {
    outline: none;
            border-color: #667eea;
        }
        .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
    cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #5a67d8;
        }
        .btn-secondary {
            background: #6b7280;
            margin-right: 1rem;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .alert {
        padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #667eea;
            text-decoration: none;
            margin-bottom: 1rem;
        }
        .back-link:hover {
            color: #5a67d8;
        }
        .newspaper-info {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .newspaper-info h3 {
            margin: 0;
            color: #374151;
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
        <div class="header">
            <h1>
                <i class="fas fa-newspaper"></i>
                <?= $lang == 'ar' ? 'إضافة جريدة' : 'Ajouter un journal' ?>
            </h1>
        </div>
        
        <div class="form-container">
            <a href="../../dashboard.php?lang=<?=$lang?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
            </a>
            
            <div class="newspaper-info">
                <h3>
                    <i class="fas fa-info-circle"></i>
                    <?= $lang == 'ar' ? 'إضافة مجموعة أعداد من الجريدة' : 'Ajouter une série de numéros du journal' ?>
                </h3>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                    <?= $success ?>
            </div>
        <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="newspaper_name">
                        <i class="fas fa-newspaper"></i>
                        <?= $lang == 'ar' ? 'اسم الجريدة' : 'Nom du journal' ?> *
                    </label>
                    <select id="newspaper_name" name="newspaper_name" required>
                        <option value=""><?= $lang == 'ar' ? 'اختر الجريدة' : 'Choisissez le journal' ?></option>
                        <option value="<?= $newspaper_options[$lang]['shaab'] ?>" <?= ($_POST['newspaper_name'] ?? '') == $newspaper_options[$lang]['shaab'] ? 'selected' : '' ?>>
                            <?= $newspaper_options[$lang]['shaab'] ?>
                        </option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="issue_from">
                            <i class="fas fa-sort-numeric-up"></i>
                            <?= $lang == 'ar' ? 'الأعداد من' : 'Numéros de' ?> *
                        </label>
                        <input type="text" id="issue_from" name="issue_from" required
                               placeholder="<?= $lang == 'ar' ? 'من' : 'De' ?>"
                               value="<?= htmlspecialchars($_POST['issue_from'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="issue_to">
                            <i class="fas fa-sort-numeric-up"></i>
                            <?= $lang == 'ar' ? 'إلى' : 'À' ?> *
                        </label>
                        <input type="text" id="issue_to" name="issue_to" required
                               placeholder="<?= $lang == 'ar' ? 'إلى' : 'À' ?>"
                               value="<?= htmlspecialchars($_POST['issue_to'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="newspaper_date">
                        <i class="fas fa-calendar"></i>
                        <?= $lang == 'ar' ? 'التاريخ' : 'Date' ?>
                    </label>
                    <input type="date" id="newspaper_date" name="newspaper_date"
                           value="<?= htmlspecialchars($_POST['newspaper_date'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="missing_issues">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= $lang == 'ar' ? 'الأعداد الناقصة' : 'Numéros manquants' ?>
                    </label>
                    <textarea id="missing_issues" name="missing_issues" rows="3"
                              placeholder="<?= $lang == 'ar' ? 'أدخل الأعداد الناقصة' : 'Entrez les numéros manquants' ?>"><?= htmlspecialchars($_POST['missing_issues'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="box_number">
                            <i class="fas fa-box"></i>
                            <?= $lang == 'ar' ? 'رقم العلبة' : 'Numéro de boîte' ?>
                        </label>
                        <input type="text" id="box_number" name="box_number"
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم العلبة' : 'Entrez le numéro de boîte' ?>"
                               value="<?= htmlspecialchars($_POST['box_number'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="cabinet_number">
                            <i class="fas fa-archive"></i>
                            <?= $lang == 'ar' ? 'رقم الخزانة' : 'Numéro d\'armoire' ?>
                        </label>
                        <input type="text" id="cabinet_number" name="cabinet_number"
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم الخزانة' : 'Entrez le numéro d\'armoire' ?>"
                               value="<?= htmlspecialchars($_POST['cabinet_number'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="shelf_number">
                            <i class="fas fa-layer-group"></i>
                            <?= $lang == 'ar' ? 'رقم الرف' : 'Numéro d\'étagère' ?>
                        </label>
                        <input type="text" id="shelf_number" name="shelf_number"
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم الرف' : 'Entrez le numéro d\'étagère' ?>"
                               value="<?= htmlspecialchars($_POST['shelf_number'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="drawer_number">
                        <i class="fas fa-drawer"></i>
                        <?= $lang == 'ar' ? 'رقم الدرج' : 'Numéro de tiroir' ?>
                    </label>
                    <input type="text" id="drawer_number" name="drawer_number"
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم الدرج' : 'Entrez le numéro de tiroir' ?>"
                           value="<?= htmlspecialchars($_POST['drawer_number'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="registration_date">
                            <i class="fas fa-calendar-plus"></i>
                            <?= $lang == 'ar' ? 'تاريخ التسجيل' : 'Date d\'enregistrement' ?>
                        </label>
                        <input type="date" id="registration_date" name="registration_date"
                               value="<?= htmlspecialchars($_POST['registration_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="modification_date">
                            <i class="fas fa-calendar-check"></i>
                            <?= $lang == 'ar' ? 'تاريخ التعديل' : 'Date de modification' ?>
                        </label>
                        <input type="date" id="modification_date" name="modification_date"
                               value="<?= htmlspecialchars($_POST['modification_date'] ?? '') ?>">
                    </div>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                    <i class="fas fa-times"></i>
                    <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                    </button>
                    <button type="submit" class="btn">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة الجريدة' : 'Ajouter le journal' ?>
                    </button>
            </div>
        </form>
    </div>
</div>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html> 