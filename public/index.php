<?php
/**
 * Page d'accueil simple avec options de diagnostic
 */

session_start();

$lang = $_GET['lang'] ?? 'ar';

// Rediriger si déjà connecté
if (isset($_SESSION['uid'])) {
    header('Location: dashboard.php?lang=' . $lang);
    exit;
}

// Si mode debug activé, afficher les options
$debug = $_GET['debug'] ?? false;

if (!$debug) {
    // Redirection normale vers la page de connexion simple
    header('Location: login.php?lang=' . $lang);
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'الصفحة الرئيسية' : 'Page d\'accueil' ?> - Library System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
            text-align: center;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        
        .logo {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .options {
            display: grid;
            gap: 20px;
            margin: 30px 0;
        }
        
        .option-link {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 20px;
            text-decoration: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .option-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .option-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .option-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .option-desc {
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">📚</div>
        <h1 class="title">
            <?= $lang == 'ar' ? ' النظام الإلكتروني للمكتبة\SEBIL-ENAJM' : 'SEBIL–ENAJM : Système Électronique de la Bibliothèque' ?>
        </h1>
        <p class="subtitle">
            <?= $lang == 'ar' ? 'اختر طريقة الدخول المناسبة' : 'Choisissez votre méthode de connexion' ?>
        </p>
        
        <div class="options">
            <a href="login.php?lang=<?= $lang ?>" class="option-link">
                <div class="option-icon">🔑</div>
                <div class="option-title"><?= $lang == 'ar' ? 'تسجيل دخول مبسط' : 'Connexion Simple' ?></div>
                <div class="option-desc"><?= $lang == 'ar' ? 'الطريقة الموصى بها' : 'Méthode recommandée' ?></div>
            </a>
            
            <a href="login.php?lang=<?= $lang ?>" class="option-link">
                <div class="option-icon">🎨</div>
                <div class="option-title"><?= $lang == 'ar' ? 'تسجيل دخول متقدم' : 'Connexion Avancée' ?></div>
                <div class="option-desc"><?= $lang == 'ar' ? 'مع التصميم المتقدم' : 'Avec design avancé' ?></div>
            </a>
            
            <a href="test-connection.php" class="option-link">
                <div class="option-icon">🧪</div>
                <div class="option-title"><?= $lang == 'ar' ? 'اختبار النظام' : 'Test du Système' ?></div>
                <div class="option-desc"><?= $lang == 'ar' ? 'للمطورين والاختبار' : 'Pour développeurs et tests' ?></div>
            </a>
            
            <a href="diagnostic.php" class="option-link">
                <div class="option-icon">🔧</div>
                <div class="option-title"><?= $lang == 'ar' ? 'تشخيص النظام' : 'Diagnostic' ?></div>
                <div class="option-desc"><?= $lang == 'ar' ? 'تحقق من حالة النظام' : 'Vérifier l\'état du système' ?></div>
            </a>
        </div>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.3);">
            <p style="font-size: 0.9rem; opacity: 0.7;">
                <?= $lang == 'ar' ? 'إذا واجهت مشاكل، استخدم التشخيص أولاً' : 'En cas de problème, utilisez le diagnostic d\'abord' ?>
            </p>
        </div>
    </div>
</body>
</html>