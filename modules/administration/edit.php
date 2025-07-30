<?php
/* modules/administration/edit.php */
require_once '../../config/db.php';
require_once '../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: list.php?lang=$lang&error=no_id");
    exit;
}

$errors = [];
$success = false;

// الحصول على بيانات الموظف
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ? AND lang = ?");
    $stmt->execute([$id, $lang]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        header("Location: list.php?lang=$lang&error=not_found");
        exit;
    }
} catch (PDOException $e) {
    header("Location: list.php?lang=$lang&error=db_error");
    exit;
}

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $number = trim($_POST['number'] ?? '');
    $function = trim($_POST['function'] ?? '');
    $access_rights = trim($_POST['access_rights'] ?? '');
    
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
    
    // التحقق من عدم تكرار الرقم (باستثناء الموظف الحالي)
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE number = ? AND lang = ? AND id != ?");
            $stmt->execute([$number, $lang, $id]);
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
            $stmt = $pdo->prepare("UPDATE employees SET name=?, number=?, function=?, access_rights=? WHERE id=?");
            $stmt->execute([$name, $number, $function, $access_rights, $id]);
            
            $success = true;
            header("Location: list.php?lang=$lang&success=updated");
            exit;
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في حفظ البيانات' : 'Erreur lors de l\'enregistrement';
        }
    }
}

// تحضير حقوق الوصول للعرض
$current_rights = explode(',', $employee['access_rights']);
$current_rights = array_map('trim', $current_rights);
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'تعديل الموظف' : 'Modifier l\'employé' ?> - Library System</title>
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
                    <h1><?= $lang == 'ar' ? 'تعديل الموظف' : 'Modifier l\'employé' ?></h1>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                        <?= $lang == 'ar' ? 'تعديل معلومات الموظف' : 'Modifier les informations de l\'employé' ?>
                    </p>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title">
                        <i class="fas fa-user-edit"></i>
                        <?= $lang == 'ar' ? 'معلومات الموظف' : 'Informations de l\'employé' ?>
                    </h2>
                    <p class="form-subtitle">
                        <?= $lang == 'ar' ? 'قم بتعديل المعلومات أدناه' : 'Modifiez les informations ci-dessous' ?>
                    </p>
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
                               value="<?= htmlspecialchars($_POST['name'] ?? $employee['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="number" class="form-label">
                            <?= $lang == 'ar' ? 'الرقم' : 'Numéro' ?> *
                        </label>
                        <input type="text" id="number" name="number" class="form-input" 
                               value="<?= htmlspecialchars($_POST['number'] ?? $employee['number']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="function" class="form-label">
                            <?= $lang == 'ar' ? 'الوظيفة' : 'Fonction' ?> *
                        </label>
                        <select id="function" name="function" class="form-select" required>
                            <option value=""><?= $lang == 'ar' ? 'اختر الوظيفة' : 'Sélectionner une fonction' ?></option>
                            <option value="<?= $lang == 'ar' ? 'مدير المكتبة' : 'Directeur de bibliothèque' ?>" <?= ($_POST['function'] ?? $employee['function']) == ($lang == 'ar' ? 'مدير المكتبة' : 'Directeur de bibliothèque') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'مدير المكتبة' : 'Directeur de bibliothèque' ?>
                            </option>
                            <option value="<?= $lang == 'ar' ? 'أمين المكتبة' : 'Bibliothécaire' ?>" <?= ($_POST['function'] ?? $employee['function']) == ($lang == 'ar' ? 'أمين المكتبة' : 'Bibliothécaire') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'أمين المكتبة' : 'Bibliothécaire' ?>
                            </option>
                            <option value="<?= $lang == 'ar' ? 'مساعد أمين المكتبة' : 'Assistant bibliothécaire' ?>" <?= ($_POST['function'] ?? $employee['function']) == ($lang == 'ar' ? 'مساعد أمين المكتبة' : 'Assistant bibliothécaire') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'مساعد أمين المكتبة' : 'Assistant bibliothécaire' ?>
                            </option>
                            <option value="<?= $lang == 'ar' ? 'موظف استقبال' : 'Employé d\'accueil' ?>" <?= ($_POST['function'] ?? $employee['function']) == ($lang == 'ar' ? 'موظف استقبال' : 'Employé d\'accueil') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'موظف استقبال' : 'Employé d\'accueil' ?>
                            </option>
                            <option value="<?= $lang == 'ar' ? 'موظف تقني' : 'Employé technique' ?>" <?= ($_POST['function'] ?? $employee['function']) == ($lang == 'ar' ? 'موظف تقني' : 'Employé technique') ? 'selected' : '' ?>>
                                <?= $lang == 'ar' ? 'موظف تقني' : 'Employé technique' ?>
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="access_rights" class="form-label">
                            <?= $lang == 'ar' ? 'حقوق الوصول' : 'Droits d\'accès' ?> *
                        </label>
                        <div class="checkbox-group">
                            <?php 
                            $rights_options = [
                                $lang == 'ar' ? 'إدارة الكتب' : 'Gestion des livres',
                                $lang == 'ar' ? 'إدارة المجلات' : 'Gestion des magazines',
                                $lang == 'ar' ? 'إدارة الصحف' : 'Gestion des journaux',
                                $lang == 'ar' ? 'إدارة التزويد' : 'Gestion des approvisionnements',
                                $lang == 'ar' ? 'إدارة المستعيرين' : 'Gestion des emprunteurs',
                                $lang == 'ar' ? 'إدارة الإعارة' : 'Gestion des prêts',
                                $lang == 'ar' ? 'إدارة الموظفين' : 'Gestion des employés',
                                $lang == 'ar' ? 'إدارة النظام' : 'Administration du système'
                            ];
                            
                            foreach ($rights_options as $right):
                                $is_checked = in_array($right, $current_rights) ? 'checked' : '';
                            ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="access_rights[]" value="<?= $right ?>" <?= $is_checked ?>>
                                    <span class="checkmark"></span>
                                    <?= $right ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            <?= $lang == 'ar' ? 'حفظ التغييرات' : 'Enregistrer les modifications' ?>
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
            
            form.addEventListener('submit', function(e) {
                const checkedRights = Array.from(accessRightsCheckboxes)
                    .filter(cb => cb.checked)
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
        });
    </script>
</body>
</html> 