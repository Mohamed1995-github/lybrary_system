<?php
/* Copie directe de add_magazine.php pour accès public */
require_once __DIR__ . '/../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { 
    header('Location: login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';
$pdo = require_once __DIR__ . '/../config/db.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $magazine_number = $_POST['magazine_number'] ?? '';
    $publisher = $_POST['publisher'] ?? '';
    $frequency = $_POST['frequency'] ?? '';
    $copies_total = $_POST['copies_total'] ?? 1;
    $copies_in = $_POST['copies_in'] ?? 1;
    
    if (!empty($title)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO items (title, magazine_number, publisher, frequency, copies_total, copies_in, type, lang) VALUES (?, ?, ?, ?, ?, ?, 'magazine', ?)");
            $stmt->execute([$title, $magazine_number, $publisher, $frequency, $copies_total, $copies_in, $lang]);
            
            $success_message = $lang == 'ar' ? 'تم إضافة المجلة بنجاح' : 'Magazine ajoutée avec succès';
        } catch (PDOException $e) {
            $error_message = $lang == 'ar' ? 'خطأ في إضافة المجلة' : 'Erreur lors de l\'ajout de la revue';
        }
    } else {
        $error_message = $lang == 'ar' ? 'يرجى إدخال عنوان المجلة' : 'Veuillez saisir le titre de la revue';
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'إضافة مجلة جديدة' : 'Ajouter une nouvelle revue' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f8fafc;
            color: #1e293b;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header h1 {
            font-size: 2rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #64748b;
        }
        .btn-secondary:hover {
            background: #475569;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= $lang == 'ar' ? 'إضافة مجلة جديدة' : 'Ajouter une nouvelle revue' ?></h1>
            <p><?= $lang == 'ar' ? 'أدخل معلومات المجلة الجديدة' : 'Saisissez les informations de la nouvelle revue' ?></p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success_message ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="title"><?= $lang == 'ar' ? 'عنوان المجلة' : 'Titre de la revue' ?> *</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="magazine_number"><?= $lang == 'ar' ? 'رقم المجلة' : 'Numéro de la revue' ?></label>
                <input type="text" id="magazine_number" name="magazine_number">
            </div>

            <div class="form-group">
                <label for="publisher"><?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?></label>
                <input type="text" id="publisher" name="publisher">
            </div>

            <div class="form-group">
                <label for="frequency"><?= $lang == 'ar' ? 'التكرار' : 'Fréquence' ?></label>
                <select id="frequency" name="frequency">
                    <option value=""><?= $lang == 'ar' ? 'اختر التكرار' : 'Choisir la fréquence' ?></option>
                    <option value="mensuel"><?= $lang == 'ar' ? 'شهري' : 'Mensuel' ?></option>
                    <option value="trimestriel"><?= $lang == 'ar' ? 'فصلي' : 'Trimestriel' ?></option>
                    <option value="semestriel"><?= $lang == 'ar' ? 'نصف سنوي' : 'Semestriel' ?></option>
                    <option value="annuel"><?= $lang == 'ar' ? 'سنوي' : 'Annuel' ?></option>
                </select>
            </div>

            <div class="form-group">
                <label for="copies_total"><?= $lang == 'ar' ? 'عدد النسخ الإجمالي' : 'Nombre total d\'exemplaires' ?></label>
                <input type="number" id="copies_total" name="copies_total" value="1" min="1">
            </div>

            <div class="form-group">
                <label for="copies_in"><?= $lang == 'ar' ? 'عدد النسخ المتاحة' : 'Nombre d\'exemplaires disponibles' ?></label>
                <input type="number" id="copies_in" name="copies_in" value="1" min="0">
            </div>

            <div class="actions">
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'حفظ المجلة' : 'Enregistrer la revue' ?>
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
                </a>
            </div>
        </form>
    </div>
</body>
</html> 