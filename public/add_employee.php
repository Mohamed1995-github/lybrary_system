<?php
/* public/add_employee.php - Version simplifiée */
require_once __DIR__ . '/../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';
$pdo = require_once __DIR__ . '/../config/db.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $number = $_POST['number'] ?? '';
    $function = $_POST['function'] ?? '';
    $access_rights = $_POST['access_rights'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = $lang == 'ar' ? 'الاسم مطلوب' : 'Le nom est requis';
    }
    
    if (empty($number)) {
        $errors[] = $lang == 'ar' ? 'الرقم مطلوب' : 'Le numéro est requis';
    }
    
    if (empty($function)) {
        $errors[] = $lang == 'ar' ? 'الوظيفة مطلوبة' : 'La fonction est requise';
    }
    
    if (empty($password)) {
        $errors[] = $lang == 'ar' ? 'كلمة المرور مطلوبة' : 'Le mot de passe est requis';
    } elseif (strlen($password) < 6) {
        $errors[] = $lang == 'ar' ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل' : 'Le mot de passe doit contenir au moins 6 caractères';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = $lang == 'ar' ? 'كلمة المرور وتأكيدها غير متطابقين' : 'Le mot de passe et sa confirmation ne correspondent pas';
    }
    
    // Vérifier si le numéro existe déjà
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE number = ? AND lang = ?");
            $stmt->execute([$number, $lang]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = $lang == 'ar' ? 'الرقم مستخدم بالفعل' : 'Ce numéro est déjà utilisé';
            }
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
        }
    }
    
    // Sauvegarder si pas d'erreurs
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO employees (lang, name, number, function, access_rights, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$lang, $name, $number, $function, $access_rights, $password_hash]);
            
            $success_message = $lang == 'ar' ? 'تم إضافة الموظف بنجاح' : 'Employé ajouté avec succès';
        } catch (PDOException $e) {
            $error_message = $lang == 'ar' ? 'خطأ في إضافة الموظف' : 'Erreur lors de l\'ajout de l\'employé';
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un nouvel employé' ?></title>
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
        .user-info {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Informations utilisateur -->
        <div class="user-info">
            <i class="fas fa-user"></i>
            <?= $lang == 'ar' ? 'المستخدم: ' : 'Utilisateur: ' ?>
            <?= $_SESSION['username'] ?? $_SESSION['uid'] ?>
            | 
            <?= $lang == 'ar' ? 'الوصول: كامل' : 'Accès: Complet' ?>
        </div>

        <div class="header">
            <h1><?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un nouvel employé' ?></h1>
            <p><?= $lang == 'ar' ? 'أدخل معلومات الموظف الجديد' : 'Saisissez les informations du nouvel employé' ?></p>
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
                <label for="name"><?= $lang == 'ar' ? 'الاسم الكامل' : 'Nom complet' ?> *</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="number"><?= $lang == 'ar' ? 'الرقم' : 'Numéro' ?> *</label>
                <input type="text" id="number" name="number" required>
            </div>

            <div class="form-group">
                <label for="function"><?= $lang == 'ar' ? 'الوظيفة' : 'Fonction' ?> *</label>
                <select id="function" name="function" required>
                    <option value=""><?= $lang == 'ar' ? 'اختر الوظيفة' : 'Choisir la fonction' ?></option>
                    <option value="مدير"><?= $lang == 'ar' ? 'مدير' : 'Directeur' ?></option>
                    <option value="أمين مكتبة"><?= $lang == 'ar' ? 'أمين مكتبة' : 'Bibliothécaire' ?></option>
                    <option value="مساعد"><?= $lang == 'ar' ? 'مساعد' : 'Assistant' ?></option>
                    <option value="استقبال"><?= $lang == 'ar' ? 'استقبال' : 'Accueil' ?></option>
                    <option value="تقني"><?= $lang == 'ar' ? 'تقني' : 'Technicien' ?></option>
                </select>
            </div>

            <div class="form-group">
                <label for="access_rights"><?= $lang == 'ar' ? 'حقوق الوصول' : 'Droits d\'accès' ?></label>
                <textarea id="access_rights" name="access_rights" rows="3" placeholder="<?= $lang == 'ar' ? 'أدخل حقوق الوصول مفصولة بفواصل' : 'Saisissez les droits d\'accès séparés par des virgules' ?>"></textarea>
            </div>

            <div class="form-group">
                <label for="password"><?= $lang == 'ar' ? 'كلمة المرور' : 'Mot de passe' ?> *</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password"><?= $lang == 'ar' ? 'تأكيد كلمة المرور' : 'Confirmer le mot de passe' ?> *</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>

            <div class="actions">
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'حفظ الموظف' : 'Enregistrer l\'employé' ?>
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