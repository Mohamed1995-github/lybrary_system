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
    <link rel="stylesheet" href="../../public/assets/css/administration.css">
    <link rel="stylesheet" href="../../public/assets/css/forms.css">
<link rel="stylesheet" href="assets/css/professional-theme.css">
</head>
<body>
    <div class="page-container">
        <header class="page-header">
            <a href="../../public/router.php?module=administration&action=list&lang=<?= $lang ?>" class="action-btn btn-secondary" style="font-size: 0.875rem;">
                <i class="fas fa-arrow-left"></i> <?= $lang == 'ar' ? 'العودة للقائمة' : 'Retour à la liste' ?>
            </a>
        </header>

        <main>
            <div class="form-card">
                <div class="form-header">
                    <div class="page-title">
                        <i class="fas fa-user-plus page-icon"></i>
                        <?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un employé' ?>
                    </div>
                    <p class="form-subtitle"><?= $lang == 'ar' ? 'إضافة موظف جديد إلى النظام' : 'Ajouter un nouvel employé au système' ?></p>
                </div>

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

                <form method="POST" class="form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label"><?= $lang == 'ar' ? 'الاسم' : 'Nom' ?></label>
                        <div style="position: relative;">
                            <input type="text" id="name" name="name" class="form-input" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            <i class="fas fa-user" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="number" class="form-label"><?= $lang == 'ar' ? 'الرقم' : 'Numéro' ?></label>
                        <div style="position: relative;">
                            <input type="text" id="number" name="number" class="form-input" value="<?= htmlspecialchars($_POST['number'] ?? '') ?>" required>
                            <i class="fas fa-id-badge" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="function" class="form-label"><?= $lang == 'ar' ? 'الوظيفة' : 'Fonction' ?></label>
                        <div style="position: relative;">
                            <select id="function" name="function" class="form-select" required>
                                <option value=""><?= $lang == 'ar' ? 'اختر الوظيفة' : 'Choisir la fonction' ?></option>
                                <option value="مدير" <?= ($_POST['function'] ?? '') === 'مدير' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'مدير' : 'Directeur' ?></option>
                                <option value="أمين مكتبة" <?= ($_POST['function'] ?? '') === 'أمين مكتبة' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'أمين مكتبة' : 'Bibliothécaire' ?></option>
                                <option value="مساعد" <?= ($_POST['function'] ?? '') === 'مساعد' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'مساعد' : 'Assistant' ?></option>
                                <option value="استقبال" <?= ($_POST['function'] ?? '') === 'استقبال' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'استقبال' : 'Accueil' ?></option>
                            </select>
                            <i class="fas fa-briefcase" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-light); pointer-events: none;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="access_rights" class="form-label"><?= $lang == 'ar' ? 'حقوق الوصول' : 'Droits d\'accès' ?></label>
                        <div style="position: relative;">
                            <select id="access_rights" name="access_rights" class="form-select" required>
                                <option value=""><?= $lang == 'ar' ? 'اختر الحقوق' : 'Choisir les droits' ?></option>
                                <option value="admin,items,loans,borrowers,administration" <?= ($_POST['access_rights'] ?? '') === 'admin,items,loans,borrowers,administration' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'مدير - جميع الصلاحيات' : 'Admin - Tous les droits' ?></option>
                                <option value="items,loans,borrowers" <?= ($_POST['access_rights'] ?? '') === 'items,loans,borrowers' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'أمين مكتبة - إدارة المواد والقروض' : 'Bibliothécaire - Gestion des items et prêts' ?></option>
                                <option value="items,loans" <?= ($_POST['access_rights'] ?? '') === 'items,loans' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'مساعد - إدارة المواد والقروض' : 'Assistant - Gestion des items et prêts' ?></option>
                                <option value="borrowers" <?= ($_POST['access_rights'] ?? '') === 'borrowers' ? 'selected' : '' ?>><?= $lang == 'ar' ? 'استقبال - إدارة المستعيرين' : 'Accueil - Gestion des emprunteurs' ?></option>
                            </select>
                            <i class="fas fa-key" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-light); pointer-events: none;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label"><?= $lang == 'ar' ? 'كلمة المرور' : 'Mot de passe' ?></label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-input" required>
                            <i class="fas fa-lock" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label"><?= $lang == 'ar' ? 'تأكيد كلمة المرور' : 'Confirmer le mot de passe' ?></label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                            <i class="fas fa-lock" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        </div>
                    </div>

                    <div class="form-group full-width form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            <?= $lang == 'ar' ? 'حفظ الموظف' : 'Enregistrer l\'employé' ?>
                        </button>
                        <a href="../../public/router.php?module=administration&action=list&lang=<?= $lang ?>" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../../public/assets/js/script.js"></script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>
