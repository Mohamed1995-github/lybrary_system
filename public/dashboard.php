<?php
/**
 * Dashboard Optimisé - Version 2.0
 * Architecture modulaire avec séparation des préoccupations
 * CSS et JavaScript externalisés pour une meilleure performance
 */

session_start();

// Vérifier la connexion
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

// Charger la configuration
$menuConfig = require __DIR__ . '/partials/dashboard/menu-config.php';

// Déterminer la langue
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'ar';
$texts = $menuConfig['menu_texts'][$lang];
$specializations = $menuConfig['specializations'];
$resourceTypes = $menuConfig['resource_types'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $texts['welcome'] ?>">
    <title><?= $lang == 'ar' ? 'لوحة التحكم' : 'Tableau de bord' ?> - Library System</title>
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles - Externalized -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <span class="logo-text">
                <?= $lang == 'ar' ? ' النظام الإلكتروني للمكتبة\SEBIL-ENAJM' : 'SEBIL–ENAJM : Système Électronique de la Bibliothèque' ?>
            </span>
        </div>
        
        <div class="user-info">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span class="user-name">
                    <?= $lang == 'ar' ? 'مرحباً' : 'Bonjour' ?>, 
                    <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
            </div>
            
            <a href="logout.php" class="btn btn-error">
                <i class="fas fa-sign-out-alt"></i>
                <?= $lang == 'ar' ? 'تسجيل الخروج' : 'Déconnexion' ?>
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Welcome Card -->
        <div class="welcome-card animate-slide-up">
            <h1 class="welcome-title"><?= $texts['welcome'] ?></h1>
            <p class="welcome-subtitle">
                <?= $lang == 'ar' ? ' ' : '' ?>
            </p>
        </div>

        <!-- Main Modules Grid -->
        <div class="modules-grid">
            <!-- Collections Section -->
            <div class="module-card" onclick="showResourcesMenu()">
                <div class="module-icon">📚</div>
                <h3 class="module-title"><?= $texts['collections_section'] ?></h3>
                <p class="module-desc">
                    <?= $lang == 'ar' ? 'كتب + مجلات + جرائد + بحوث' : 'Livres + Revues + Journaux + Recherches' ?>
                </p>
            </div>

            <!-- IT Section -->
            <div class="module-card" onclick="showITMenu()">
                <div class="module-icon">💻</div>
                <h3 class="module-title"><?= $texts['it_section'] ?></h3>
                <p class="module-desc">
                    <?= $lang == 'ar' ? 'تسجيل المصادر + الخدمات' : 'Enregistrement + Services' ?>
                </p>
            </div>

            <!-- Administration -->
            <a href="modules/administration/list.php?lang=<?= $lang ?>" class="module-card">
                <div class="module-icon">👤</div>
                <h3 class="module-title">
                    <?= $lang == 'ar' ? 'إدارة الموظفين' : 'Gestion des Employés' ?>
                </h3>
                <p class="module-desc">
                    <?= $lang == 'ar' ? 'إدارة بيانات الموظفين' : 'Gérer les données des employés' ?>
                </p>
            </a>

            <!-- Language Switch -->
            <a href="?lang=<?= $lang == 'ar' ? 'fr' : 'ar' ?>" class="module-card">
                <div class="module-icon">🌐</div>
                <h3 class="module-title">
                    <?= $lang == 'ar' ? 'تغيير اللغة' : 'Changer de Langue' ?>
                </h3>
                <p class="module-desc">
                    <?= $lang == 'ar' ? 'التبديل بين العربية والفرنسية' : 'Basculer entre arabe et français' ?>
                </p>
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">📚</div>
                <div class="stat-label"><?= $texts['system_ready'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number">⚡</div>
                <div class="stat-label"><?= $texts['optimized'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number">🔒</div>
                <div class="stat-label"><?= $texts['secure'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number">🌐</div>
                <div class="stat-label"><?= $texts['multilingual'] ?></div>
            </div>
        </div>
    </div>

    <!-- Modals Section -->
    <?php include __DIR__ . '/partials/dashboard/modals.php'; ?>

    <!-- Overlay -->
    <div id="menuOverlay" class="overlay"></div>

    <!-- JavaScript - Externalized -->
    <script src="assets/js/dashboard.js?v=<?= time() ?>"></script>
</body>
</html>
