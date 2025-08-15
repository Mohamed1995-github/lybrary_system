<?php
/* public/test_complet.php - Test complet du système */
require_once __DIR__ . '/../includes/auth.php';

$lang = $_GET['lang'] ?? 'ar';
$pdo = require_once __DIR__ . '/../config/db.php';

// Fonction pour tester la connectivité à la base de données
function testDatabase($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        return ['success' => true, 'users' => $userCount];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Fonction pour tester les fichiers
function testFiles() {
    $files = [
        'public/login.php' => 'Page de connexion',
        'public/dashboard.php' => 'Tableau de bord',
        'public/add_magazine.php' => 'Ajouter magazine',
        'public/add_employee.php' => 'Ajouter employé',
        'public/assets/css/style.css' => 'Fichier CSS',
        'config/db.php' => 'Configuration DB',
        'includes/auth.php' => 'Authentification'
    ];
    
    $results = [];
    foreach ($files as $file => $description) {
        $results[$file] = [
            'exists' => file_exists($file),
            'readable' => is_readable($file),
            'description' => $description
        ];
    }
    return $results;
}

// Fonction pour tester les sessions
function testSession() {
    return [
        'session_started' => session_status() === PHP_SESSION_ACTIVE,
        'user_id' => $_SESSION['uid'] ?? 'Non connecté',
        'user_role' => $_SESSION['role'] ?? 'Aucun',
        'language' => $_SESSION['lang'] ?? 'ar'
    ];
}

// Fonction pour tester les modules
function testModules() {
    $modules = [
        'modules/items/add_book.php' => 'Ajouter livre',
        'modules/items/add_magazine.php' => 'Ajouter magazine',
        'modules/items/add_newspaper.php' => 'Ajouter journal',
        'modules/items/list.php' => 'Liste items',
        'modules/administration/add.php' => 'Ajouter employé',
        'modules/loans/add.php' => 'Ajouter prêt',
        'modules/borrowers/add.php' => 'Ajouter emprunteur'
    ];
    
    $results = [];
    foreach ($modules as $file => $description) {
        $results[$file] = [
            'exists' => file_exists($file),
            'readable' => is_readable($file),
            'description' => $description
        ];
    }
    return $results;
}

// Tests
$dbTest = testDatabase($pdo);
$fileTest = testFiles();
$sessionTest = testSession();
$moduleTest = testModules();
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'تقرير اختبار شامل' : 'Rapport de test complet' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .test-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .test-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .test-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
        }
        .test-status {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-success {
            background: #dcfce7;
            color: #166534;
        }
        .status-error {
            background: #fef2f2;
            color: #dc2626;
        }
        .status-warning {
            background: #fef3c7;
            color: #d97706;
        }
        .url-list {
            list-style: none;
            padding: 0;
        }
        .url-list li {
            margin-bottom: 0.5rem;
        }
        .url-list a {
            color: #2563eb;
            text-decoration: none;
            font-family: monospace;
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .url-list a:hover {
            background: #e2e8f0;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
        }
        .summary-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .summary-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-section">
            <h1 class="test-title">
                <i class="fas fa-clipboard-check"></i>
                <?= $lang == 'ar' ? 'تقرير اختبار شامل - نظام المكتبة' : 'Rapport de test complet - Système de bibliothèque' ?>
            </h1>
            
            <!-- Résumé -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-number"><?= $dbTest['success'] ? '✅' : '❌' ?></div>
                    <div class="summary-label"><?= $lang == 'ar' ? 'قاعدة البيانات' : 'Base de données' ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?= count(array_filter($fileTest, fn($f) => $f['exists'])) ?>/<?= count($fileTest) ?></div>
                    <div class="summary-label"><?= $lang == 'ar' ? 'الملفات' : 'Fichiers' ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?= count(array_filter($moduleTest, fn($m) => $m['exists'])) ?>/<?= count($moduleTest) ?></div>
                    <div class="summary-label"><?= $lang == 'ar' ? 'الوحدات' : 'Modules' ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?= $sessionTest['session_started'] ? '✅' : '❌' ?></div>
                    <div class="summary-label"><?= $lang == 'ar' ? 'الجلسة' : 'Session' ?></div>
                </div>
            </div>
        </div>

        <!-- Test Base de données -->
        <div class="test-section">
            <h2 class="test-title">
                <i class="fas fa-database"></i>
                <?= $lang == 'ar' ? 'اختبار قاعدة البيانات' : 'Test de la base de données' ?>
            </h2>
            <div class="test-item">
                <div class="test-status <?= $dbTest['success'] ? 'status-success' : 'status-error' ?>">
                    <i class="fas fa-<?= $dbTest['success'] ? 'check' : 'times' ?>"></i>
                    <?= $dbTest['success'] ? 
                        ($lang == 'ar' ? 'اتصال ناجح' : 'Connexion réussie') : 
                        ($lang == 'ar' ? 'فشل الاتصال' : 'Échec de connexion') ?>
                </div>
                <?php if ($dbTest['success']): ?>
                    <p><strong><?= $lang == 'ar' ? 'عدد المستخدمين:' : 'Nombre d\'utilisateurs:' ?></strong> <?= $dbTest['users'] ?></p>
                <?php else: ?>
                    <p><strong><?= $lang == 'ar' ? 'خطأ:' : 'Erreur:' ?></strong> <?= $dbTest['error'] ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Test Sessions -->
        <div class="test-section">
            <h2 class="test-title">
                <i class="fas fa-user"></i>
                <?= $lang == 'ar' ? 'اختبار الجلسة' : 'Test de session' ?>
            </h2>
            <div class="test-grid">
                <div class="test-item">
                    <strong><?= $lang == 'ar' ? 'حالة الجلسة:' : 'Statut session:' ?></strong>
                    <div class="test-status <?= $sessionTest['session_started'] ? 'status-success' : 'status-error' ?>">
                        <?= $sessionTest['session_started'] ? 
                            ($lang == 'ar' ? 'نشطة' : 'Active') : 
                            ($lang == 'ar' ? 'غير نشطة' : 'Inactive') ?>
                    </div>
                </div>
                <div class="test-item">
                    <strong><?= $lang == 'ar' ? 'معرف المستخدم:' : 'ID utilisateur:' ?></strong>
                    <span><?= $sessionTest['user_id'] ?></span>
                </div>
                <div class="test-item">
                    <strong><?= $lang == 'ar' ? 'الدور:' : 'Rôle:' ?></strong>
                    <span><?= $sessionTest['user_role'] ?></span>
                </div>
                <div class="test-item">
                    <strong><?= $lang == 'ar' ? 'اللغة:' : 'Langue:' ?></strong>
                    <span><?= $sessionTest['language'] ?></span>
                </div>
            </div>
        </div>

        <!-- Test Fichiers -->
        <div class="test-section">
            <h2 class="test-title">
                <i class="fas fa-file"></i>
                <?= $lang == 'ar' ? 'اختبار الملفات' : 'Test des fichiers' ?>
            </h2>
            <div class="test-grid">
                <?php foreach ($fileTest as $file => $info): ?>
                    <div class="test-item">
                        <strong><?= $info['description'] ?></strong><br>
                        <small><?= $file ?></small><br>
                        <div class="test-status <?= $info['exists'] ? 'status-success' : 'status-error' ?>">
                            <i class="fas fa-<?= $info['exists'] ? 'check' : 'times' ?>"></i>
                            <?= $info['exists'] ? 
                                ($lang == 'ar' ? 'موجود' : 'Existe') : 
                                ($lang == 'ar' ? 'غير موجود' : 'Manquant') ?>
                        </div>
                        <?php if ($info['exists']): ?>
                            <div class="test-status <?= $info['readable'] ? 'status-success' : 'status-error' ?>">
                                <i class="fas fa-<?= $info['readable'] ? 'check' : 'times' ?>"></i>
                                <?= $info['readable'] ? 
                                    ($lang == 'ar' ? 'قابل للقراءة' : 'Lisible') : 
                                    ($lang == 'ar' ? 'غير قابل للقراءة' : 'Non lisible') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Test Modules -->
        <div class="test-section">
            <h2 class="test-title">
                <i class="fas fa-puzzle-piece"></i>
                <?= $lang == 'ar' ? 'اختبار الوحدات' : 'Test des modules' ?>
            </h2>
            <div class="test-grid">
                <?php foreach ($moduleTest as $file => $info): ?>
                    <div class="test-item">
                        <strong><?= $info['description'] ?></strong><br>
                        <small><?= $file ?></small><br>
                        <div class="test-status <?= $info['exists'] ? 'status-success' : 'status-error' ?>">
                            <i class="fas fa-<?= $info['exists'] ? 'check' : 'times' ?>"></i>
                            <?= $info['exists'] ? 
                                ($lang == 'ar' ? 'موجود' : 'Existe') : 
                                ($lang == 'ar' ? 'غير موجود' : 'Manquant') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- URLs de Test -->
        <div class="test-section">
            <h2 class="test-title">
                <i class="fas fa-link"></i>
                <?= $lang == 'ar' ? 'روابط الاختبار' : 'Liens de test' ?>
            </h2>
            <div class="test-grid">
                <div class="test-item">
                    <h3><?= $lang == 'ar' ? 'الصفحات الرئيسية' : 'Pages principales' ?></h3>
                    <ul class="url-list">
                        <li><a href="login.php"><?= $lang == 'ar' ? 'تسجيل الدخول' : 'Connexion' ?></a></li>
                        <li><a href="dashboard.php"><?= $lang == 'ar' ? 'لوحة التحكم' : 'Tableau de bord' ?></a></li>
                        <li><a href="add_pages.php"><?= $lang == 'ar' ? 'صفحات الإضافة' : 'Pages d\'ajout' ?></a></li>
                    </ul>
                </div>
                <div class="test-item">
                    <h3><?= $lang == 'ar' ? 'إضافة العناصر' : 'Ajouter des éléments' ?></h3>
                    <ul class="url-list">
                        <li><a href="add_magazine.php?lang=ar"><?= $lang == 'ar' ? 'إضافة مجلة' : 'Ajouter magazine' ?></a></li>
                        <li><a href="add_employee.php?lang=ar"><?= $lang == 'ar' ? 'إضافة موظف' : 'Ajouter employé' ?></a></li>
                        <li><a href="router.php?module=items&action=add_book&lang=ar"><?= $lang == 'ar' ? 'إضافة كتاب' : 'Ajouter livre' ?></a></li>
                    </ul>
                </div>
                <div class="test-item">
                    <h3><?= $lang == 'ar' ? 'أدوات التشخيص' : 'Outils de diagnostic' ?></h3>
                    <ul class="url-list">
                        <li><a href="database_check.php"><?= $lang == 'ar' ? 'فحص قاعدة البيانات' : 'Vérifier DB' ?></a></li>
                        <li><a href="test_router.php"><?= $lang == 'ar' ? 'اختبار الراوتر' : 'Test routeur' ?></a></li>
                        <li><a href="simple_router.php?module=items&action=list&lang=ar"><?= $lang == 'ar' ? 'راوتر مبسط' : 'Routeur simple' ?></a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recommandations -->
        <div class="test-section">
            <h2 class="test-title">
                <i class="fas fa-lightbulb"></i>
                <?= $lang == 'ar' ? 'التوصيات' : 'Recommandations' ?>
            </h2>
            <div class="test-grid">
                <div class="test-item">
                    <h3><?= $lang == 'ar' ? '✅ ما يعمل بشكل جيد' : '✅ Ce qui fonctionne bien' ?></h3>
                    <ul>
                        <li><?= $lang == 'ar' ? 'تصميم حديث وجميل' : 'Design moderne et beau' ?></li>
                        <li><?= $lang == 'ar' ? 'دعم اللغتين العربية والفرنسية' : 'Support arabe et français' ?></li>
                        <li><?= $lang == 'ar' ? 'نظام مصادقة آمن' : 'Système d\'authentification sécurisé' ?></li>
                        <li><?= $lang == 'ar' ? 'واجهة مستخدم سهلة الاستخدام' : 'Interface utilisateur intuitive' ?></li>
                    </ul>
                </div>
                <div class="test-item">
                    <h3><?= $lang == 'ar' ? '⚠️ ما يحتاج تحسين' : '⚠️ Ce qui peut être amélioré' ?></h3>
                    <ul>
                        <li><?= $lang == 'ar' ? 'إصلاح مشاكل المسارات' : 'Corriger les problèmes de routage' ?></li>
                        <li><?= $lang == 'ar' ? 'تحسين إدارة الأخطاء' : 'Améliorer la gestion d\'erreurs' ?></li>
                        <li><?= $lang == 'ar' ? 'إضافة المزيد من التحقق من الصلاحيات' : 'Ajouter plus de vérifications de permissions' ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 