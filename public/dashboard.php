<?php
require_once __DIR__.'/../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: login.php'); exit; }

if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'fr'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'ar';
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'لوحة التحكم' : 'Tableau de bord' ?> - Library System</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.dashboard-container {
    min-height: 100vh;
    padding: 2rem;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 3rem;
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.dashboard-subtitle {
    color: var(--text-secondary);
    font-size: 1.125rem;
}

.nav-bar {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
}

.nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: var(--bg-primary);
    color: var(--text-primary);
    text-decoration: none;
    border-radius: var(--radius-lg);
    font-weight: 500;
    transition: all 0.2s ease;
    border: 2px solid var(--border-color);
}

.nav-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.nav-btn.logout {
    background: var(--error-color);
    color: white;
    border-color: var(--error-color);
}

.nav-btn.logout:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.section-tabs {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
}

.section-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: var(--radius-lg);
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
    min-width: 180px;
    justify-content: center;
}

.section-tab:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
    transform: translateY(-2px);
}

.section-tab.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    box-shadow: var(--shadow-md);
}

.section-tab.active:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.section-content {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}

.section-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.content-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    padding: 2rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.content-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.content-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.content-icon {
    font-size: 1.5rem;
    color: var(--primary-color);
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(37 99 235 / 0.1);
    border-radius: var(--radius-lg);
}

.content-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.content-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.content-list li {
    margin-bottom: 0.75rem;
}

.content-list a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
    font-weight: 500;
}

.content-list a:hover {
    background: var(--bg-secondary);
    color: var(--primary-color);
    transform: translateX(4px);
}

.content-list i {
    color: var(--primary-color);
    width: 1rem;
}

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: var(--radius-lg);
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.2s ease;
    min-width: 200px;
    justify-content: center;
}

.action-btn:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.action-btn.acquisition {
    background: var(--accent-color);
}

.action-btn.acquisition:hover {
    background: var(--accent-hover);
}

.action-btn.borrowers {
    background: #059669;
}

.action-btn.borrowers:hover {
    background: #047857;
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }
    
    .dashboard-title {
        font-size: 2rem;
    }
    
    .nav-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .nav-btn {
        justify-content: center;
    }
    
    .section-tabs {
        flex-direction: column;
        align-items: stretch;
    }
    
    .section-tab {
        width: 100%;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-btn {
        width: 100%;
    }
}
</style>
</head>
<body>
<div class="dashboard-container">
    <div class="dashboard-header fade-in">
        <h1 class="dashboard-title">
            <i class="fas fa-book-open"></i>
            <?= $lang == 'ar' ? 'نظام إدارة المكتبة' : 'Système de Gestion de Bibliothèque' ?>
        </h1>
        <p class="dashboard-subtitle">
            <?= $lang == 'ar' ? 'مرحباً بك في لوحة التحكم' : 'Bienvenue dans votre tableau de bord' ?>
        </p>
    </div>

    <nav class="nav-bar">
        <a href="dashboard.php?lang=ar" class="nav-btn <?= $lang == 'ar' ? 'active' : '' ?>">
            <i class="fas fa-language"></i>
            🇸🇦 
        </a>
        <a href="dashboard.php?lang=fr" class="nav-btn <?= $lang == 'fr' ? 'active' : '' ?>">
            <i class="fas fa-language"></i>
            🇫🇷 
        </a>
        <a href="logout.php" class="nav-btn logout">
            <i class="fas fa-sign-out-alt"></i>
            <?= $lang == 'ar' ? 'تسجيل الخروج' : 'Déconnexion' ?>
        </a>
    </nav>

    <div class="section-tabs">
        <button class="section-tab active" data-section="arabic">
            <i class="fas fa-book"></i>
            <?= $lang == 'ar' ? 'القسم العربي' : 'Section Arabe' ?>
        </button>
        <button class="section-tab" data-section="french">
            <i class="fas fa-book"></i>
            <?= $lang == 'ar' ? 'القسم الفرنسي' : 'Section Française' ?>
        </button>
        <button class="section-tab" data-section="loans">
            <i class="fas fa-handshake"></i>
            <?= $lang == 'ar' ? 'قسم الإعارة' : 'Section Emprunts' ?>
        </button>
                    <button class="section-tab" data-section="acquisitions">
                <i class="fas fa-truck"></i>
                <?= $lang == 'ar' ? 'إدارة التزويد' : 'Gestion des Approvisionnements' ?>
            </button>
            <button class="section-tab" data-section="administration">
                <i class="fas fa-users-cog"></i>
                <?= $lang == 'ar' ? 'الإدارة' : 'Administration' ?>
            </button>
    </div>

    <!-- Arabic Section -->
    <div id="arabic" class="section-content active">
        <div class="content-grid">
            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'الكتب العربية' : 'Livres Arabes' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=items&action=list&lang=ar&type=book">
                            <i class="fas fa-book"></i>
                            <?= $lang == 'ar' ? 'عرض الكتب' : 'Voir les livres' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=items&action=add_edit&lang=ar&type=book">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة كتاب جديد' : 'Ajouter un livre' ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'المجلات العربية' : 'Magazines Arabes' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=items&action=list&lang=ar&type=magazine">
                            <i class="fas fa-newspaper"></i>
                            <?= $lang == 'ar' ? 'عرض المجلات' : 'Voir les magazines' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=items&action=add_edit&lang=ar&type=magazine">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة مجلة جديدة' : 'Ajouter un magazine' ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'الصحف العربية' : 'Journaux Arabes' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=items&action=list&lang=ar&type=newspaper">
                            <i class="fas fa-newspaper"></i>
                            <?= $lang == 'ar' ? 'عرض الصحف' : 'Voir les journaux' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=items&action=add_edit&lang=ar&type=newspaper">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة صحيفة جديدة' : 'Ajouter un journal' ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-archive"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'المواد العامة' : 'Matériaux Généraux' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=items&action=list&lang=ar&type=general">
                            <i class="fas fa-archive"></i>
                            <?= $lang == 'ar' ? 'عرض المواد العامة' : 'Voir les matériaux généraux' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=items&action=add_edit&lang=ar&type=general">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة مادة عامة' : 'Ajouter un matériau' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- French Section -->
    <div id="french" class="section-content">
        <div class="content-grid">
            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'الكتب الفرنسية' : 'Livres Français' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=items&action=list&lang=fr&type=book">
                            <i class="fas fa-book"></i>
                            <?= $lang == 'ar' ? 'عرض الكتب' : 'Voir les livres' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=items&action=add_edit&lang=fr&type=book">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة كتاب جديد' : 'Ajouter un livre' ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'المجلات الفرنسية' : 'Magazines Français' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=items&action=list&lang=fr&type=magazine">
                            <i class="fas fa-newspaper"></i>
                            <?= $lang == 'ar' ? 'عرض المجلات' : 'Voir les magazines' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=items&action=add_edit&lang=fr&type=magazine">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة مجلة جديدة' : 'Ajouter un magazine' ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'الصحف الفرنسية' : 'Journaux Français' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=items&action=list&lang=fr&type=newspaper">
                            <i class="fas fa-newspaper"></i>
                            <?= $lang == 'ar' ? 'عرض الصحف' : 'Voir les journaux' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=items&action=add_edit&lang=fr&type=newspaper">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة صحيفة جديدة' : 'Ajouter un journal' ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-archive"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'المواد العامة' : 'Matériaux Généraux' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=items&action=list&lang=fr&type=general">
                            <i class="fas fa-archive"></i>
                            <?= $lang == 'ar' ? 'عرض المواد العامة' : 'Voir les matériaux généraux' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=items&action=add_edit&lang=fr&type=general">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة مادة عامة' : 'Ajouter un matériau' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Loans Section -->
    <div id="loans" class="section-content">
        <div class="content-grid">
            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'إدارة الإعارة' : 'Gestion des Emprunts' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=loans&action=list">
                            <i class="fas fa-list"></i>
                            <?= $lang == 'ar' ? 'عرض جميع الإعارات' : 'Voir tous les emprunts' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=loans&action=borrow">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إعارة جديدة' : 'Nouvel emprunt' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=loans&action=return">
                            <i class="fas fa-undo"></i>
                            <?= $lang == 'ar' ? 'إرجاع مادة' : 'Retourner un matériau' ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'إدارة المستعيرين' : 'Gestion des Emprunteurs' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=borrowers&action=list">
                            <i class="fas fa-list"></i>
                            <?= $lang == 'ar' ? 'عرض المستعيرين' : 'Voir les emprunteurs' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=borrowers&action=add">
                            <i class="fas fa-user-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة مستعير جديد' : 'Ajouter un emprunteur' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="action-buttons">
            <a href="router.php?module=loans&action=borrow" class="action-btn">
                <i class="fas fa-handshake"></i>
                <?= $lang == 'ar' ? 'إعارة جديدة' : 'Nouvel emprunt' ?>
            </a>
            <a href="router.php?module=borrowers&action=add" class="action-btn borrowers">
                <i class="fas fa-user-plus"></i>
                <?= $lang == 'ar' ? 'إضافة مستعير' : 'Nouvel emprunteur' ?>
            </a>
        </div>
    </div>

    <!-- Acquisitions Section -->
    <div id="acquisitions" class="section-content">
        <div class="content-grid">
            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'إدارة التزويد' : 'Gestion des Approvisionnements' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=acquisitions&action=list">
                            <i class="fas fa-list"></i>
                            <?= $lang == 'ar' ? 'عرض التزويد' : 'Voir les approvisionnements' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=acquisitions&action=add">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة مشترى جديد' : 'Ajouter une acquisition' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="action-buttons">
            <a href="router.php?module=acquisitions&action=add" class="action-btn acquisition">
                <i class="fas fa-truck"></i>
                <?= $lang == 'ar' ? 'إضافة تزويد جديد' : 'Nouvel approvisionnement' ?>
            </a>
        </div>
    </div>

    <!-- Administration Section -->
    <div id="administration" class="section-content">
        <div class="content-grid">
            <div class="content-card fade-in">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h2 class="content-title"><?= $lang == 'ar' ? 'الإدارة' : 'Administration' ?></h2>
                </div>
                <ul class="content-list">
                    <li>
                        <a href="router.php?module=administration&action=list">
                            <i class="fas fa-users"></i>
                            <?= $lang == 'ar' ? 'قائمة الموظفين' : 'Liste des employés' ?>
                        </a>
                    </li>
                    <li>
                        <a href="router.php?module=administration&action=add">
                            <i class="fas fa-user-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un employé' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="action-buttons">
            <a href="router.php?module=administration&action=add" class="action-btn administration">
                <i class="fas fa-user-plus"></i>
                <?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un employé' ?>
            </a>
        </div>
    </div>
</div>

<script>
// Section switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const sectionTabs = document.querySelectorAll('.section-tab');
    const sectionContents = document.querySelectorAll('.section-content');

    sectionTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetSection = this.getAttribute('data-section');
            
            // Remove active class from all tabs and contents
            sectionTabs.forEach(t => t.classList.remove('active'));
            sectionContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetSection).classList.add('active');
        });
    });
});
</script>
</body>
</html>
