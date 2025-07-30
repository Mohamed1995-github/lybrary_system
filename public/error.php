<?php
/**
 * Page d'erreur pour gérer les accès non autorisés et autres erreurs
 * Error page to handle unauthorized access and other errors
 */

$error_code = $_GET['code'] ?? '404';
$lang = $_GET['lang'] ?? 'ar';

// Définir les messages d'erreur selon la langue
$error_messages = [
    '403' => [
        'ar' => [
            'title' => 'الوصول مرفوض',
            'message' => 'عذراً، ليس لديك صلاحية للوصول إلى هذه الصفحة.',
            'description' => 'يرجى التواصل مع المسؤول إذا كنت تعتقد أن هذا خطأ.',
            'back' => 'العودة للوحة التحكم'
        ],
        'fr' => [
            'title' => 'Accès Refusé',
            'message' => 'Désolé, vous n\'avez pas la permission d\'accéder à cette page.',
            'description' => 'Veuillez contacter l\'administrateur si vous pensez qu\'il s\'agit d\'une erreur.',
            'back' => 'Retour au tableau de bord'
        ]
    ],
    '404' => [
        'ar' => [
            'title' => 'الصفحة غير موجودة',
            'message' => 'عذراً، الصفحة التي تبحث عنها غير موجودة.',
            'description' => 'يرجى التحقق من الرابط أو العودة للصفحة الرئيسية.',
            'back' => 'العودة للوحة التحكم'
        ],
        'fr' => [
            'title' => 'Page Non Trouvée',
            'message' => 'Désolé, la page que vous recherchez n\'existe pas.',
            'description' => 'Veuillez vérifier le lien ou retourner à la page d\'accueil.',
            'back' => 'Retour au tableau de bord'
        ]
    ],
    '500' => [
        'ar' => [
            'title' => 'خطأ في الخادم',
            'message' => 'عذراً، حدث خطأ في الخادم.',
            'description' => 'يرجى المحاولة مرة أخرى لاحقاً أو التواصل مع المسؤول.',
            'back' => 'العودة للوحة التحكم'
        ],
        'fr' => [
            'title' => 'Erreur Serveur',
            'message' => 'Désolé, une erreur serveur s\'est produite.',
            'description' => 'Veuillez réessayer plus tard ou contacter l\'administrateur.',
            'back' => 'Retour au tableau de bord'
        ]
    ]
];

// Obtenir le message d'erreur approprié
$error = $error_messages[$error_code] ?? $error_messages['404'];
$error_info = $error[$lang] ?? $error['ar'];
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $error_info['title'] ?> - Système de Bibliothèque</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            line-height: 1;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 1rem 0;
        }

        .error-message {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .error-description {
            font-size: 0.9rem;
            color: var(--text-tertiary);
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
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

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
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

        .error-details {
            margin-top: 2rem;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            font-size: 0.8rem;
            color: var(--text-tertiary);
        }

        .error-details strong {
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .error-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .error-code {
                font-size: 4rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .error-actions {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <?php if ($error_code == '403'): ?>
                <i class="fas fa-ban" style="color: var(--error-color);"></i>
            <?php elseif ($error_code == '404'): ?>
                <i class="fas fa-search" style="color: var(--warning-color);"></i>
            <?php else: ?>
                <i class="fas fa-exclamation-triangle" style="color: var(--error-color);"></i>
            <?php endif; ?>
        </div>

        <h1 class="error-code"><?= $error_code ?></h1>
        <h2 class="error-title"><?= $error_info['title'] ?></h2>
        
        <p class="error-message"><?= $error_info['message'] ?></p>
        <p class="error-description"><?= $error_info['description'] ?></p>

        <div class="error-actions">
            <a href="dashboard.php?lang=<?=$lang?>" class="btn btn-primary">
                <i class="fas fa-home"></i>
                <?= $error_info['back'] ?>
            </a>
            
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة للصفحة السابقة' : 'Page précédente' ?>
            </a>
        </div>

        <?php if (isset($_SESSION['uid'])): ?>
            <div class="error-details">
                <strong><?= $lang == 'ar' ? 'معلومات إضافية:' : 'Informations supplémentaires:' ?></strong><br>
                <?= $lang == 'ar' ? 'رمز الخطأ:' : 'Code d\'erreur:' ?> <?= $error_code ?><br>
                <?= $lang == 'ar' ? 'المستخدم:' : 'Utilisateur:' ?> <?= $_SESSION['uid'] ?><br>
                <?= $lang == 'ar' ? 'الوقت:' : 'Heure:' ?> <?= date('Y-m-d H:i:s') ?><br>
                <?= $lang == 'ar' ? 'العنوان:' : 'URL:' ?> <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Log de l'erreur côté client
        console.log('Erreur <?= $error_code ?>: <?= $error_info['title'] ?>');
        
        // Redirection automatique après 30 secondes (optionnel)
        setTimeout(function() {
            window.location.href = 'dashboard.php?lang=<?=$lang?>';
        }, 30000);
    </script>
</body>
</html> 