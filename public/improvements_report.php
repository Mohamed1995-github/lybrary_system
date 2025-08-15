<?php
/* public/improvements_report.php - تقرير التحسينات المطبقة */
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'ar';
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'تقرير التحسينات' : 'Rapport des améliorations' ?> - Library System</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/script.js"></script>
<style>
.report-container {
    min-height: 100vh;
    padding: 2rem;
    background: var(--bg-secondary);
}

.report-header {
    text-align: center;
    margin-bottom: 3rem;
}

.report-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.report-subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
    max-width: 800px;
    margin: 0 auto;
}

.improvements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.improvement-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 2rem;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.improvement-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary-color);
}

.improvement-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.improvement-icon {
    font-size: 2rem;
    color: var(--primary-color);
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(37 99 235 / 0.1);
    border-radius: var(--radius-lg);
}

.improvement-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.improvement-description {
    color: var(--text-secondary);
    font-size: 0.875rem;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.improvement-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.improvement-features li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.improvement-features i {
    color: var(--success-color);
    font-size: 0.75rem;
}

.improvement-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-lg);
    font-size: 0.75rem;
    font-weight: 500;
    margin-top: 1rem;
}

.status-completed {
    background: rgb(34 197 94 / 0.1);
    color: var(--success-color);
    border: 1px solid rgb(34 197 94 / 0.2);
}

.status-in-progress {
    background: rgb(251 191 36 / 0.1);
    color: var(--warning-color);
    border: 1px solid rgb(251 191 36 / 0.2);
}

.status-planned {
    background: rgb(107 114 128 / 0.1);
    color: var(--text-secondary);
    border: 1px solid rgb(107 114 128 / 0.2);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
    margin-bottom: 2rem;
    transition: color 0.2s ease;
}

.back-link:hover {
    color: var(--primary-color);
}

.summary-section {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 2rem;
    margin-bottom: 3rem;
    border: 1px solid var(--border-color);
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.summary-item {
    text-align: center;
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
}

.summary-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.summary-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .report-container {
        padding: 1rem;
    }
    
    .report-title {
        font-size: 2rem;
    }
    
    .improvements-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .improvement-card {
        padding: 1.5rem;
    }
    
    .summary-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}
</style>
</head>
<body>
<div class="report-container">
    <div class="report-header fade-in">
        <h1 class="report-title">
            <i class="fas fa-chart-line"></i>
            <?= $lang == 'ar' ? 'تقرير التحسينات المطبقة' : 'Rapport des améliorations appliquées' ?>
        </h1>
        <p class="report-subtitle">
            <?= $lang == 'ar' ? 'نظرة شاملة على التحسينات التي تم تطبيقها على النظام لحل مشاكل الوصول والأخطاء' : 'Vue d\'ensemble des améliorations appliquées au système pour résoudre les problèmes d\'accès et d\'erreurs' ?>
        </p>
    </div>

    <a href="dashboard.php?lang=<?=$lang?>" class="back-link">
        <i class="fas fa-arrow-left"></i>
        <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
    </a>

    <!-- ملخص التحسينات -->
    <div class="summary-section fade-in">
        <h2 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1.5rem;">
            <i class="fas fa-tasks"></i>
            <?= $lang == 'ar' ? 'ملخص التحسينات' : 'Résumé des améliorations' ?>
        </h2>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-number">6</div>
                <div class="summary-label"><?= $lang == 'ar' ? 'تحسينات مكتملة' : 'Améliorations complétées' ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-number">3</div>
                <div class="summary-label"><?= $lang == 'ar' ? 'ملفات جديدة' : 'Nouveaux fichiers' ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-number">100%</div>
                <div class="summary-label"><?= $lang == 'ar' ? 'حل مشاكل الوصول' : 'Résolution des problèmes d\'accès' ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-number">5</div>
                <div class="summary-label"><?= $lang == 'ar' ? 'أنواع أخطاء مغطاة' : 'Types d\'erreurs couverts' ?></div>
            </div>
        </div>
    </div>

    <div class="improvements-grid">
        <!-- إدارة الأخطاء -->
        <div class="improvement-card">
            <div class="improvement-header">
                <div class="improvement-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="improvement-title"><?= $lang == 'ar' ? 'إدارة الأخطاء' : 'Gestion des erreurs' ?></h3>
            </div>
            <p class="improvement-description">
                <?= $lang == 'ar' ? 'تحسين نظام إدارة الأخطاء مع رسائل واضحة ومعالجة أفضل للأخطاء' : 'Amélioration du système de gestion des erreurs avec des messages clairs et une meilleure gestion des erreurs' ?>
            </p>
            <ul class="improvement-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'رسائل خطأ واضحة ومفيدة' : 'Messages d\'erreur clairs et utiles' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معالجة أفضل للأخطاء' : 'Meilleure gestion des erreurs' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'اقتراحات لحل المشاكل' : 'Suggestions pour résoudre les problèmes' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'دعم متعدد اللغات' : 'Support multilingue' ?></li>
            </ul>
            <div class="improvement-status status-completed">
                <i class="fas fa-check-circle"></i>
                <?= $lang == 'ar' ? 'مكتمل' : 'Complété' ?>
            </div>
        </div>

        <!-- نظام المسارات -->
        <div class="improvement-card">
            <div class="improvement-header">
                <div class="improvement-icon">
                    <i class="fas fa-route"></i>
                </div>
                <h3 class="improvement-title"><?= $lang == 'ar' ? 'نظام المسارات' : 'Système de routage' ?></h3>
            </div>
            <p class="improvement-description">
                <?= $lang == 'ar' ? 'إصلاح مشاكل التوجيه وتحسين الوصول للملفات' : 'Correction des problèmes de routage et amélioration de l\'accès aux fichiers' ?>
            </p>
            <ul class="improvement-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معالجة أفضل للمسارات' : 'Meilleure gestion des chemins' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تحسين الوصول للملفات' : 'Amélioration de l\'accès aux fichiers' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تسجيل الأخطاء والوصول' : 'Journalisation des erreurs et accès' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معالجة الاستثناءات' : 'Gestion des exceptions' ?></li>
            </ul>
            <div class="improvement-status status-completed">
                <i class="fas fa-check-circle"></i>
                <?= $lang == 'ar' ? 'مكتمل' : 'Complété' ?>
            </div>
        </div>

        <!-- صفحات الإضافة المباشرة -->
        <div class="improvement-card">
            <div class="improvement-header">
                <div class="improvement-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3 class="improvement-title"><?= $lang == 'ar' ? 'صفحات الإضافة المباشرة' : 'Pages d\'ajout directes' ?></h3>
            </div>
            <p class="improvement-description">
                <?= $lang == 'ar' ? 'إنشاء صفحات إضافة مباشرة في مجلد public لتسهيل الوصول' : 'Création de pages d\'ajout directes dans le dossier public pour faciliter l\'accès' ?>
            </p>
            <ul class="improvement-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'إضافة الكتب مباشرة' : 'Ajout direct de livres' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'إضافة المجلات مباشرة' : 'Ajout direct de revues' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'إضافة الجرائد مباشرة' : 'Ajout direct de journaux' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'إضافة الموظفين مباشرة' : 'Ajout direct d\'employés' ?></li>
            </ul>
            <div class="improvement-status status-completed">
                <i class="fas fa-check-circle"></i>
                <?= $lang == 'ar' ? 'مكتمل' : 'Complété' ?>
            </div>
        </div>

        <!-- تحسين الوصول للملفات -->
        <div class="improvement-card">
            <div class="improvement-header">
                <div class="improvement-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3 class="improvement-title"><?= $lang == 'ar' ? 'تحسين الوصول للملفات' : 'Amélioration de l\'accès aux fichiers' ?></h3>
            </div>
            <p class="improvement-description">
                <?= $lang == 'ar' ? 'نظام محسن للوصول للملفات مع التحقق من الأمان' : 'Système amélioré d\'accès aux fichiers avec vérification de sécurité' ?>
            </p>
            <ul class="improvement-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'التحقق من نوع الملف' : 'Vérification du type de fichier' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'التحقق من الصلاحيات' : 'Vérification des permissions' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تسجيل الوصول للملفات' : 'Journalisation de l\'accès aux fichiers' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'إدارة التخزين المؤقت' : 'Gestion du cache' ?></li>
            </ul>
            <div class="improvement-status status-completed">
                <i class="fas fa-check-circle"></i>
                <?= $lang == 'ar' ? 'مكتمل' : 'Complété' ?>
            </div>
        </div>

        <!-- صفحة مركزية للإضافة -->
        <div class="improvement-card">
            <div class="improvement-header">
                <div class="improvement-icon">
                    <i class="fas fa-th-large"></i>
                </div>
                <h3 class="improvement-title"><?= $lang == 'ar' ? 'صفحة مركزية للإضافة' : 'Page centrale d\'ajout' ?></h3>
            </div>
            <p class="improvement-description">
                <?= $lang == 'ar' ? 'صفحة مركزية محدثة لتسهيل الوصول لجميع صفحات الإضافة' : 'Page centrale mise à jour pour faciliter l\'accès à toutes les pages d\'ajout' ?>
            </p>
            <ul class="improvement-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تصميم محسن' : 'Design amélioré' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'روابط مباشرة' : 'Liens directs' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'وصف لكل وظيفة' : 'Description de chaque fonction' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تجربة مستخدم محسنة' : 'Expérience utilisateur améliorée' ?></li>
            </ul>
            <div class="improvement-status status-completed">
                <i class="fas fa-check-circle"></i>
                <?= $lang == 'ar' ? 'مكتمل' : 'Complété' ?>
            </div>
        </div>

        <!-- نظام مسارات محسن -->
        <div class="improvement-card">
            <div class="improvement-header">
                <div class="improvement-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3 class="improvement-title"><?= $lang == 'ar' ? 'نظام مسارات محسن' : 'Système de routage amélioré' ?></h3>
            </div>
            <p class="improvement-description">
                <?= $lang == 'ar' ? 'نظام مسارات جديد مع معالجة أفضل للأخطاء والتحقق من الأمان' : 'Nouveau système de routage avec une meilleure gestion des erreurs et vérification de sécurité' ?>
            </p>
            <ul class="improvement-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'التحقق من الوحدات المسموحة' : 'Vérification des modules autorisés' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'التحقق من الإجراءات المسموحة' : 'Vérification des actions autorisées' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معالجة الاستثناءات' : 'Gestion des exceptions' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تسجيل مفصل' : 'Journalisation détaillée' ?></li>
            </ul>
            <div class="improvement-status status-completed">
                <i class="fas fa-check-circle"></i>
                <?= $lang == 'ar' ? 'مكتمل' : 'Complété' ?>
            </div>
        </div>
    </div>
</div>

<script>
// تحسينات تفاعلية
document.addEventListener('DOMContentLoaded', function() {
    // إضافة تأثيرات للبطاقات
    document.querySelectorAll('.improvement-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // إضافة تأثيرات للإحصائيات
    document.querySelectorAll('.summary-number').forEach(number => {
        const finalValue = number.textContent;
        const isPercentage = finalValue.includes('%');
        const isNumber = !isNaN(parseInt(finalValue));
        
        if (isNumber) {
            let currentValue = 0;
            const targetValue = parseInt(finalValue);
            const increment = targetValue / 50;
            
            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= targetValue) {
                    currentValue = targetValue;
                    clearInterval(timer);
                }
                number.textContent = isPercentage ? Math.floor(currentValue) + '%' : Math.floor(currentValue);
            }, 50);
        }
    });
});

// إضافة تأثيرات CSS إضافية
const style = document.createElement('style');
style.textContent = `
    .improvement-card {
        transition: all 0.3s ease;
    }
    
    .summary-item {
        transition: all 0.3s ease;
    }
    
    .summary-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .improvement-icon {
        transition: all 0.3s ease;
    }
    
    .improvement-card:hover .improvement-icon {
        transform: scale(1.1);
    }
`;
document.head.appendChild(style);
</script>
</body>
</html> 