<?php
/**
 * مركز إضافة المصادر - صفحة مركزية لجميع أنواع الإضافات
 */

session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) {
    header('Location: ../../login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'ar';

// قائمة جميع صفحات الإضافة المتاحة
$add_options = [
    'books' => [
        'ar' => [
            'title' => 'إضافة الكتب',
            'desc' => 'إضافة كتب جديدة للمكتبة',
            'options' => [
                ['file' => 'add_book.php', 'title' => 'إضافة كتاب متخصص', 'desc' => 'كتب أكاديمية ومتخصصة', 'icon' => '📖'],
                ['file' => 'add_general_book.php', 'title' => 'إضافة كتاب عام', 'desc' => 'كتب عامة وثقافية', 'icon' => '📖'],
            ]
        ],
        'fr' => [
            'title' => 'Ajouter des Livres',
            'desc' => 'Ajouter de nouveaux livres à la bibliothèque',
            'options' => [
                ['file' => 'add_book.php', 'title' => 'Ajouter Livre Spécialisé', 'desc' => 'Livres académiques et spécialisés', 'icon' => '📖'],
                ['file' => 'add_general_book.php', 'title' => 'Ajouter Livre Général', 'desc' => 'Livres généraux et culturels', 'icon' => '📖'],
            ]
        ]
    ],
    'magazines' => [
        'ar' => [
            'title' => 'إضافة المجلات',
            'desc' => 'إضافة مجلات جديدة للمكتبة',
            'options' => [
                ['file' => 'add_magazine.php', 'title' => 'إضافة مجلة متخصصة', 'desc' => 'مجلات أكاديمية ومتخصصة', 'icon' => '📰'],
                ['file' => 'add_general_magazine.php', 'title' => 'إضافة مجلة عامة', 'desc' => 'مجلات عامة وثقافية', 'icon' => '📰']
            ]
        ],
        'fr' => [
            'title' => 'Ajouter des Revues',
            'desc' => 'Ajouter de nouvelles revues à la bibliothèque',
            'options' => [
                ['file' => 'add_magazine.php', 'title' => 'Ajouter Revue Spécialisée', 'desc' => 'Revues académiques et spécialisées', 'icon' => '📰'],
                ['file' => 'add_general_magazine.php', 'title' => 'Ajouter Revue Générale', 'desc' => 'Revues générales et culturelles', 'icon' => '📰']
            ]
        ]
    ],
    'others' => [
        'ar' => [
            'title' => 'مصادر أخرى',
            'desc' => 'إضافة أنواع أخرى من المصادر',
            'options' => [
                ['file' => 'add_newspaper.php', 'title' => 'إضافة جريدة', 'desc' => 'جرائد ومنشورات', 'icon' => '🗞️'],
                ['file' => 'addrecherche.php', 'title' => 'إضافة بحث', 'desc' => 'بحوث وأطروحات', 'icon' => '🎓']
            ]
        ],
        'fr' => [
            'title' => 'Autres Ressources',
            'desc' => 'Ajouter d\'autres types de ressources',
            'options' => [
                ['file' => 'add_newspaper.php', 'title' => 'Ajouter Journal', 'desc' => 'Journaux et publications', 'icon' => '🗞️'],
                ['file' => 'addrecherche.php', 'title' => 'Ajouter Recherche', 'desc' => 'Recherches et thèses', 'icon' => '🎓']
            ]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'مركز إضافة المصادر' : 'Centre d\'Ajout des Ressources' ?> - نظام المكتبة</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --white: #ffffff;
            --success: #22c55e;
            --warning: #f59e0b;
            --info: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            min-height: 100vh;
            color: var(--gray-800);
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            padding: 3rem 2rem;
            text-align: center;
            color: var(--white);
        }

        .header-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .nav-section {
            background: var(--white);
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--gray-200);
            color: var(--gray-600);
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .back-btn:hover {
            background: var(--gray-300);
            transform: translateY(-1px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .section {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-200);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .section-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.8rem;
        }

        .section-info h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .section-info p {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .option-card {
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            padding: 1.5rem;
            text-decoration: none;
            color: inherit;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .option-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .option-card:hover {
            background: var(--white);
            border-color: var(--primary-color);
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.15);
        }

        .option-card:hover::before {
            transform: scaleY(1);
        }

        .option-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .option-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .option-desc {
            font-size: 0.875rem;
            color: var(--gray-500);
            line-height: 1.4;
        }

        .quick-stats {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #fde68a;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            text-align: center;
        }

        .stat-item {
            color: #92400e;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #b45309;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .nav-section {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .options-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section">
        <div class="header-icon">📋</div>
        <h1 class="header-title"><?= $lang == 'ar' ? 'مركز إضافة المصادر' : 'Centre d\'Ajout des Ressources' ?></h1>
        <p class="header-subtitle"><?= $lang == 'ar' ? 'إضافة وتسجيل المصادر الجديدة' : 'Ajout et enregistrement de nouvelles ressources' ?></p>
    </div>

    <!-- Navigation -->
    <div class="nav-section">
        <a href="../../dashboard-fixed.php?lang=<?=$lang?>" class="back-btn">
            <i class="fas fa-arrow-right"></i>
            <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
        </a>
        
        <div style="display: flex; align-items: center; gap: 1rem; color: var(--gray-600); font-weight: 500;">
            <i class="fas fa-plus"></i>
            <span><?= $lang == 'ar' ? 'اختر نوع المصدر لإضافته' : 'Choisissez le type de ressource à ajouter' ?></span>
        </div>
    </div>

    <div class="container">
        <!-- إحصائيات سريعة -->
        <?php
        try {
            require_once __DIR__ . '/../../../config/db.php';
            
            $stmt = $pdo->query("SELECT 
                COUNT(CASE WHEN type = 'book' THEN 1 END) as books,
                COUNT(CASE WHEN type = 'magazine' THEN 1 END) as magazines,
                COUNT(CASE WHEN type = 'newspaper' THEN 1 END) as newspapers,
                COUNT(*) as total
                FROM items");
            $stats = $stmt->fetch();
        } catch (Exception $e) {
            $stats = ['books' => 0, 'magazines' => 0, 'newspapers' => 0, 'total' => 0];
        }
        ?>
        
        <div class="quick-stats">
            <h3 style="color: #92400e; margin-bottom: 1rem; text-align: center;">
                <i class="fas fa-chart-bar"></i>
                <?= $lang == 'ar' ? 'إحصائيات المصادر الحالية' : 'Statistiques des Ressources Actuelles' ?>
            </h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['books'] ?></div>
                    <div class="stat-label"><?= $lang == 'ar' ? 'كتاب' : 'Livres' ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['magazines'] ?></div>
                    <div class="stat-label"><?= $lang == 'ar' ? 'مجلة' : 'Revues' ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['newspapers'] ?></div>
                    <div class="stat-label"><?= $lang == 'ar' ? 'جريدة' : 'Journaux' ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div class="stat-label"><?= $lang == 'ar' ? 'إجمالي' : 'Total' ?></div>
                </div>
            </div>
        </div>

        <!-- قسم الكتب -->
        <div class="section">
            <div class="section-header">
                <div class="section-icon">📚</div>
                <div class="section-info">
                    <h2><?= $add_options['books'][$lang]['title'] ?></h2>
                    <p><?= $add_options['books'][$lang]['desc'] ?></p>
                </div>
            </div>
            
            <div class="options-grid">
                <?php foreach ($add_options['books'][$lang]['options'] as $option): ?>
                    <a href="<?= $option['file'] ?>?lang=<?= $lang ?>" class="option-card">
                        <div class="option-icon"><?= $option['icon'] ?></div>
                        <h3 class="option-title"><?= $option['title'] ?></h3>
                        <p class="option-desc"><?= $option['desc'] ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- قسم المجلات -->
        <div class="section">
            <div class="section-header">
                <div class="section-icon">📰</div>
                <div class="section-info">
                    <h2><?= $add_options['magazines'][$lang]['title'] ?></h2>
                    <p><?= $add_options['magazines'][$lang]['desc'] ?></p>
                </div>
            </div>
            
            <div class="options-grid">
                <?php foreach ($add_options['magazines'][$lang]['options'] as $option): ?>
                    <a href="<?= $option['file'] ?>?lang=<?= $lang ?>" class="option-card">
                        <div class="option-icon"><?= $option['icon'] ?></div>
                        <h3 class="option-title"><?= $option['title'] ?></h3>
                        <p class="option-desc"><?= $option['desc'] ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- مصادر أخرى -->
        <div class="section">
            <div class="section-header">
                <div class="section-icon">🎯</div>
                <div class="section-info">
                    <h2><?= $add_options['others'][$lang]['title'] ?></h2>
                    <p><?= $add_options['others'][$lang]['desc'] ?></p>
                </div>
            </div>
            
            <div class="options-grid">
                <?php foreach ($add_options['others'][$lang]['options'] as $option): ?>
                    <a href="<?= $option['file'] ?>?lang=<?= $lang ?>" class="option-card">
                        <div class="option-icon"><?= $option['icon'] ?></div>
                        <h3 class="option-title"><?= $option['title'] ?></h3>
                        <p class="option-desc"><?= $option['desc'] ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- روابط سريعة -->
        <div style="margin-top: 3rem; text-align: center;">
            <h3 style="color: var(--gray-600); margin-bottom: 1rem;">
                <?= $lang == 'ar' ? 'روابط سريعة أخرى' : 'Autres liens rapides' ?>
            </h3>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="list.php?lang=<?=$lang?>" class="back-btn">
                    📋 <?= $lang == 'ar' ? 'قائمة المصادر' : 'Liste des Ressources' ?>
                </a>
                <a href="statistics.php?lang=<?=$lang?>" class="back-btn">
                    📊 <?= $lang == 'ar' ? 'الإحصائيات' : 'Statistiques' ?>
                </a>
                <a href="../../dashboard-fixed.php?lang=<?=$lang?>" class="back-btn">
                    🏠 <?= $lang == 'ar' ? 'لوحة التحكم' : 'Tableau de bord' ?>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Animation للبطاقات
            const cards = document.querySelectorAll('.option-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Animation للأقسام
            const sections = document.querySelectorAll('.section');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateX(' + (index % 2 === 0 ? '-20px' : '20px') + ')';
                
                setTimeout(() => {
                    section.style.transition = 'all 0.8s ease';
                    section.style.opacity = '1';
                    section.style.transform = 'translateX(0)';
                }, index * 200);
            });
            
            console.log('✅ مركز إضافة المصادر محمل بنجاح');
        });
    </script>
</body>
</html>
