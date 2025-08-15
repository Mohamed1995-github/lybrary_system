<?php
require_once __DIR__.'/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_SESSION['lang'] ?? 'ar';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($username)) {
        $errors[] = $lang == 'ar' ? 'اسم المستخدم مطلوب' : 'Le nom d\'utilisateur est requis';
    } elseif (strlen($username) < 3) {
        $errors[] = $lang == 'ar' ? 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل' : 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
    }
    
    if (empty($password)) {
        $errors[] = $lang == 'ar' ? 'كلمة المرور مطلوبة' : 'Le mot de passe est requis';
    } elseif (strlen($password) < 6) {
        $errors[] = $lang == 'ar' ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل' : 'Le mot de passe doit contenir au moins 6 caractères';
    }
    
    if (empty($full_name)) {
        $errors[] = $lang == 'ar' ? 'الاسم الكامل مطلوب' : 'Le nom complet est requis';
    }
    
    // Check if username already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = $lang == 'ar' ? 'اسم المستخدم موجود مسبقاً' : 'Le nom d\'utilisateur existe déjà';
        }
    }
    
    // If no errors, insert the new member
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'member'; // Always set role as member for borrowers
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password_hash, $full_name, $email, $phone, $address, $role]);
            
            $success = $lang == 'ar' ? 'تم إضافة العضو بنجاح!' : 'Membre ajouté avec succès!';
        } catch (PDOException $e) {
            $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'إضافة عضو جديد' : 'Ajouter un nouveau membre' ?> - Library System</title>
<link rel="stylesheet" href="../../public/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../../public/assets/js/script.js"></script>
<style>
.page-container {
    min-height: 100vh;
    padding: 2rem;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
}

.page-icon {
    font-size: 2rem;
    color: var(--secondary-color);
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(5 150 105 / 0.1);
    border-radius: var(--radius-lg);
}

.page-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-radius: var(--radius-lg);
    font-weight: 500;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 2px solid var(--border-color);
}

.btn-secondary:hover {
    background: var(--bg-tertiary);
    transform: translateY(-1px);
}

.form-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 2.5rem;
    border: 1px solid var(--border-color);
    max-width: 800px;
    margin: 0 auto;
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid var(--border-color);
}

.form-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.form-subtitle {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.form-input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    font-size: 1rem;
    transition: all 0.2s ease;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.form-input:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgb(5 150 105 / 0.1);
}

.form-input::placeholder {
    color: var(--text-light);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    padding-top: 2rem;
    border-top: 2px solid var(--border-color);
}

.btn-primary {
    background: var(--secondary-color);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 150px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-primary:hover {
    background: var(--secondary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.form-info {
    background: rgb(5 150 105 / 0.1);
    border: 1px solid rgb(5 150 105 / 0.2);
    border-radius: var(--radius-lg);
    padding: 1rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--secondary-color);
}

.form-info i {
    font-size: 1.25rem;
}

.required-field {
    color: var(--error-color);
    margin-left: 0.25rem;
}

@media (max-width: 768px) {
    .page-container {
        padding: 1rem;
    }
    
    .page-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .page-title {
        font-size: 1.5rem;
        justify-content: center;
    }
    
    .page-actions {
        justify-content: center;
    }
    
    .form-card {
        padding: 1.5rem;
        margin: 0 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-primary {
        width: 100%;
    }
}
</style>
</head>
<body>
<div class="page-container">
    <div class="page-header fade-in">
        <div class="page-title">
            <div class="page-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'إضافة عضو جديد' : 'Ajouter un nouveau membre' ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'إضافة مستعير جديد إلى النظام' : 'Ajouter un nouvel emprunteur au système' ?>
                </p>
            </div>
        </div>
        
        <div class="page-actions">
            <a href=" http://localhost/library_system/public/dashboard.php" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
            </a>
        </div>
    </div>

    <div class="form-card fade-in">
        <div class="form-header">
            <h2 class="form-title">
                <i class="fas fa-user-plus"></i>
                <?= $lang == 'ar' ? 'معلومات العضو الجديد' : 'Informations du nouveau membre' ?>
            </h2>
            <p class="form-subtitle">
                <?= $lang == 'ar' ? 'املأ النموذج أدناه لإضافة عضو جديد (مستعير فقط)' : 'Remplissez le formulaire ci-dessous pour ajouter un nouveau membre (emprunteur uniquement)' ?>
            </p>
        </div>

        <div class="form-info">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong><?= $lang == 'ar' ? 'ملاحظة:' : 'Note:' ?></strong> 
                <?= $lang == 'ar' ? 'سيتم إنشاء حساب مستعير فقط بدون صلاحيات إدارية' : 'Un compte emprunteur sera créé sans privilèges administratifs' ?>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <ul style="margin: 0; padding-right: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="post" id="memberForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i>
                        <?= $lang == 'ar' ? 'اسم المستخدم' : 'Nom d\'utilisateur' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="username" name="username" class="form-input" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل اسم المستخدم' : 'Entrez le nom d\'utilisateur' ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        <?= $lang == 'ar' ? 'كلمة المرور' : 'Mot de passe' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل كلمة المرور' : 'Entrez le mot de passe' ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label">
                        <i class="fas fa-id-card"></i>
                        <?= $lang == 'ar' ? 'الاسم الكامل' : 'Nom complet' ?>
                        <span class="required-field">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" class="form-input" 
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل الاسم الكامل' : 'Entrez le nom complet' ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        <?= $lang == 'ar' ? 'البريد الإلكتروني' : 'Email' ?>
                    </label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل البريد الإلكتروني' : 'Entrez l\'email' ?>">
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i>
                        <?= $lang == 'ar' ? 'رقم الهاتف' : 'Numéro de téléphone' ?>
                    </label>
                    <input type="tel" id="phone" name="phone" class="form-input" 
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم الهاتف' : 'Entrez le numéro de téléphone' ?>">
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= $lang == 'ar' ? 'العنوان' : 'Adresse' ?>
                    </label>
                    <textarea id="address" name="address" class="form-input" rows="3"
                              placeholder="<?= $lang == 'ar' ? 'أدخل العنوان' : 'Entrez l\'adresse' ?>"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $lang == 'ar' ? 'إضافة العضو' : 'Ajouter le membre' ?>
                </button>
                <a href=" http://localhost/library_system/public/dashboard.php" class="action-btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Form submission enhancement
document.getElementById('memberForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري الإضافة...' : 'Ajout en cours...' ?>';
    submitBtn.disabled = true;
    
    // Show success notification after a short delay
    setTimeout(() => {
        if (window.LibrarySystem) {
            window.LibrarySystem.showNotification(
                '<?= $lang == 'ar' ? 'تم إضافة العضو بنجاح!' : 'Membre ajouté avec succès!' ?>', 
                'success'
            );
        }
    }, 1000);
});
</script>
</body>
</html> 