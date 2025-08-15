<?php
/* public/add_pages.php - صفحة مركزية لجميع صفحات الإضافة */
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
<title><?= $lang == 'ar' ? 'صفحات الإضافة' : 'Pages d\'ajout' ?> - Library System</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/script.js"></script>
<style>
.page-container {
    min-height: 100vh;
    padding: 2rem;
    background: var(--bg-secondary);
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.page-subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 2rem;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    display: block;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary-color);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.card-icon {
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

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.card-description {
    color: var(--text-secondary);
    font-size: 0.875rem;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.card-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.card-features li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.card-features i {
    color: var(--success-color);
    font-size: 0.75rem;
}

.card-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
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

@media (max-width: 768px) {
    .page-container {
        padding: 1rem;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .cards-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .card {
        padding: 1.5rem;
    }
    
    .card-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>
<div class="page-container">
    <div class="page-header fade-in">
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i>
            <?= $lang == 'ar' ? 'صفحات الإضافة' : 'Pages d\'ajout' ?>
        </h1>
        <p class="page-subtitle">
            <?= $lang == 'ar' ? 'اختر نوع المادة التي تريد إضافتها إلى المكتبة' : 'Sélectionnez le type de matériau que vous souhaitez ajouter à la bibliothèque' ?>
        </p>
    </div>

    <a href="dashboard.php?lang=<?=$lang?>" class="back-link">
        <i class="fas fa-arrow-left"></i>
        <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
    </a>

    <div class="cards-grid">
        <!-- إضافة كتاب -->
        <a href="add_book.php?lang=<?=$lang?>" class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="card-title"><?= $lang == 'ar' ? 'إضافة كتاب' : 'Ajouter un livre' ?></h3>
            </div>
            <p class="card-description">
                <?= $lang == 'ar' ? 'إضافة كتاب جديد إلى المكتبة مع معلومات مفصلة مثل المؤلف والناشر والتصنيف' : 'Ajouter un nouveau livre à la bibliothèque avec des informations détaillées comme l\'auteur, l\'éditeur et la classification' ?>
            </p>
            <ul class="card-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات المؤلف والناشر' : 'Informations sur l\'auteur et l\'éditeur' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تصنيفات محددة' : 'Classifications spécifiques' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'رقم ISBN وتفاصيل الطبعة' : 'Numéro ISBN et détails d\'édition' ?></li>
            </ul>
            <div class="card-actions">
                <span class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة كتاب' : 'Ajouter un livre' ?>
                </span>
            </div>
        </a>

        <!-- إضافة مجلة -->
        <a href="add_magazine.php?lang=<?=$lang?>" class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-magazine"></i>
                </div>
                <h3 class="card-title"><?= $lang == 'ar' ? 'إضافة مجلة' : 'Ajouter une revue' ?></h3>
            </div>
            <p class="card-description">
                <?= $lang == 'ar' ? 'إضافة مجلة جديدة مع معلومات العدد والتاريخ والموضوعات' : 'Ajouter une nouvelle revue avec les informations du numéro, de la date et des sujets' ?>
            </p>
            <ul class="card-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات العدد والتاريخ' : 'Informations sur le numéro et la date' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'موضوعات المجلة' : 'Sujets de la revue' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تصنيف المجلات' : 'Classification des revues' ?></li>
            </ul>
            <div class="card-actions">
                <span class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة مجلة' : 'Ajouter une revue' ?>
                </span>
            </div>
        </a>

        <!-- إضافة جريدة -->
        <a href="add_newspaper.php?lang=<?=$lang?>" class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h3 class="card-title"><?= $lang == 'ar' ? 'إضافة جريدة' : 'Ajouter un journal' ?></h3>
            </div>
            <p class="card-description">
                <?= $lang == 'ar' ? 'إضافة جريدة جديدة مع معلومات العدد والتاريخ' : 'Ajouter un nouveau journal avec les informations du numéro et de la date' ?>
            </p>
            <ul class="card-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'جرائد محددة (الشعب، الجريدة الرسمية)' : 'Journaux spécifiques (Ech-Chaab, Journal Officiel)' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات العدد والتاريخ' : 'Informations sur le numéro et la date' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تصنيف الجرائد' : 'Classification des journaux' ?></li>
            </ul>
            <div class="card-actions">
                <span class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة جريدة' : 'Ajouter un journal' ?>
                </span>
            </div>
        </a>

        <!-- إضافة موظف -->
        <a href="add_employee.php?lang=<?=$lang?>" class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3 class="card-title"><?= $lang == 'ar' ? 'إضافة موظف' : 'Ajouter un employé' ?></h3>
            </div>
            <p class="card-description">
                <?= $lang == 'ar' ? 'إضافة موظف جديد مع تحديد الصلاحيات والصلاحيات المقيدة' : 'Ajouter un nouvel employé avec la définition des permissions et des permissions restreintes' ?>
            </p>
            <ul class="card-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات الموظف الشخصية' : 'Informations personnelles de l\'employé' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تحديد الصلاحيات' : 'Définition des permissions' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'الصلاحيات المقيدة' : 'Permissions restreintes' ?></li>
            </ul>
            <div class="card-actions">
                <span class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة موظف' : 'Ajouter un employé' ?>
                </span>
            </div>
        </a>

        <!-- إضافة مستعير -->
        <a href="router.php?module=borrowers&action=add&lang=<?=$lang?>" class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="card-title"><?= $lang == 'ar' ? 'إضافة مستعير' : 'Ajouter un emprunteur' ?></h3>
            </div>
            <p class="card-description">
                <?= $lang == 'ar' ? 'إضافة مستعير جديد مع معلومات الاتصال والتفاصيل الشخصية' : 'Ajouter un nouvel emprunteur avec les informations de contact et les détails personnels' ?>
            </p>
            <ul class="card-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات الاتصال' : 'Informations de contact' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'التفاصيل الشخصية' : 'Détails personnels' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'إدارة المستعيرين' : 'Gestion des emprunteurs' ?></li>
            </ul>
            <div class="card-actions">
                <span class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة مستعير' : 'Ajouter un emprunteur' ?>
                </span>
            </div>
        </a>

        <!-- إضافة استحواذ -->
        <a href="router.php?module=acquisitions&action=add&lang=<?=$lang?>" class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 class="card-title"><?= $lang == 'ar' ? 'إضافة استحواذ' : 'Ajouter une acquisition' ?></h3>
            </div>
            <p class="card-description">
                <?= $lang == 'ar' ? 'إضافة استحواذ جديد للمواد المكتبية مع معلومات المورد والتكلفة' : 'Ajouter une nouvelle acquisition de matériel de bibliothèque avec les informations du fournisseur et du coût' ?>
            </p>
            <ul class="card-features">
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'معلومات المورد' : 'Informations sur le fournisseur' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'التكلفة والتاريخ' : 'Coût et date' ?></li>
                <li><i class="fas fa-check"></i> <?= $lang == 'ar' ? 'تفاصيل الاستحواذ' : 'Détails de l\'acquisition' ?></li>
            </ul>
            <div class="card-actions">
                <span class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة استحواذ' : 'Ajouter une acquisition' ?>
                </span>
            </div>
        </a>
    </div>
</div>

<script>
// Add hover effects and animations
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// Add click tracking
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Add a small delay to show the click effect
        this.style.transform = 'scale(0.98)';
        setTimeout(() => {
            this.style.transform = 'translateY(-5px)';
        }, 150);
    });
});
</script>
</body>
</html> 