<?php
/* modules/items/manage_books.php - Gestion des livres par spécialisation */
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';
$specialization = $_GET['specialization'] ?? 'administration';
$category = $_GET['category'] ?? 'specialized';
$search = $_GET['search'] ?? '';

// Mapper les spécialisations
$specialization_map = [
    'administration' => [
        'ar' => 'علوم الإدارة ',
        'fr' => 'Sciences de l\'Administration',
        'icon' => '🏛️',
        'color' => '#4f46e5'
    ],
    'law' => [
        'ar' => 'القانون',
        'fr' => 'Droit',
        'icon' => '⚖️',
        'color' => '#dc2626'
    ],
    'economics' => [
        'ar' => 'الاقتصاد والمالية',
        'fr' => 'Économie et Finance',
        'icon' => '💰',
        'color' => '#16a34a'
    ],
    'diplomacy' => [
        'ar' => 'الدبلوماسية والعلوم السياسية',
        'fr' => 'La Diplomatie et les Sciences Politiques',
        'icon' => '🤝',
        'color' => '#ea580c'
    ],
    'media' => [
        'ar' => 'الإعلام والاتصالات',
        'fr' => 'Médias et Communications',
        'icon' => '📡',
        'color' => '#7c3aed'
    ]
];

$current_specialization = $specialization_map[$specialization] ?? $specialization_map['administration'];

// جلب الكتب من قاعدة البيانات
try {
    $field_name = ucfirst($specialization);
    
    if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT * FROM items 
            WHERE type = 'book' 
            AND field = ? 
            AND lang = ? 
            AND (
                main_title LIKE ?
                OR title LIKE ?
                OR subtitle LIKE ?
                OR short_title LIKE ?
                OR author LIKE ?
                OR isbn LIKE ?
                OR classification_number LIKE ?
                OR publication_place LIKE ?
            )
            ORDER BY created_at DESC
        ");
        $search_param = "%$search%";
        $stmt->execute([
            $field_name,
            $lang,
            $search_param,
            $search_param,
            $search_param,
            $search_param,
            $search_param,
            $search_param,
            $search_param,
            $search_param
        ]);
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM items 
            WHERE type = 'book' 
            AND field = ? 
            AND lang = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$field_name, $lang]);
    }
    
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $book_count = count($books);
} catch (PDOException $e) {
    $books = [];
    $book_count = 0;
    $db_error = $e->getMessage();
}

function book_value(array $book, array $keys, string $default = '-'): string {
    foreach ($keys as $key) {
        if (array_key_exists($key, $book) && $book[$key] !== null && $book[$key] !== '') {
            return (string)$book[$key];
        }
    }
    return $default;
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $current_specialization[$lang] ?> - <?= $lang == 'ar' ? 'نظام المكتبة' : 'SEBIL–ENAJM : Système Électronique de la Bibliothèque' ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: <?= $current_specialization['color'] ?>;
            --primary-dark: <?= $current_specialization['color'] ?>dd;
            --primary-light: <?= $current_specialization['color'] ?>22;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.05)"/></svg>');
            opacity: 0.3;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .header-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-subtitle {
            font-size: 1rem;
            opacity: 0.95;
            font-weight: 400;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--white);
            color: var(--gray-600);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        .back-link:hover {
            background: var(--gray-100);
            color: var(--gray-800);
            transform: translateX(<?= $lang == 'ar' ? '5px' : '-5px' ?>);
            box-shadow: var(--shadow-md);
        }

        .search-section {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 0.875rem 1.25rem;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .search-btn {
            padding: 0.875rem 2rem;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--white);
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--primary);
        }

        .stat-info h4 {
            font-size: 0.875rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        .stat-info p {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .books-table {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-light));
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid var(--primary);
        }

        .table-header h3 {
            color: var(--primary);
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--gray-50);
        }

        th {
            padding: 1rem 1.5rem;
            text-align: <?= $lang == 'ar' ? 'right' : 'left' ?>;
            font-weight: 700;
            color: var(--gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-200);
        }

        td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-700);
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: var(--gray-50);
        }

        .book-title {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 1rem;
        }

        .book-author {
            color: var(--gray-600);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .badge-lang {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .col-actions {
            width: 120px;
            text-align: center;
        }

        .details-btn {
            border: 1px solid var(--primary);
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 8px;
            padding: 0.45rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .details-btn:hover {
            background: var(--primary);
            color: var(--white);
        }

        .details-row {
            display: none;
            background: var(--gray-50);
        }

        .details-row.open {
            display: table-row;
        }

        .details-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            padding: 1rem 1.25rem;
        }

        .details-title {
            color: var(--gray-900);
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem 1.25rem;
            font-size: 0.875rem;
        }

        .detail-item strong {
            color: var(--gray-700);
            display: block;
            margin-bottom: 0.2rem;
        }

        .detail-desc {
            margin-top: 0.9rem;
            border-top: 1px solid var(--gray-200);
            padding-top: 0.75rem;
            font-size: 0.875rem;
            color: var(--gray-700);
            white-space: pre-wrap;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-600);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .quick-links {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            margin-top: 2rem;
        }

        .quick-links h3 {
            color: var(--gray-800);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
        }

        .quick-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            background: var(--gray-50);
            border-radius: 10px;
            text-decoration: none;
            color: var(--gray-700);
            transition: all 0.2s ease;
            border: 1px solid var(--gray-200);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .quick-link:hover {
            background: var(--gray-100);
            border-color: var(--primary);
            transform: translateX(<?= $lang == 'ar' ? '3px' : '-3px' ?>);
        }

        @media (max-width: 768px) {
            .header-title {
                font-size: 1.5rem;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-btn {
                width: 100%;
                justify-content: center;
            }
            
            .stats-bar {
                flex-direction: column;
                gap: 1rem;
            }
            
            table {
                font-size: 0.875rem;
            }
            
            th, td {
                padding: 0.75rem;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon"><?= $current_specialization['icon'] ?></div>
            <h1 class="header-title"><?= $current_specialization[$lang] ?></h1>
            <p class="header-subtitle">
                <?= $lang == 'ar' ? 'إدارة الكتب المتخصصة' : 'Gestion des livres spécialisés' ?>
            </p>
        </div>
    </div>

    <div class="container">
        <!-- Back Link -->
        <a href="../../dashboard.php?lang=<?=$lang?>" class="back-link fade-in">
            <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
            <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
        </a>

        <!-- Search Section -->
        <div class="search-section fade-in" style="animation-delay: 0.1s;">
            <form method="GET" action="" class="search-form">
                <input type="hidden" name="specialization" value="<?= htmlspecialchars($specialization) ?>">
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
                
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="<?= $lang == 'ar' ? '🔍 البحث عن كتاب (العنوان، المؤلف، ISBN)...' : '🔍 Rechercher un livre (titre, auteur, ISBN)...' ?>"
                       value="<?= htmlspecialchars($search) ?>">
                
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    <?= $lang == 'ar' ? 'بحث' : 'Rechercher' ?>
                </button>
                
                <?php if (!empty($search)): ?>
                    <a href="?specialization=<?=$specialization?>&category=<?=$category?>&lang=<?=$lang?>" 
                       class="search-btn" style="background: var(--gray-600); text-decoration: none;">
                        <i class="fas fa-times"></i>
                        <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar fade-in" style="animation-delay: 0.2s;">
            <div class="stat-item">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h4><?= $lang == 'ar' ? 'إجمالي الكتب' : 'Total des livres' ?></h4>
                    <p><?= $book_count ?></p>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon"><?= $current_specialization['icon'] ?></div>
                <div class="stat-info">
                    <h4><?= $lang == 'ar' ? 'التخصص' : 'Spécialisation' ?></h4>
                    <p style="font-size: 1.1rem;"><?= $current_specialization[$lang] ?></p>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon"><?= $lang == 'ar' ? '🇸🇦' : '🇫🇷' ?></div>
                <div class="stat-info">
                    <h4><?= $lang == 'ar' ? 'اللغة' : 'Langue' ?></h4>
                    <p style="font-size: 1.1rem;"><?= $lang == 'ar' ? 'العربية' : 'Français' ?></p>
                </div>
            </div>
        </div>

        <!-- Books Table -->
        <div class="books-table fade-in" style="animation-delay: 0.3s;">
            <div class="table-header">
                <h3>
                    <i class="fas fa-list"></i>
                    <?= $lang == 'ar' ? 'قائمة الكتب' : 'Liste des livres' ?>
                    <?php if (!empty($search)): ?>
                        <span style="font-weight: 400; font-size: 0.9rem;">
                            (<?= $lang == 'ar' ? 'نتائج البحث عن: ' : 'Résultats pour: ' ?>"<?= htmlspecialchars($search) ?>")
                        </span>
                    <?php endif; ?>
                </h3>
            </div>
            
            <?php if (count($books) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= $lang == 'ar' ? 'العنوان والمؤلف' : 'Titre et Auteur' ?></th>
                            <th><?= $lang == 'ar' ? 'دار النشر' : 'Éditeur' ?></th>
                            <th><?= $lang == 'ar' ? 'سنة النشر' : 'Année' ?></th>
                            <th><?= $lang == 'ar' ? 'ISBN' : 'ISBN' ?></th>
                            <th><?= $lang == 'ar' ? 'النسخ' : 'Copies' ?></th>
                            <th><?= $lang == 'ar' ? 'اللغة' : 'Langue' ?></th>
                            <th class="col-actions"><?= $lang == 'ar' ? 'التفاصيل' : 'Détails' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $index => $book): ?>
                            <?php
                                $book_id = isset($book['id']) ? (string)$book['id'] : (string)$index;
                                $detail_row_id = 'book-details-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $book_id) . '-' . $index;
                                $display_title = book_value($book, ['main_title', 'title']);
                                $display_year = book_value($book, ['publication_year', 'year_pub']);
                                $display_copies = book_value($book, ['copies'], '0');
                                $display_available = book_value($book, ['available_copies'], '0');
                                $display_language = book_value($book, ['language', 'lang']);
                            ?>
                            <tr class="book-row">
                                <td style="font-weight: 600; color: var(--primary);"><?= $index + 1 ?></td>
                                <td>
                                    <div class="book-title"><?= htmlspecialchars($display_title) ?></div>
                                    <div class="book-author">
                                        <i class="fas fa-user" style="font-size: 0.75rem;"></i>
                                        <?= htmlspecialchars(book_value($book, ['author'])) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars(book_value($book, ['publisher'])) ?></td>
                                <td><?= htmlspecialchars($display_year) ?></td>
                                <td style="font-family: monospace; font-size: 0.875rem;"><?= htmlspecialchars(book_value($book, ['isbn'])) ?></td>
                                <td>
                                    <span style="font-weight: 600; color: var(--primary);">
                                        <?= htmlspecialchars($display_available) ?>
                                    </span>
                                    /
                                    <?= htmlspecialchars($display_copies) ?>
                                </td>
                                <td>
                                    <span class="badge-lang">
                                        <?= $display_language == 'ar' ? 'AR' : 'FR' ?>
                                    </span>
                                </td>
                                <td class="col-actions">
                                    <button type="button" class="details-btn" data-target="<?= htmlspecialchars($detail_row_id) ?>">
                                        <i class="fas fa-eye"></i>
                                        <?= $lang == 'ar' ? 'عرض' : 'Voir' ?>
                                    </button>
                                </td>
                            </tr>
                            <tr id="<?= htmlspecialchars($detail_row_id) ?>" class="details-row">
                                <td colspan="8">
                                    <div class="details-card">
                                        <div class="details-title"><?= $lang == 'ar' ? 'معلومات الكتاب التفصيلية' : 'Informations détaillées du livre' ?></div>
                                        <div class="details-grid">
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'التصنيف' : 'Classification' ?></strong><span><?= htmlspecialchars(book_value($book, ['classification', 'classification_number'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'المجال' : 'Domaine' ?></strong><span><?= htmlspecialchars(book_value($book, ['field'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'الصيغة' : 'Format' ?></strong><span><?= htmlspecialchars(book_value($book, ['format'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'عدد الصفحات' : 'Pages' ?></strong><span><?= htmlspecialchars(book_value($book, ['pages'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'الطبعة' : 'Édition' ?></strong><span><?= htmlspecialchars(book_value($book, ['edition'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'السلسلة' : 'Série' ?></strong><span><?= htmlspecialchars(book_value($book, ['series'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'نوع الكتاب' : 'Type de livre' ?></strong><span><?= htmlspecialchars(book_value($book, ['book_type'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'العنوان الفرعي' : 'Sous-titre' ?></strong><span><?= htmlspecialchars(book_value($book, ['subtitle'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'العنوان المختصر' : 'Titre abrégé' ?></strong><span><?= htmlspecialchars(book_value($book, ['short_title'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'مكان النشر' : 'Lieu de publication' ?></strong><span><?= htmlspecialchars(book_value($book, ['publication_place'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'رقم التصنيف' : 'Numéro de classification' ?></strong><span><?= htmlspecialchars(book_value($book, ['classification_number'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'رقم الرف' : 'Numéro d\'étagère' ?></strong><span><?= htmlspecialchars(book_value($book, ['shelf_number'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'رقم الدرج' : 'Numéro du tiroir' ?></strong><span><?= htmlspecialchars(book_value($book, ['drawer_number'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'عدد الأجزاء' : 'Nombre de parties' ?></strong><span><?= htmlspecialchars(book_value($book, ['parts'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'تاريخ التسجيل' : 'Date d\'enregistrement' ?></strong><span><?= htmlspecialchars(book_value($book, ['registration_date'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'تاريخ التعديل' : 'Date de modification' ?></strong><span><?= htmlspecialchars(book_value($book, ['modification_date'])) ?></span></div>
                                            <div class="detail-item"><strong><?= $lang == 'ar' ? 'تاريخ الإضافة' : 'Date d\'ajout' ?></strong><span><?= htmlspecialchars(book_value($book, ['created_at'])) ?></span></div>
                                        </div>
                                        <div class="detail-desc">
                                            <strong><?= $lang == 'ar' ? 'Description' : 'Description' ?></strong><br>
                                            <?= htmlspecialchars(book_value($book, ['description'])) ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <h3 style="color: var(--gray-700); margin-bottom: 0.5rem;">
                        <?= $lang == 'ar' ? 'لا توجد كتب' : 'Aucun livre trouvé' ?>
                    </h3>
                    <p>
                        <?php if (!empty($search)): ?>
                            <?= $lang == 'ar' ? 'لم يتم العثور على نتائج للبحث' : 'Aucun résultat trouvé pour votre recherche' ?>
                        <?php else: ?>
                            <?= $lang == 'ar' ? 'لا توجد كتب في هذا التخصص حالياً' : 'Aucun livre dans cette spécialisation actuellement' ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Links -->
        <div class="quick-links fade-in" style="animation-delay: 0.4s;">
            <h3>
                <i class="fas fa-link"></i>
                <?= $lang == 'ar' ? 'التخصصات الأخرى' : 'Autres spécialisations' ?>
            </h3>
            <div class="quick-links-grid">
                <?php foreach ($specialization_map as $spec_key => $spec_data): ?>
                    <?php if ($spec_key !== $specialization): ?>
                        <a href="?specialization=<?= $spec_key ?>&category=<?= $category ?>&lang=<?= $lang ?>" class="quick-link">
                            <span style="font-size: 1.25rem;"><?= $spec_data['icon'] ?></span>
                            <span><?= $spec_data[$lang] ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ صفحة إدارة الكتب المتخصصة محملة بنجاح');
            
            // Add smooth scroll behavior
            document.documentElement.style.scrollBehavior = 'smooth';
            
            // Focus on search input
            const searchInput = document.querySelector('.search-input');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }

            document.querySelectorAll('.details-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const targetId = btn.getAttribute('data-target');
                    const row = document.getElementById(targetId);
                    if (!row) return;

                    const isOpen = row.classList.contains('open');
                    document.querySelectorAll('.details-row.open').forEach(function(openRow) {
                        openRow.classList.remove('open');
                    });

                    if (!isOpen) {
                        row.classList.add('open');
                    }
                });
            });
        });
    </script>
</body>
</html>
