<?php
/* modules/administration/add.php */

// Définir le chemin de base de manière robuste
$base_path = dirname(dirname(__DIR__));
require_once $base_path . '/includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../public/login.php'); 
    exit; 
}

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';

$errors = [];
$success = false;

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $number = trim($_POST['number'] ?? '');
    $function = trim($_POST['function'] ?? '');
    $access_rights = trim($_POST['access_rights'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // التحقق من صحة البيانات
    if (empty($name)) {
        $errors[] = $lang == 'ar' ? 'الاسم مطلوب' : 'Le nom est requis';
    }
    
    if (empty($number)) {
        $errors[] = $lang == 'ar' ? 'الرقم مطلوب' : 'Le numéro est requis';
    }
    
    if (empty($function)) {
        $errors[] = $lang == 'ar' ? 'الوظيفة مطلوبة' : 'La fonction est requise';
    }
    
    if (empty($access_rights)) {
        $errors[] = $lang == 'ar' ? 'حقوق الوصول مطلوبة' : 'Les droits d\'accès sont requis';
    }
    
    // التحقق من كلمة المرور
    if (empty($password)) {
        $errors[] = $lang == 'ar' ? 'كلمة المرور مطلوبة' : 'Le mot de passe est requis';
    } elseif (strlen($password) < 6) {
        $errors[] = $lang == 'ar' ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل' : 'Le mot de passe doit contenir au moins 6 caractères';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = $lang == 'ar' ? 'كلمة المرور وتأكيدها غير متطابقين' : 'Le mot de passe et sa confirmation ne correspondent pas';
    }
    
    // التحقق من عدم تكرار الرقم
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
    
    // حفظ البيانات إذا لم تكن هناك أخطاء
    if (empty($errors)) {
        try {
            // تشفير كلمة المرور
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO employees (lang, name, number, function, access_rights, password) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$lang, $name, $number, $function, $access_rights, $password_hash]);
            
            $success = true;
            header("Location: ../../public/router.php?module=administration&action=list&lang=$lang&success=added");
            exit;
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في حفظ البيانات' : 'Erreur lors de l\'enregistrement';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un employé' ?> - Library System</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            background: white;
        }
        
        .error-message {
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
        }
        
        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin-right: 1rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="header-left">
                    <a href="../../public/router.php?module=administration&action=list&lang=<?=$lang?>" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        <?= $lang == 'ar' ? 'العودة للقائمة' : 'Retour à la liste' ?>
                    </a>
                </div>
                <div>
                    <h1><?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un employé' ?></h1>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                        <?= $lang == 'ar' ? 'إضافة موظف جديد إلى النظام' : 'Ajouter un nouvel employé au système' ?>
                    </p>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="main-content">
            <div class="form-container">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <h4><?= $lang == 'ar' ? 'أخطاء في النموذج' : 'Erreurs dans le formulaire' ?></h4>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label"><?= $lang == 'ar' ? 'الاسم' : 'Nom' ?></label>
                        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?= $lang == 'ar' ? 'الرقم' : 'Numéro' ?></label>
                        <input type="text" name="number" class="form-input" value="<?= htmlspecialchars($_POST['number'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?= $lang == 'ar' ? 'الوظيفة' : 'Fonction' ?></label>
                        <select name="function" class="form-select" required>
                            <option value=""><?= $lang == 'ar' ? 'اختر الوظيفة' : 'Choisir la fonction' ?></option>
                            <option value="مدير" <?= ($_POST['function'] ?? '') === 'مدير' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'مدير' : 'Directeur' ?></option>
                            <option value="أمين مكتبة" <?= ($_POST['function'] ?? '') === 'أمين مكتبة' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'أمين مكتبة' : 'Bibliothécaire' ?></option>
                            <option value="مساعد" <?= ($_POST['function'] ?? '') === 'مساعد' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'مساعد' : 'Assistant' ?></option>
                            <option value="استقبال" <?= ($_POST['function'] ?? '') === 'استقبال' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'استقبال' : 'Accueil' ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?= $lang == 'ar' ? 'حقوق الوصول' : 'Droits d\'accès' ?></label>
                        <select name="access_rights" class="form-select" required>
                            <option value=""><?= $lang == 'ar' ? 'اختر الحقوق' : 'Choisir les droits' ?></option>
                            <option value="admin,items,loans,borrowers,administration" <?= ($_POST['access_rights'] ?? '') === 'admin,items,loans,borrowers,administration' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'مدير - جميع الصلاحيات' : 'Admin - Tous les droits' ?></option>
                            <option value="items,loans,borrowers" <?= ($_POST['access_rights'] ?? '') === 'items,loans,borrowers' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'أمين مكتبة - إدارة المواد والقروض' : 'Bibliothécaire - Gestion des items et prêts' ?></option>
                            <option value="items,loans" <?= ($_POST['access_rights'] ?? '') === 'items,loans' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'مساعد - إدارة المواد والقروض' : 'Assistant - Gestion des items et prêts' ?></option>
                            <option value="borrowers" <?= ($_POST['access_rights'] ?? '') === 'borrowers' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'استقبال - إدارة المستعيرين' : 'Accueil - Gestion des emprunteurs' ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?= $lang == 'ar' ? 'كلمة المرور' : 'Mot de passe' ?></label>
                        <input type="password" name="password" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?= $lang == 'ar' ? 'تأكيد كلمة المرور' : 'Confirmer le mot de passe' ?></label>
                        <input type="password" name="confirm_password" class="form-input" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            <?= $lang == 'ar' ? 'حفظ الموظف' : 'Enregistrer l\'employé' ?>
                        </button>
                        <a href="../../public/router.php?module=administration&action=list&lang=<?=$lang?>" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../../public/assets/js/script.js"></script>
</body>
</html> 