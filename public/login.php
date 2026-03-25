<?php
/**
 * Page de connexion simple
 */

session_start();

// Charger la base de données
require_once __DIR__ . '/../config/db.php';

$lang = $_GET['lang'] ?? 'ar';

// Rediriger si déjà connecté
if (isset($_SESSION['uid'])) {
    header('Location: dashboard.php?lang=' . $lang);
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $errors[] = $lang == 'ar' ? 'يرجى ملء جميع الحقول' : 'Veuillez remplir tous les champs';
    } else {
        try {
            // Rechercher l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE number = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Connexion réussie
                $_SESSION['uid'] = $user['id'];
                $_SESSION['username'] = $user['number'];
                $_SESSION['role'] = $user['access_rights'] ?? 'employee';
                $_SESSION['lang'] = $user['lang'] ?? 'ar';
                
                // Redirection
                header('Location: dashboard.php?lang=' . $lang);
                exit;
            } else {
                $errors[] = $lang == 'ar' ? 'رقم الموظف أو كلمة المرور غير صحيحة' : 'Numéro d\'employé ou mot de passe incorrect';
            }
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
<title><?= $lang == 'ar' ? 'تسجيل الدخول' : 'Connexion' ?> - Library System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary-color: #4f46e5;
    --primary-dark: #3730a3;
    --primary-light: #818cf8;
    --secondary-color: #06b6d4;
    --success-color: #10b981;
    --error-color: #ef4444;
    --warning-color: #f59e0b;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --white: #ffffff;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
    --radius-2xl: 1.5rem;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    position: relative;
    overflow: hidden;
}

body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.login-container {
    background: var(--white);
    backdrop-filter: blur(20px);
    border-radius: var(--radius-2xl);
    padding: 3rem 2.5rem;
    box-shadow: var(--shadow-xl), 0 0 0 1px rgba(255, 255, 255, 0.05);
    max-width: 440px;
    width: 100%;
    text-align: center;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-header {
    margin-bottom: 2.5rem;
}

.logo-container {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border-radius: var(--radius-xl);
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-lg);
}

.login-icon {
    font-size: 2rem;
    color: var(--white);
}

.login-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.login-subtitle {
    color: var(--gray-500);
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
}

.form-group {
    margin-bottom: 1.5rem;
    text-align: left;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
    font-size: 0.875rem;
    letter-spacing: 0.025em;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400);
    font-size: 1.125rem;
    transition: color 0.2s ease;
}

.form-input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    font-size: 1rem;
    font-weight: 400;
    transition: all 0.2s ease;
    background: var(--white);
    color: var(--gray-800);
    line-height: 1.5;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-input:focus + .input-icon {
    color: var(--primary-color);
}

.login-btn {
    width: 100%;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: var(--white);
    border: none;
    border-radius: var(--radius-lg);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.login-btn:hover::before {
    left: 100%;
}

.login-btn:hover {
    background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
    transform: translateY(-1px);
    box-shadow: var(--shadow-lg);
}

.login-btn:active {
    transform: translateY(0);
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    text-align: left;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    color: var(--error-color);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.alert-icon {
    font-size: 1.25rem;
    margin-top: 0.125rem;
}

.lang-selector {
    margin-top: 2rem;
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
}

.lang-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    text-decoration: none;
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.lang-btn.active {
    background: var(--primary-color);
    color: var(--white);
    box-shadow: var(--shadow-sm);
}

.lang-btn.inactive {
    background: var(--gray-100);
    color: var(--gray-600);
    border-color: var(--gray-200);
}

.lang-btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.lang-btn.inactive:hover {
    background: var(--gray-200);
    border-color: var(--gray-300);
}

.floating-elements {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
}

.floating-element {
    position: absolute;
    opacity: 0.1;
    animation: float 20s infinite linear;
}

.floating-element:nth-child(1) {
    top: 20%;
    left: 10%;
    font-size: 2rem;
    animation-delay: 0s;
}

.floating-element:nth-child(2) {
    top: 60%;
    right: 10%;
    font-size: 1.5rem;
    animation-delay: 5s;
}

.floating-element:nth-child(3) {
    bottom: 30%;
    left: 20%;
    font-size: 1.8rem;
    animation-delay: 10s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
}

/* Responsive Design */
@media (max-width: 640px) {
    body {
        padding: 1rem 0.5rem;
    }
    
    .login-container {
        padding: 2rem 1.5rem;
        border-radius: var(--radius-xl);
    }
    
    .login-title {
        font-size: 1.5rem;
    }
    
    .logo-container {
        width: 60px;
        height: 60px;
    }
    
    .login-icon {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .login-container {
        padding: 1.5rem 1rem;
    }
    
    .lang-selector {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .lang-btn {
        justify-content: center;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .login-container {
        background: rgba(17, 24, 39, 0.95);
        border-color: rgba(75, 85, 99, 0.3);
    }
    
    .login-title {
        color: var(--gray-100);
    }
    
    .login-subtitle {
        color: var(--gray-400);
    }
    
    .form-label {
        color: var(--gray-300);
    }
    
    .form-input {
        background: rgba(31, 41, 55, 0.5);
        border-color: var(--gray-600);
        color: var(--gray-100);
    }
    
    .form-input:focus {
        border-color: var(--primary-light);
    }
}
</style>
<link rel="stylesheet" href="assets/css/professional-theme.css">
</head>
<body>
    <!-- Éléments flottants décoratifs -->
    <div class="floating-elements">
        <div class="floating-element"><i class="fas fa-book"></i></div>
        <div class="floating-element"><i class="fas fa-graduation-cap"></i></div>
        <div class="floating-element"><i class="fas fa-bookmark"></i></div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <i class="fas fa-book-open login-icon"></i>
            </div>
            <h1 class="login-title">
                <?= $lang == 'ar' ? ' النظام الإلكتروني للمكتبة' : 'SEBIL–ENAJM : Système Électronique de la Bibliothèque' ?>
            </h1>
            <p class="login-subtitle">
                <?= $lang == 'ar' ? 'المدرسة الوطنية للإدارة و الصحافة و القضاء' : 'École Nationale d\'Administrationet de magistrature' ?>
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username" class="form-label">
                    <?= $lang == 'ar' ? 'رقم الموظف' : 'Numéro d\'employé' ?>
                </label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" class="form-input" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل رقم الموظف' : 'Entrez votre numéro d\'employé' ?>" 
                           required autocomplete="username">
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    <?= $lang == 'ar' ? 'كلمة المرور' : 'Mot de passe' ?>
                </label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="<?= $lang == 'ar' ? 'أدخل كلمة المرور' : 'Entrez votre mot de passe' ?>" 
                           required autocomplete="current-password">
                    <i class="fas fa-lock input-icon"></i>
                </div>
            </div>

            <button type="submit" class="login-btn" id="loginButton">
                <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
                <?= $lang == 'ar' ? 'تسجيل الدخول' : 'Se connecter' ?>
            </button>
        </form>

        <!-- Sélecteur de langue -->
        <div class="lang-selector">
            <a href="?lang=ar" class="lang-btn <?= $lang == 'ar' ? 'active' : 'inactive' ?>">
                <i class="fas fa-globe"></i>
                العربية
            </a>
            <a href="?lang=fr" class="lang-btn <?= $lang == 'fr' ? 'active' : 'inactive' ?>">
                <i class="fas fa-globe"></i>
                Français
            </a>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Focus automatique sur le premier champ
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    const loginButton = document.getElementById('loginButton');
    const loginForm = document.getElementById('loginForm');
    
    // Focus initial
    setTimeout(() => {
        usernameField.focus();
    }, 500);
    
    // Animation des champs lors du focus
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
    
    // Animation du bouton lors du clic
    loginButton.addEventListener('click', function(e) {
        // Vérifier si les champs sont remplis
        if (usernameField.value.trim() === '' || passwordField.value.trim() === '') {
            e.preventDefault();
            
            // Animation d'erreur
            loginButton.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
                loginButton.style.animation = '';
            }, 500);
            
            return false;
        }
        
        // Ne pas empêcher la soumission du formulaire
        // Juste changer l'apparence du bouton
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 0.5rem;"></i><?= $lang == "ar" ? "جاري التحقق..." : "Vérification..." ?>';
            this.disabled = true;
        }, 50);
    });
    
    // Validation en temps réel
    usernameField.addEventListener('input', function() {
        validateField(this);
    });
    
    passwordField.addEventListener('input', function() {
        validateField(this);
    });
    
    function validateField(field) {
        const wrapper = field.parentElement;
        const icon = wrapper.querySelector('.input-icon');
        
        if (field.value.trim() !== '') {
            field.style.borderColor = 'var(--success-color)';
            icon.style.color = 'var(--success-color)';
            icon.className = icon.className.replace('fa-user', 'fa-check').replace('fa-lock', 'fa-check');
        } else {
            field.style.borderColor = 'var(--gray-200)';
            icon.style.color = 'var(--gray-400)';
            if (field.type === 'password') {
                icon.className = 'fas fa-lock input-icon';
            } else {
                icon.className = 'fas fa-user input-icon';
            }
        }
    }
    
    // Gestion du clavier
    loginForm.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loginButton.click();
        }
    });
    
    // Animation des éléments flottants
    const floatingElements = document.querySelectorAll('.floating-element');
    floatingElements.forEach((element, index) => {
        element.style.animationDelay = `${index * 2}s`;
    });
    
    // Effet de particules au survol du bouton
    loginButton.addEventListener('mouseenter', function() {
        this.style.background = 'linear-gradient(135deg, var(--primary-dark), var(--primary-color))';
    });
    
    loginButton.addEventListener('mouseleave', function() {
        this.style.background = 'linear-gradient(135deg, var(--primary-color), var(--primary-light))';
    });
});

// Animation CSS pour l'erreur
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
        100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
    }
    
    .login-btn:focus {
        animation: pulse 1.5s infinite;
    }
`;
document.head.appendChild(style);
</script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>
