<?php
/**
 * Page d'accueil simplifiée du SEBIL–ENAJM : Système Électronique de la Bibliothèque
 * Simplified homepage for the library management system
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

// Détecter la langue préférée
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'ar';
if (in_array($lang, ['ar', 'fr'])) {
    $_SESSION['lang'] = $lang;
}

// Si l'utilisateur est connecté, rediriger vers le dashboard
if (isset($_SESSION['uid'])) {
    header('Location: public/dashboard.php?lang=' . $lang);
    exit;
}

// Traitement de la recherche
$search_results = [];
$search_query = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_query = trim($_POST['search']);
    if (!empty($search_query)) {
        try {
            require_once __DIR__ . '/config/db.php';
            
            $search_param = "%{$search_query}%";
            $stmt = $pdo->prepare("
                SELECT id, title, author, type, lang, available_copies, copies
                FROM items 
                WHERE (title LIKE ? OR author LIKE ?) 
                AND available_copies > 0
                ORDER BY title ASC 
                LIMIT 20
            ");
            $stmt->execute([$search_param, $search_param]);
            $search_results = $stmt->fetchAll();
        } catch (PDOException $e) {
            // Ignorer l'erreur
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? ' النظام الإلكتروني للمكتبة' : 'Système de Gestion de Bibliothèque' ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 3rem;
            max-width: 800px;
            width: 100%;
            text-align: center;
            position: relative;
            margin-top: 1rem;
            border: 1px solid #e2e8f0;
        }
        
        .login-section {
            position: absolute;
            top: 2rem;
            right: 2rem;
            z-index: 10;
        }
        
        .login-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(79, 70, 229, 0.3);
            border: 1px solid #4338ca;
        }
        
        .login-btn:hover {
            background: #3730a3;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.4);
        }
        
        .login-btn i {
            font-size: 0.85rem;
        }
        
        .resources-section {
            margin-bottom: 3rem;
        }
        
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .resource-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .resource-item:hover {
            background: #f1f5f9;
            border-color: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        }
        
        .resource-icon {
            font-size: 2.5rem;
            color: #4f46e5;
            margin-bottom: 1rem;
        }
        
        .resource-name {
            font-weight: 600;
            color: #374151;
            font-size: 1rem;
        }
        
        .header {
            margin-bottom: 3rem;
        }
        
        .header h1 {
            font-size: 2.2rem;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            text-align: center;
            line-height: 1.3;
            font-weight: 700;
            margin-top: 2rem; /* مساحة إضافية من الأعلى */
        }
        
        .header .icon {
            color: #667eea;
            font-size: 2.5rem;
        }
        
        .header p {
            color: #6b7280;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .search-section {
            margin-bottom: 3rem;
        }
        
        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .search-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .search-btn {
            padding: 1rem 2rem;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(79, 70, 229, 0.3);
        }
        
        .search-btn:hover {
            background: #3730a3;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.4);
        }
        
        .search-results {
            text-align: left;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .result-item {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }
        
        .result-item:hover {
            background: #f1f5f9;
            border-color: #667eea;
        }
        
        .result-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .result-author {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .result-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .result-type {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        
        .no-results {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 2rem;
        }
        
        .language-switch {
            position: absolute;
            top: 2rem;
            left: 2rem;
            display: flex;
            gap: 0.5rem;
            z-index: 10;
        }
        
        .lang-btn {
            padding: 0.5rem 1rem;
            background: white;
            color: #64748b;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .lang-btn:hover {
            background: #f8fafc;
            color: #4f46e5;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }
        
        .lang-btn.active {
            background: #4f46e5;
            color: white;
            font-weight: 600;
            border-color: #4338ca;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 2rem;
                margin: 1rem;
                margin-top: 5rem; /* مساحة للأزرار العلوية */
            }
            
            .header h1 {
                font-size: 1.8rem;
                text-align: center;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .language-switch {
                top: 1rem;
                left: 1rem;
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .login-section {
                top: 1rem;
                right: 1rem;
            }
            
            .lang-btn, .login-btn {
                font-size: 0.8rem;
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="language-switch">
        <a href="?lang=ar" class="lang-btn <?= $lang == 'ar' ? 'active' : '' ?>">العربية</a>
        <a href="?lang=fr" class="lang-btn <?= $lang == 'fr' ? 'active' : '' ?>">Français</a>
                </div>
    
    <div class="container">
        <div class="login-section">
            <a href="public/login.php?lang=<?=$lang?>" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                <?= $lang == 'ar' ? 'تسجيل الدخول' : 'Connexion' ?>
            </a>
        </div>
        
        <div class="header">
            <h1>
                <i class="fas fa-book icon"></i>
                <?= $lang == 'ar' ? 'المدرسة الوطنية للإدارة و الصحافة و القضاء ' : 'L’École nationale d’administration, de la magistrature, du journalisme et de l’information' ?>
            </h1>
            
        </div>
        
        <div class="resources-section">
            <h2 style="margin-bottom: 1.5rem; color: #374151;">
                <i class="fas fa-th-large"></i>
                <?= $lang == 'ar' ? 'المجموعات والوثائق المتاحة' : 'Collections et Documents disponibles' ?>
            </h2>
            
            <div class="resources-grid">
                <div class="resource-item">
                    <div class="resource-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="resource-name"><?= $lang == 'ar' ? 'الكتب' : 'Livres' ?></div>
        </div>
        
                <div class="resource-item">
                    <div class="resource-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="resource-name"><?= $lang == 'ar' ? 'الدوريات والبحوث' : 'Périodiques et Recherches' ?></div>
                </div>
                
                <div class="resource-item">
                    <div class="resource-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="resource-name"><?= $lang == 'ar' ? 'البحوث الأكاديمية' : 'Recherches Académiques' ?></div>
                </div>
<!--                 
                <div class="resource-item">
                    <div class="resource-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="resource-name"><?= $lang == 'ar' ? 'المستعيرين' : 'Emprunteurs' ?></div>
                </div>
                 -->
                <!-- <div class="resource-item">
                    <div class="resource-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="resource-name"><?= $lang == 'ar' ? 'الإعارات' : 'Emprunts' ?></div>
                </div> -->
<!--                 
                <div class="resource-item">
                    <div class="resource-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="resource-name"><?= $lang == 'ar' ? 'الموظفين' : 'Employés' ?></div>
                </div>
            </div> -->
        </div>
        
        <div class="search-section">
            <h2 style="margin-bottom: 1.5rem; color: #374151;">
                <i class="fas fa-search"></i>
                <?= $lang == 'ar' ? 'البحث في المصادر' : 'Recherche dans les ressources' ?>
            </h2>
            
            <form method="POST" class="search-form">
                <input type="text" name="search" class="search-input" 
                       placeholder="<?= $lang == 'ar' ? 'ابحث في الكتب والمجلات والجرائد...' : 'Rechercher dans les livres, magazines, journaux...' ?>"
                       value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    <?= $lang == 'ar' ? 'بحث' : 'Rechercher' ?>
                </button>
            </form>
            
            <?php if (!empty($search_results)): ?>
                <div class="search-results">
                    <h3 style="margin-bottom: 1rem; color: #374151;">
                        <?= $lang == 'ar' ? 'نتائج البحث:' : 'Résultats de recherche:' ?>
                    </h3>
                    <?php foreach ($search_results as $item): ?>
                        <div class="result-item">
                            <div class="result-title"><?= htmlspecialchars($item['title']) ?></div>
                            <div class="result-author">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($item['author']) ?>
                            </div>
                            <div class="result-info">
                                <span>
                                    <i class="fas fa-copy"></i>
                                    <?= $item['available_copies'] ?> / <?= $item['copies'] ?> 
                                    <?= $lang == 'ar' ? 'متاح' : 'disponible' ?>
                                </span>
                                <span class="result-type">
                                    <?php
                                    $type_names = [
                                        'book' => $lang == 'ar' ? 'كتاب' : 'Livre',
                                        'magazine' => $lang == 'ar' ? 'مجلة' : 'Magazine',
                                        'newspaper' => $lang == 'ar' ? 'جريدة' : 'Journal'
                                    ];
                                    echo $type_names[$item['type']] ?? $item['type'];
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!empty($search_query)): ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; color: #d1d5db;"></i>
                    <p><?= $lang == 'ar' ? 'لم يتم العثور على نتائج' : 'Aucun résultat trouvé' ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>