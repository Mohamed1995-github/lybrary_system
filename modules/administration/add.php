<?php
/* modules/administration/add.php */
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/permissions.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

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
            
            // تطبيق الصلاحيات المقيدة حسب الوظيفة
            $restrictedRights = applyRestrictedPermissions($function, $access_rights, $lang);
            
            $stmt = $pdo->prepare("INSERT INTO employees (lang, name, number, function, access_rights, password) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$lang, $name, $number, $function, $restrictedRights, $password_hash]);
            
            $success = true;
            header("Location: list.php?lang=$lang&success=added");
            exit;
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في حفظ البيانات' : 'Erreur lors de l\'enregistrement';
        }
    }
}

/**
 * تطبيق الصلاحيات المقيدة حسب الوظيفة
 * Appliquer les permissions restreintes selon la fonction
 */
function applyRestrictedPermissions($function, $selectedRights, $lang) {
    // تحديد مستوى الوصول حسب الوظيفة
    $accessLevel = 'limited';
    
    if (strpos($function, 'مدير') !== false || strpos($function, 'Directeur') !== false) {
        $accessLevel = 'admin';
    } elseif (strpos($function, 'أمين') !== false || strpos($function, 'Bibliothécaire') !== false) {
        $accessLevel = 'librarian';
    } elseif (strpos($function, 'مساعد') !== false || strpos($function, 'Assistant') !== false) {
        $accessLevel = 'assistant';
    } elseif (strpos($function, 'استقبال') !== false || strpos($function, 'accueil') !== false) {
        $accessLevel = 'reception';
    } elseif (strpos($function, 'تقني') !== false || strpos($function, 'technique') !== false) {
        $accessLevel = 'technical';
    }
    
    // الحصول على الصلاحيات المقيدة حسب المستوى
    $restrictedPermissions = getRestrictedPermissions($accessLevel);
    $allowedPermissions = $restrictedPermissions[$lang] ?? [];
    
    // تقسيم الصلاحيات المحددة
    $selectedPermissions = explode(',', $selectedRights);
    $selectedPermissions = array_map('trim', $selectedPermissions);
    
    // تصفية الصلاحيات حسب المستوى المسموح
    $filteredPermissions = [];
    foreach ($selectedPermissions as $permission) {
        if (in_array($permission, $allowedPermissions)) {
            $filteredPermissions[] = $permission;
        }
    }
    
    // إذا لم تكن هناك صلاحيات محددة، تطبيق الصلاحيات الافتراضية للمستوى
    if (empty($filteredPermissions)) {
        $filteredPermissions = $allowedPermissions;
    }
    
    return implode(', ', $filteredPermissions);
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
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="header-left">
                    <a href="../../public/router.php?module=administration&action=list&lang=<?=$lang?>" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        <?= $lang == 'ar' ? 'العودة لقائمة الموظفين' : 'Retour à la liste des employés' ?>
                    </a>
                </div>
                <div>
                    <h1><?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un employé' ?></h1>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                        <?= $lang == 'ar' ? 'إضافة موظف جديد إلى نظام المكتبة' : 'Ajouter un nouvel employé au système de bibliothèque' ?>
                    </p>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title">
                        <i class="fas fa-user-plus"></i>
                        <?= $lang == 'ar' ? 'معلومات الموظف الجديد' : 'Informations du nouvel employé' ?>
                    </h2>
                    <p class="form-subtitle">
                        <?= $lang == 'ar' ? 'املأ النموذج أدناه لإضافة موظف جديد' : 'Remplissez le formulaire ci-dessous pour ajouter un nouvel employé' ?>
                    </p>
                </div>

                <!-- رسالة إعلامية عن الصلاحيات المقيدة -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <h4><?= $lang == 'ar' ? 'معلومات مهمة' : 'Information importante' ?></h4>
                        <p><?= $lang == 'ar' ? 'سيتم تطبيق صلاحيات مقيدة حسب الوظيفة المختارة. بعض الصلاحيات قد لا تكون متاحة لجميع الوظائف.' : 'Des permissions restreintes seront appliquées selon la fonction choisie. Certaines permissions peuvent ne pas être disponibles pour toutes les fonctions.' ?></p>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <?= $lang == 'ar' ? 'الاسم' : 'Nom' ?> *
                        </label>
                        <input type="text" id="name" name="name" class="form-input" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="number" class="form-label">
                            <?= $lang == 'ar' ? 'الرقم' : 'Numéro' ?> *
                        </label>
                        <input type="text" id="number" name="number" class="form-input" 
                               value="<?= htmlspecialchars($_POST['number'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="function" class="form-label">
                            <?= $lang == 'ar' ? 'الوظيفة' : 'Fonction' ?> *
                        </label>
                        <select id="function" name="function" class="form-select" required>
                            <option value=""><?= $lang == 'ar' ? 'اختر الوظيفة' : 'Sélectionner une fonction' ?></option>
                            <option value="<?= $lang == 'ar' ? 'مدير المكتبة' : 'Directeur de bibliothèque' ?>" <?= ($_POST['function'] ?? '') == ($lang == 'ar' ? 'مدير المكتبة' : 'Directeur de bibliothèque') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'مدير المكتبة' : 'Directeur de bibliothèque' ?>
                            </option>
                            <option value="<?= $lang == 'ar' ? 'أمين المكتبة' : 'Bibliothécaire' ?>" <?= ($_POST['function'] ?? '') == ($lang == 'ar' ? 'أمين المكتبة' : 'Bibliothécaire') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'أمين المكتبة' : 'Bibliothécaire' ?>
                            </option>
                            <option value="<?= $lang == 'ar' ? 'مساعد أمين المكتبة' : 'Assistant bibliothécaire' ?>" <?= ($_POST['function'] ?? '') == ($lang == 'ar' ? 'مساعد أمين المكتبة' : 'Assistant bibliothécaire') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'مساعد أمين المكتبة' : 'Assistant bibliothécaire' ?>
                            </option>
                            <option value="<?= $lang == 'ar' ? 'موظف استقبال' : 'Employé d\'accueil' ?>" <?= ($_POST['function'] ?? '') == ($lang == 'ar' ? 'موظف استقبال' : 'Employé d\'accueil') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'موظف استقبال' : 'Employé d\'accueil' ?>
                            </option>
                            <option value="<?= $lang == 'ar' ? 'موظف تقني' : 'Employé technique' ?>" <?= ($_POST['function'] ?? '') == ($lang == 'ar' ? 'موظف تقني' : 'Employé technique') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'موظف تقني' : 'Employé technique' ?>
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <?= $lang == 'ar' ? 'كلمة المرور' : 'Mot de passe' ?> *
                        </label>
                        <input type="password" id="password" name="password" class="form-input" 
                               value="<?= htmlspecialchars($_POST['password'] ?? '') ?>" required 
                               minlength="6" autocomplete="new-password">
                        <small class="form-help">
                            <?= $lang == 'ar' ? 'يجب أن تكون كلمة المرور 6 أحرف على الأقل' : 'Le mot de passe doit contenir au moins 6 caractères' ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <?= $lang == 'ar' ? 'تأكيد كلمة المرور' : 'Confirmer le mot de passe' ?> *
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                               value="<?= htmlspecialchars($_POST['confirm_password'] ?? '') ?>" required 
                               minlength="6" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="access_rights" class="form-label">
                            <?= $lang == 'ar' ? 'حقوق الوصول' : 'Droits d\'accès' ?> *
                        </label>
                        <div class="checkbox-group">
                            <label class="checkbox-item">
                                <input type="checkbox" name="access_rights[]" value="<?= $lang == 'ar' ? 'إدارة الكتب' : 'Gestion des livres' ?>">
                                <span class="checkmark"></span>
                                <?= $lang == 'ar' ? 'إدارة الكتب' : 'Gestion des livres' ?>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="access_rights[]" value="<?= $lang == 'ar' ? 'إدارة المجلات' : 'Gestion des magazines' ?>">
                                <span class="checkmark"></span>
                                <?= $lang == 'ar' ? 'إدارة المجلات' : 'Gestion des magazines' ?>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="access_rights[]" value="<?= $lang == 'ar' ? 'إدارة الصحف' : 'Gestion des journaux' ?>">
                                <span class="checkmark"></span>
                                <?= $lang == 'ar' ? 'إدارة الصحف' : 'Gestion des journaux' ?>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="access_rights[]" value="<?= $lang == 'ar' ? 'إدارة التزويد' : 'Gestion des approvisionnements' ?>">
                                <span class="checkmark"></span>
                                <?= $lang == 'ar' ? 'إدارة التزويد' : 'Gestion des approvisionnements' ?>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="access_rights[]" value="<?= $lang == 'ar' ? 'إدارة المستعيرين' : 'Gestion des emprunteurs' ?>">
                                <span class="checkmark"></span>
                                <?= $lang == 'ar' ? 'إدارة المستعيرين' : 'Gestion des emprunteurs' ?>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="access_rights[]" value="<?= $lang == 'ar' ? 'إدارة الإعارة' : 'Gestion des prêts' ?>">
                                <span class="checkmark"></span>
                                <?= $lang == 'ar' ? 'إدارة الإعارة' : 'Gestion des prêts' ?>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="access_rights[]" value="<?= $lang == 'ar' ? 'إدارة الموظفين' : 'Gestion des employés' ?>">
                                <span class="checkmark"></span>
                                <?= $lang == 'ar' ? 'إدارة الموظفين' : 'Gestion des employés' ?>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="access_rights[]" value="<?= $lang == 'ar' ? 'إدارة النظام' : 'Administration du système' ?>">
                                <span class="checkmark"></span>
                                <?= $lang == 'ar' ? 'إدارة النظام' : 'Administration du système' ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            <?= $lang == 'ar' ? 'حفظ الموظف' : 'Enregistrer l\'employé' ?>
                        </button>
                        <a href="../../public/router.php?module=administration&action=list&lang=<?=$lang?>" class="action-btn btn-secondary">
                            <i class="fas fa-times"></i>
                            <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../../public/assets/js/script.js"></script>
    <script>
        // معالجة حقوق الوصول
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const accessRightsCheckboxes = document.querySelectorAll('input[name="access_rights[]"]');
            const functionSelect = document.getElementById('function');
            
            // دالة لتحديث الصلاحيات المتاحة حسب الوظيفة
            function updatePermissions() {
                const selectedFunction = functionSelect.value;
                const checkboxes = document.querySelectorAll('input[name="access_rights[]"]');
                
                // إعادة تفعيل جميع الصلاحيات
                checkboxes.forEach(cb => {
                    cb.disabled = false;
                    cb.checked = false;
                });
                
                // تطبيق القيود حسب الوظيفة
                if (selectedFunction.includes('موظف تقني') || selectedFunction.includes('technique')) {
                    // الموظف التقني: صلاحيات محدودة
                    checkboxes.forEach(cb => {
                        if (!cb.value.includes('النظام') && !cb.value.includes('système')) {
                            cb.disabled = true;
                        }
                    });
                } else if (selectedFunction.includes('موظف استقبال') || selectedFunction.includes('accueil')) {
                    // موظف الاستقبال: صلاحيات المستعيرين والإعارة فقط
                    checkboxes.forEach(cb => {
                        if (!cb.value.includes('المستعيرين') && !cb.value.includes('الإعارة') && 
                            !cb.value.includes('emprunteurs') && !cb.value.includes('prêts')) {
                            cb.disabled = true;
                        }
                    });
                } else if (selectedFunction.includes('مساعد') || selectedFunction.includes('Assistant')) {
                    // المساعد: لا يمكنه إدارة الموظفين أو النظام
                    checkboxes.forEach(cb => {
                        if (cb.value.includes('الموظفين') || cb.value.includes('النظام') ||
                            cb.value.includes('employés') || cb.value.includes('système')) {
                            cb.disabled = true;
                        }
                    });
                } else if (selectedFunction.includes('أمين') || selectedFunction.includes('Bibliothécaire')) {
                    // أمين المكتبة: لا يمكنه إدارة الموظفين أو النظام
                    checkboxes.forEach(cb => {
                        if (cb.value.includes('الموظفين') || cb.value.includes('النظام') ||
                            cb.value.includes('employés') || cb.value.includes('système')) {
                            cb.disabled = true;
                        }
                    });
                }
                // المدير لديه جميع الصلاحيات
            }
            
            // تحديث الصلاحيات عند تغيير الوظيفة
            functionSelect.addEventListener('change', updatePermissions);
            
            form.addEventListener('submit', function(e) {
                const checkedRights = Array.from(accessRightsCheckboxes)
                    .filter(cb => cb.checked && !cb.disabled)
                    .map(cb => cb.value);
                
                if (checkedRights.length === 0) {
                    e.preventDefault();
                    alert('<?= $lang == 'ar' ? 'يرجى اختيار حقوق وصول واحدة على الأقل' : 'Veuillez sélectionner au moins un droit d\'accès' ?>');
                    return;
                }
                
                // إنشاء حقل مخفي لحقوق الوصول المحددة
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'access_rights';
                hiddenInput.value = checkedRights.join(', ');
                form.appendChild(hiddenInput);
            });
            
            // تحديث الصلاحيات عند تحميل الصفحة
            updatePermissions();
        });
    </script>
</body>
</html> 