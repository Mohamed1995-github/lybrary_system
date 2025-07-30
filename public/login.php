<?php
// Ne PAS mettre session_start() ici
require_once __DIR__.'/../includes/auth.php';

if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'fr'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'ar';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$u]);
    $user = $stmt->fetch();
    if ($user && password_verify($p, $user['password'])) {
        $_SESSION['uid'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    }
    $error = "❌ Identifiants incorrects / بيانات غير صحيحة";
}
?>

<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang == 'ar') ? 'rtl' : 'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'تسجيل الدخول' : 'Connexion' ?> - Library System</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.login-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    padding: 3rem;
    width: 100%;
    max-width: 400px;
    text-align: center;
    border: 1px solid var(--border-color);
}

.login-header {
    margin-bottom: 2rem;
}

.login-logo {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.login-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.login-subtitle {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.login-form {
    text-align: right;
}

.form-group {
    margin-bottom: 1.5rem;
    text-align: right;
}

.form-input {
    width: 100%;
    padding: 1rem 1.25rem;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    font-size: 1rem;
    transition: all 0.2s ease;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
}

.form-input::placeholder {
    color: var(--text-light);
}

.login-btn {
    width: 100%;
    padding: 1rem;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: var(--radius-lg);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 1rem;
}

.login-btn:hover {
    background: var(--primary-hover);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.language-switch {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.language-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: var(--radius-md);
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

@media (max-width: 480px) {
    .login-card {
        padding: 2rem;
        margin: 1rem;
    }
    
    .login-logo {
        font-size: 2rem;
    }
    
    .login-title {
        font-size: 1.5rem;
    }
}
</style>
</head>
<body>
<div class="login-container">
    <div class="login-card fade-in">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-book-open"></i>
            </div>
            <h1 class="login-title"><?= $lang == 'ar' ? 'نظام المكتبة' : 'Système de Bibliothèque' ?></h1>
            <p class="login-subtitle"><?= $lang == 'ar' ? 'تسجيل الدخول للوصول إلى النظام' : 'Connectez-vous pour accéder au système' ?></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="form-group">
                <input type="text" name="username" class="form-input" 
                       placeholder="<?= $lang == 'ar' ? 'اسم المستخدم' : 'Nom d\'utilisateur' ?>" 
                       required autocomplete="username">
            </div>
            
            <div class="form-group">
                <input type="password" name="password" class="form-input" 
                       placeholder="<?= $lang == 'ar' ? 'كلمة المرور' : 'Mot de passe' ?>" 
                       required autocomplete="current-password">
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                <?= $lang == 'ar' ? 'تسجيل الدخول' : 'Se connecter' ?>
            </button>
        </form>

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
        
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <a href="employee_login.php?lang=<?= $lang ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                <i class="fas fa-users-cog"></i>
                <?= $lang == 'ar' ? 'تسجيل دخول الموظفين' : 'Connexion Employés' ?>
            </a>
        </div>
    </div>
</div>
</body>
</html>
