<?php
require_once '../../../config/db.php';
require_once '../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';
$category = $_GET['category'] ?? 'general';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $classification = $_POST['classification'] ?? '';
        $title = $_POST['title'] ?? '';
        $issue_number = $_POST['issue_number'] ?? '';
        $field = $_POST['field'] ?? '';
        
        // Validation
        if (empty($title)) {
            $error = $lang == 'ar' ? 'عنوان المجلة مطلوب' : 'Le titre de la revue est requis';
        } else {
            // Vérifier si la revue existe déjà
            $check_stmt = $pdo->prepare("SELECT id FROM items WHERE title = ? AND lang = ? AND type = 'magazine'");
            $check_stmt->execute([$title, $lang]);
            
            if ($check_stmt->fetch()) {
                $error = $lang == 'ar' ? 'هذه المجلة موجودة بالفعل' : 'Cette revue existe déjà';
            } else {
                // Insérer la nouvelle revue générale
                $stmt = $pdo->prepare("
                    INSERT INTO items (lang, type, title, issue_number, field, created_at) 
                    VALUES (?, 'magazine', ?, ?, 'General', NOW())
                ");
                $stmt->execute([
                    $lang, 
                    $title, 
                    $issue_number
                ]);
                
                $success = $lang == 'ar' ? 'تم إضافة المجلة بنجاح' : 'Revue ajoutée avec succès';
            }
        }
    } catch (PDOException $e) {
        $error = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'إضافة مجلة عامة' : 'Ajouter une revue générale' ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
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
                <?= $lang == 'ar' ? 'إضافة مجلة عامة' : 'Ajouter une revue générale' ?>
            </h1>
        </div>
        
        <div class="form-container">
            <a href="../../dashboard.php?lang=<?=$lang?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
            </a>
            
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="classification">
                            <i class="fas fa-hashtag"></i>
                            <?= $lang == 'ar' ? 'رقم التصنيف' : 'Numéro de classification' ?>
                        </label>
                        <input type="text" id="classification" name="classification" 
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم التصنيف' : 'Entrez le numéro de classification' ?>"
                               value="<?= htmlspecialchars($_POST['classification'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="title">
                            <i class="fas fa-newspaper"></i>
                            <?= $lang == 'ar' ? 'عنوان المجلة' : 'Titre de la revue' ?> *
                        </label>
                        <input type="text" id="title" name="title" required
                               placeholder="<?= $lang == 'ar' ? 'أدخل عنوان المجلة' : 'Entrez le titre de la revue' ?>"
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="issue_number">
                            <i class="fas fa-sort-numeric-up"></i>
                            <?= $lang == 'ar' ? 'العدد' : 'Numéro' ?>
                        </label>
                        <input type="text" id="issue_number" name="issue_number"
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم العدد' : 'Entrez le numéro' ?>"
                               value="<?= htmlspecialchars($_POST['issue_number'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="field">
                            <i class="fas fa-tags"></i>
                            <?= $lang == 'ar' ? 'المجال' : 'Domaine' ?>
                        </label>
                        <select id="field" name="field">
                            <option value=""><?= $lang == 'ar' ? 'اختر المجال' : 'Choisissez le domaine' ?></option>
                            <option value="culture" <?= ($_POST['field'] ?? '') == 'culture' ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'ثقافة' : 'Culture' ?>
                            </option>
                            <option value="sports" <?= ($_POST['field'] ?? '') == 'sports' ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'رياضة' : 'Sport' ?>
                            </option>
                            <option value="entertainment" <?= ($_POST['field'] ?? '') == 'entertainment' ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'ترفيه' : 'Divertissement' ?>
                            </option>
                            <option value="technology" <?= ($_POST['field'] ?? '') == 'technology' ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'تكنولوجيا' : 'Technologie' ?>
                            </option>
                            <option value="health" <?= ($_POST['field'] ?? '') == 'health' ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'صحة' : 'Santé' ?>
                            </option>
                            <option value="travel" <?= ($_POST['field'] ?? '') == 'travel' ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'سفر' : 'Voyage' ?>
                            </option>
                            <option value="fashion" <?= ($_POST['field'] ?? '') == 'fashion' ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'أزياء' : 'Mode' ?>
                            </option>
                            <option value="food" <?= ($_POST['field'] ?? '') == 'food' ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'طعام' : 'Cuisine' ?>
                            </option>
                            <option value="other" <?= ($_POST['field'] ?? '') == 'other' ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'أخرى' : 'Autre' ?>
                            </option>
                        </select>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        <i class="fas fa-times"></i>
                        <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                    </button>
                    <button type="submit" class="btn">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة المجلة' : 'Ajouter la revue' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>


