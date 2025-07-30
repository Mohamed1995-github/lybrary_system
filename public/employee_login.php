<?php
/* public/employee_login.php */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'fr'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'ar';
require_once '../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_number = trim($_POST['employee_number'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($employee_number) || empty($password)) {
        $error = $lang == 'ar' ? 'يرجى إدخال جميع البيانات المطلوبة' : 'Veuillez saisir toutes les informations requises';
    } else {
        try {
            // البحث عن الموظف برقمه
            $stmt = $pdo->prepare('SELECT * FROM employees WHERE number = ? AND lang = ?');
            $stmt->execute([$employee_number, $lang]);
            $employee = $stmt->fetch();
            
            if ($employee) {
                // في هذا المثال، نستخدم كلمة مرور بسيطة (يمكن تطويرها لاحقاً)
                // يمكن استخدام password_hash() و password_verify() للتحقق الآمن
                if ($password === '123456' || password_verify($password, $employee['password'] ?? '')) {
                    $_SESSION['uid'] = $employee['id'];
                    $_SESSION['employee_name'] = $employee['name'];
                    $_SESSION['employee_function'] = $employee['function'];
                    $_SESSION['employee_rights'] = $employee['access_rights'];
                    
                    header('Location: employee_dashboard.php?lang=' . $lang);
                    exit;
                } else {
                    $error = $lang == 'ar' ? 'كلمة المرور غير صحيحة' : 'Mot de passe incorrect';
                }
            } else {
                $error = $lang == 'ar' ? 'رقم الموظف غير موجود' : 'Numéro d\'employé introuvable';
            }
        } catch (PDOException $e) {
            $error = $lang == 'ar' ? 'خطأ في النظام' : 'Erreur système';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'تسجيل دخول الموظفين' : 'Connexion Employés' ?> - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        
        .login-header {
            margin-bottom: 2.5rem;
        }
        
        .login-logo {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
        }
        
        .login-form {
            text-align: <?= $lang == 'ar' ? 'right' : 'left' ?>;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
            text-align: <?= $lang == 'ar' ? 'right' : 'left' ?>;
        }
        
        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #f9fafb;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }
        
        .login-btn:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3);
        }
        
        .demo-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 1rem;
            margin: 1.5rem 0;
            text-align: <?= $lang == 'ar' ? 'right' : 'left' ?>;
        }
        
        .demo-info h4 {
            margin: 0 0 0.5rem 0;
            color: #0c4a6e;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .demo-info p {
            margin: 0.25rem 0;
            color: #0369a1;
            font-size: 0.875rem;
        }
        
        .language-switch {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .language-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            background: #f3f4f6;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .language-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }
        
        .language-btn.active {
            background: var(--primary-color);
            color: white;
        }
        
        .back-link {
            position: absolute;
            top: 2rem;
            <?= $lang == 'ar' ? 'right' : 'left' ?>: 2rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem;
                margin: 1rem;
            }
            
            .login-logo {
                font-size: 2.5rem;
            }
            
            .login-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="login.php?lang=<?= $lang ?>" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <?= $lang == 'ar' ? 'العودة لتسجيل الدخول العام' : 'Retour à la connexion générale' ?>
        </a>
        
        <div class="login-card fade-in">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h1 class="login-title"><?= $lang == 'ar' ? 'تسجيل دخول الموظفين' : 'Connexion Employés' ?></h1>
                <p class="login-subtitle">
                    <?= $lang == 'ar' ? 'سجل دخولك للوصول إلى لوحة التحكم المخصصة' : 'Connectez-vous pour accéder à votre tableau de bord personnalisé' ?>
                </p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="login-form">
                <div class="form-group">
                    <label for="employee_number" class="form-label">
                        <?= $lang == 'ar' ? 'رقم الموظف' : 'Numéro d\'employé' ?> *
                    </label>
                    <input type="text" id="employee_number" name="employee_number" class="form-input" 
                           placeholder="<?= $lang == 'ar' ? 'مثال: EMP001' : 'Ex: EMP001' ?>" 
                           required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <?= $lang == 'ar' ? 'كلمة المرور' : 'Mot de passe' ?> *
                    </label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="<?= $lang == 'ar' ? 'كلمة المرور' : 'Mot de passe' ?>" 
                           required>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <?= $lang == 'ar' ? 'تسجيل الدخول' : 'Se connecter' ?>
                </button>
            </form>

            <div class="demo-info">
                <h4><?= $lang == 'ar' ? 'معلومات تجريبية:' : 'Informations de démonstration:' ?></h4>
                <p><?= $lang == 'ar' ? 'رقم الموظف: EMP001' : 'Numéro: EMP001' ?></p>
                <p><?= $lang == 'ar' ? 'كلمة المرور: 123456' : 'Mot de passe: 123456' ?></p>
            </div>

            <div class="language-switch">
                <a href="?lang=ar" class="language-btn <?= $lang == 'ar' ? 'active' : '' ?>">
                    <i class="fas fa-language"></i>
                    🇸🇦 عربي
                </a>
                <a href="?lang=fr" class="language-btn <?= $lang == 'fr' ? 'active' : '' ?>">
                    <i class="fas fa-language"></i>
                    🇫🇷 Français
                </a>
            </div>
        </div>
    </div>
</body>
</html> 