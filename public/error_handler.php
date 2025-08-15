<?php
/* public/error_handler.php - نظام محسن لإدارة الأخطاء */
require_once __DIR__ . '/../includes/auth.php';

$lang = $_GET['lang'] ?? 'ar';
$error_code = $_GET['code'] ?? '404';
$error_message = $_GET['message'] ?? '';
$error_details = $_GET['details'] ?? '';

// تعريف رسائل الأخطاء
$error_messages = [
    '404' => [
        'ar' => [
            'title' => 'الصفحة غير موجودة',
            'message' => 'عذراً، الصفحة التي تبحث عنها غير موجودة.',
            'description' => 'يرجى التحقق من الرابط أو العودة للصفحة الرئيسية.',
            'icon' => 'fas fa-search'
        ],
        'fr' => [
            'title' => 'Page non trouvée',
            'message' => 'Désolé, la page que vous recherchez n\'existe pas.',
            'description' => 'Veuillez vérifier le lien ou retourner à la page d\'accueil.',
            'icon' => 'fas fa-search'
        ]
    ],
    '403' => [
        'ar' => [
            'title' => 'غير مصرح بالوصول',
            'message' => 'عذراً، ليس لديك صلاحية للوصول إلى هذه الصفحة.',
            'description' => 'يرجى الاتصال بالمسؤول إذا كنت تعتقد أن هذا خطأ.',
            'icon' => 'fas fa-lock'
        ],
        'fr' => [
            'title' => 'Accès non autorisé',
            'message' => 'Désolé, vous n\'avez pas l\'autorisation d\'accéder à cette page.',
            'description' => 'Veuillez contacter l\'administrateur si vous pensez qu\'il s\'agit d\'une erreur.',
            'icon' => 'fas fa-lock'
        ]
    ],
    '500' => [
        'ar' => [
            'title' => 'خطأ في الخادم',
            'message' => 'عذراً، حدث خطأ في الخادم.',
            'description' => 'يرجى المحاولة مرة أخرى لاحقاً أو الاتصال بالمسؤول.',
            'icon' => 'fas fa-exclamation-triangle'
        ],
        'fr' => [
            'title' => 'Erreur serveur',
            'message' => 'Désolé, une erreur s\'est produite sur le serveur.',
            'description' => 'Veuillez réessayer plus tard ou contacter l\'administrateur.',
            'icon' => 'fas fa-exclamation-triangle'
        ]
    ],
    'database' => [
        'ar' => [
            'title' => 'خطأ في قاعدة البيانات',
            'message' => 'عذراً، حدث خطأ في الاتصال بقاعدة البيانات.',
            'description' => 'يرجى المحاولة مرة أخرى أو الاتصال بالمسؤول.',
            'icon' => 'fas fa-database'
        ],
        'fr' => [
            'title' => 'Erreur de base de données',
            'message' => 'Désolé, une erreur s\'est produite lors de la connexion à la base de données.',
            'description' => 'Veuillez réessayer ou contacter l\'administrateur.',
            'icon' => 'fas fa-database'
        ]
    ],
    'session' => [
        'ar' => [
            'title' => 'انتهت صلاحية الجلسة',
            'message' => 'عذراً، انتهت صلاحية جلسة العمل الخاصة بك.',
            'description' => 'يرجى تسجيل الدخول مرة أخرى.',
            'icon' => 'fas fa-clock'
        ],
        'fr' => [
            'title' => 'Session expirée',
            'message' => 'Désolé, votre session a expiré.',
            'description' => 'Veuillez vous reconnecter.',
            'icon' => 'fas fa-clock'
        ]
    ],
    'validation' => [
        'ar' => [
            'title' => 'خطأ في التحقق',
            'message' => 'عذراً، البيانات المدخلة غير صحيحة.',
            'description' => 'يرجى التحقق من البيانات وإعادة المحاولة.',
            'icon' => 'fas fa-check-circle'
        ],
        'fr' => [
            'title' => 'Erreur de validation',
            'message' => 'Désolé, les données saisies sont incorrectes.',
            'description' => 'Veuillez vérifier les données et réessayer.',
            'icon' => 'fas fa-check-circle'
        ]
    ]
];

// الحصول على رسالة الخطأ المناسبة
$current_error = $error_messages[$error_code] ?? $error_messages['404'];
$error_info = $current_error[$lang];

// إذا كانت هناك رسالة مخصصة
if (!empty($error_message)) {
    $error_info['message'] = $error_message;
}

// إذا كانت هناك تفاصيل إضافية
if (!empty($error_details)) {
    $error_info['description'] = $error_details;
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $error_info['title'] ?> - Library System</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/script.js"></script>
<style>
.error-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: var(--bg-secondary);
}

.error-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    padding: 3rem;
    text-align: center;
    max-width: 500px;
    width: 100%;
    border: 1px solid var(--border-color);
    position: relative;
    overflow: hidden;
}

.error-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--error-color), var(--warning-color));
}

.error-icon {
    font-size: 4rem;
    color: var(--error-color);
    margin-bottom: 1.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.error-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.error-message {
    font-size: 1.1rem;
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.error-description {
    color: var(--text-light);
    font-size: 0.875rem;
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
    padding: 0.875rem 1.5rem;
    text-decoration: none;
    border-radius: var(--radius-lg);
    font-weight: 500;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
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
    text-align: left;
}

.error-details h4 {
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.error-details p {
    color: var(--text-secondary);
    font-size: 0.75rem;
    margin: 0;
    font-family: monospace;
}

.error-code {
    background: var(--bg-tertiary);
    color: var(--text-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 1rem;
    display: inline-block;
}

@media (max-width: 768px) {
    .error-container {
        padding: 1rem;
    }
    
    .error-card {
        padding: 2rem;
    }
    
    .error-title {
        font-size: 1.5rem;
    }
    
    .error-message {
        font-size: 1rem;
    }
    
    .error-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

/* تحسينات إضافية */
.error-card {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.error-suggestions {
    margin-top: 2rem;
    padding: 1.5rem;
    background: rgb(37 99 235 / 0.05);
    border-radius: var(--radius-lg);
    border: 1px solid rgb(37 99 235 / 0.1);
}

.error-suggestions h4 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    font-size: 1rem;
    font-weight: 600;
}

.error-suggestions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.error-suggestions li {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.error-suggestions i {
    color: var(--primary-color);
    font-size: 0.75rem;
}
</style>
</head>
<body>
<div class="error-container">
    <div class="error-card">
        <div class="error-icon">
            <i class="<?= $error_info['icon'] ?>"></i>
        </div>
        
        <div class="error-code">
            <?= $lang == 'ar' ? 'رمز الخطأ:' : 'Code d\'erreur:' ?> <?= strtoupper($error_code) ?>
        </div>
        
        <h1 class="error-title"><?= $error_info['title'] ?></h1>
        
        <p class="error-message"><?= $error_info['message'] ?></p>
        
        <p class="error-description"><?= $error_info['description'] ?></p>
        
        <div class="error-actions">
            <a href="dashboard.php?lang=<?=$lang?>" class="btn btn-primary">
                <i class="fas fa-home"></i>
                <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
            </a>
            
            <a href="add_pages.php?lang=<?=$lang?>" class="btn btn-secondary">
                <i class="fas fa-plus"></i>
                <?= $lang == 'ar' ? 'صفحات الإضافة' : 'Pages d\'ajout' ?>
            </a>
        </div>
        
        <?php if ($error_code === '404'): ?>
        <div class="error-suggestions">
            <h4>
                <i class="fas fa-lightbulb"></i>
                <?= $lang == 'ar' ? 'اقتراحات مفيدة:' : 'Suggestions utiles:' ?>
            </h4>
            <ul>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تأكد من صحة الرابط' : 'Vérifiez l\'exactitude du lien' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'استخدم الروابط في القائمة' : 'Utilisez les liens dans le menu' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'عد للصفحة السابقة' : 'Retournez à la page précédente' ?></li>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if ($error_code === '403'): ?>
        <div class="error-suggestions">
            <h4>
                <i class="fas fa-shield-alt"></i>
                <?= $lang == 'ar' ? 'حلول مقترحة:' : 'Solutions suggérées:' ?>
            </h4>
            <ul>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تسجيل الدخول مرة أخرى' : 'Reconnectez-vous' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'اتصل بالمسؤول' : 'Contactez l\'administrateur' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تحقق من الصلاحيات' : 'Vérifiez les permissions' ?></li>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['debug']) && $_SESSION['debug']): ?>
        <div class="error-details">
            <h4><?= $lang == 'ar' ? 'تفاصيل تقنية:' : 'Détails techniques:' ?></h4>
            <p><strong><?= $lang == 'ar' ? 'الرابط:' : 'URL:' ?></strong> <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?></p>
            <p><strong><?= $lang == 'ar' ? 'المستخدم:' : 'Utilisateur:' ?></strong> <?= htmlspecialchars($_SESSION['uid'] ?? 'غير مسجل') ?></p>
            <p><strong><?= $lang == 'ar' ? 'الوقت:' : 'Heure:' ?></strong> <?= date('Y-m-d H:i:s') ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// تحسين تجربة المستخدم
document.addEventListener('DOMContentLoaded', function() {
    // إضافة تأثيرات تفاعلية
    const errorCard = document.querySelector('.error-card');
    
    errorCard.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
    });
    
    errorCard.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
    
    // إضافة تأثير النقر على الأزرار
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
    
    // إضافة تأثير التحميل
    const primaryBtn = document.querySelector('.btn-primary');
    if (primaryBtn) {
        primaryBtn.addEventListener('click', function(e) {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang == 'ar' ? 'جاري التحميل...' : 'Chargement...' ?>';
            this.style.pointerEvents = 'none';
        });
    }
});

// إضافة تأثيرات CSS إضافية
const style = document.createElement('style');
style.textContent = `
    .error-card {
        transition: all 0.3s ease;
    }
    
    .btn {
        transition: all 0.2s ease;
    }
    
    .error-icon {
        transition: all 0.3s ease;
    }
    
    .error-card:hover .error-icon {
        transform: scale(1.1);
    }
`;
document.head.appendChild(style);
</script>
</body>
</html> 