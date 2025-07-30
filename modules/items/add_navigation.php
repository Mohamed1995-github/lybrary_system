<?php
/* modules/items/add_navigation.php */
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$lang = $_GET['lang'] ?? 'ar';
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'إضافة مادة جديدة' : 'Ajouter un nouveau matériau' ?> - Library System</title>
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
    color: var(--primary-color);
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(37 99 235 / 0.1);
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

.navigation-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 2.5rem;
    border: 1px solid var(--border-color);
    max-width: 1000px;
    margin: 0 auto;
}

.navigation-header {
    text-align: center;
    margin-bottom: 3rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid var(--border-color);
}

.navigation-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.navigation-subtitle {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.item-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.item-type-card {
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-xl);
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: var(--text-primary);
    position: relative;
    overflow: hidden;
}

.item-type-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-color);
}

.item-type-card.book:hover {
    border-color: var(--primary-color);
    background: rgb(37 99 235 / 0.05);
}

.item-type-card.magazine:hover {
    border-color: var(--secondary-color);
    background: rgb(5 150 105 / 0.05);
}

.item-type-card.newspaper:hover {
    border-color: var(--warning-color);
    background: rgb(245 158 11 / 0.05);
}

.item-type-card.general:hover {
    border-color: var(--text-secondary);
    background: rgb(107 114 128 / 0.05);
}

.item-type-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    width: 5rem;
    height: 5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-lg);
    margin: 0 auto 1rem;
}

.item-type-card.book .item-type-icon {
    background: rgb(37 99 235 / 0.1);
    color: var(--primary-color);
}

.item-type-card.magazine .item-type-icon {
    background: rgb(5 150 105 / 0.1);
    color: var(--secondary-color);
}

.item-type-card.newspaper .item-type-icon {
    background: rgb(245 158 11 / 0.1);
    color: var(--warning-color);
}

.item-type-card.general .item-type-icon {
    background: rgb(107 114 128 / 0.1);
    color: var(--text-secondary);
}

.item-type-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.item-type-description {
    color: var(--text-secondary);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1.5rem;
}

.item-type-features {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
}

.item-type-features li {
    padding: 0.25rem 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.item-type-features li i {
    color: var(--success-color);
    font-size: 0.75rem;
}

.add-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: var(--primary-color);
    color: white;
    border-radius: var(--radius-lg);
    font-weight: 500;
    transition: all 0.2s ease;
    margin-top: 1rem;
}

.add-button:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.item-type-card.book .add-button {
    background: var(--primary-color);
}

.item-type-card.book .add-button:hover {
    background: var(--primary-hover);
}

.item-type-card.magazine .add-button {
    background: var(--secondary-color);
}

.item-type-card.magazine .add-button:hover {
    background: var(--secondary-hover);
}

.item-type-card.newspaper .add-button {
    background: var(--warning-color);
}

.item-type-card.newspaper .add-button:hover {
    background: #d97706;
}

.item-type-card.general .add-button {
    background: var(--text-secondary);
}

.item-type-card.general .add-button:hover {
    background: #6b7280;
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
    
    .navigation-card {
        padding: 1.5rem;
        margin: 0 1rem;
    }
    
    .item-types-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .item-type-card {
        padding: 1.5rem;
    }
}
</style>
</head>
<body>
<div class="page-container">
    <div class="page-header fade-in">
        <div class="page-title">
            <div class="page-icon">
                <i class="fas fa-plus"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'إضافة مادة جديدة' : 'Ajouter un nouveau matériau' ?></h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                    <?= $lang == 'ar' ? 'اختر نوع المادة التي تريد إضافتها' : 'Choisissez le type de matériau à ajouter' ?>
                </p>
            </div>
        </div>
        
        <div class="page-actions">
            <a href="../../public/dashboard.php" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
            </a>
        </div>
    </div>

    <div class="navigation-card fade-in">
        <div class="navigation-header">
            <h2 class="navigation-title">
                <i class="fas fa-th-large"></i>
                <?= $lang == 'ar' ? 'اختر نوع المادة' : 'Sélectionner le type de matériau' ?>
            </h2>
            <p class="navigation-subtitle">
                <?= $lang == 'ar' ? 'اختر نوع المادة المناسب من القائمة أدناه' : 'Sélectionnez le type de matériau approprié dans la liste ci-dessous' ?>
            </p>
        </div>

        <div class="item-types-grid">
            <!-- Books -->
            <a href="add_book.php?lang=<?=$lang?>" class="item-type-card book">
                <div class="item-type-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="item-type-title"><?= $lang == 'ar' ? 'كتاب' : 'Livre' ?></h3>
                <p class="item-type-description">
                    <?= $lang == 'ar' ? 'إضافة كتاب جديد مع تصنيفات متخصصة للعلوم والأدب والتاريخ وغيرها' : 'Ajouter un nouveau livre avec des catégories spécialisées pour les sciences, la littérature, l\'histoire, etc.' ?>
                </p>
                <ul class="item-type-features">
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تصنيفات متخصصة' : 'Catégories spécialisées' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات ISBN' : 'Informations ISBN' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تنسيقات متعددة' : 'Formats multiples' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات المؤلف والناشر' : 'Infos auteur et éditeur' ?></li>
                </ul>
                <div class="add-button">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة كتاب' : 'Ajouter un livre' ?>
                </div>
            </a>

            <!-- Magazines -->
            <a href="add_magazine.php?lang=<?=$lang?>" class="item-type-card magazine">
                <div class="item-type-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h3 class="item-type-title"><?= $lang == 'ar' ? 'مجلة' : 'Revue' ?></h3>
                <p class="item-type-description">
                    <?= $lang == 'ar' ? 'إضافة مجلة جديدة مع تصنيفات للعلوم والتقنية والأدب والطب وغيرها' : 'Ajouter une nouvelle revue avec des catégories pour les sciences, la technologie, la littérature, la médecine, etc.' ?>
                </p>
                <ul class="item-type-features">
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تصنيفات المجلات' : 'Catégories de revues' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات ISSN' : 'Informations ISSN' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تكرار النشر' : 'Fréquence de publication' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات المجلد والعدد' : 'Infos volume et numéro' ?></li>
                </ul>
                <div class="add-button">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة مجلة' : 'Ajouter une revue' ?>
                </div>
            </a>

            <!-- Newspapers -->
            <a href="add_newspaper.php?lang=<?=$lang?>" class="item-type-card newspaper">
                <div class="item-type-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h3 class="item-type-title"><?= $lang == 'ar' ? 'جريدة' : 'Journal' ?></h3>
                <p class="item-type-description">
                    <?= $lang == 'ar' ? 'إضافة جريدة جديدة مع تصنيفات للسياسة والاقتصاد والرياضة والثقافة' : 'Ajouter un nouveau journal avec des catégories pour la politique, l\'économie, le sport, la culture' ?>
                </p>
                <ul class="item-type-features">
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تصنيفات الجرائد' : 'Catégories de journaux' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات التوزيع' : 'Informations de tirage' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تكرار النشر' : 'Fréquence de publication' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'سنة التأسيس' : 'Année de fondation' ?></li>
                </ul>
                <div class="add-button">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة جريدة' : 'Ajouter un journal' ?>
                </div>
            </a>

            <!-- General Items -->
            <a href="add_edit.php?lang=<?=$lang?>&type=general" class="item-type-card general">
                <div class="item-type-icon">
                    <i class="fas fa-archive"></i>
                </div>
                <h3 class="item-type-title"><?= $lang == 'ar' ? 'مادة عامة' : 'Matériau général' ?></h3>
                <p class="item-type-description">
                    <?= $lang == 'ar' ? 'إضافة مادة عامة أخرى مثل الأقراص المدمجة أو المواد الرقمية' : 'Ajouter d\'autres matériaux généraux comme des CD ou des matériaux numériques' ?>
                </p>
                <ul class="item-type-features">
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تصنيفات عامة' : 'Catégories générales' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات أساسية' : 'Informations de base' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'مرونة في التصنيف' : 'Flexibilité de classification' ?></li>
                    <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'مناسب للمواد المتنوعة' : 'Adapté aux matériaux divers' ?></li>
                </ul>
                <div class="add-button">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة مادة عامة' : 'Ajouter un matériau général' ?>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
// Add hover effects and animations
document.querySelectorAll('.item-type-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});
</script>
</body>
</html> 