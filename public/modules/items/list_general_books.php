<?php
/**
 * صفحة عرض الكتب العامة - عرض قائمة مع بحث
 */

session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) {
    header('Location: ../../login.php');
    exit;
}

// الاتصال بقاعدة البيانات
try {
    require_once __DIR__ . '/../../../config/db.php';
    $db_connected = true;
} catch (Exception $e) {
    $db_connected = false;
    $db_error = $e->getMessage();
}

$lang = $_GET['lang'] ?? 'ar';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// معالجة البحث
$search = trim($_GET['search'] ?? '');
$search_field = $_GET['search_field'] ?? 'all';

// جلب الكتب العامة
$books = [];
$total_books = 0;

if ($db_connected) {
    try {
        // بناء استعلام البحث
        $where_conditions = ["type = 'book'", "(book_type = 'general' OR book_type IS NULL OR book_type = '')"];
        $params = [];
        
        if (!empty($search)) {
            if ($search_field === 'all') {
                $where_conditions[] = "(main_title LIKE :search OR subtitle LIKE :search OR author LIKE :search OR publisher LIKE :search)";
                $params[':search'] = "%$search%";
            } else {
                $where_conditions[] = "$search_field LIKE :search";
                $params[':search'] = "%$search%";
            }
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // عد إجمالي الكتب
        $count_sql = "SELECT COUNT(*) FROM items WHERE $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_books = $count_stmt->fetchColumn();
        
        // جلب الكتب مع الترقيم
        $sql = "
            SELECT id, main_title, subtitle, author, publisher, publication_year, 
                   copies, available_copies, lang, created_at, parts, isbn, classification_number
            FROM items 
            WHERE $where_clause
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

// حساب عدد الصفحات
$total_pages = ceil($total_books / $limit);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'الكتب العامة' : 'Livres Généraux' ?> - نظام المكتبة</title>
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
            --error: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .header-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .toolbar {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-200);
        }

        .toolbar-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-box {
            display: flex;
            gap: 0.5rem;
            flex: 1;
            min-width: 300px;
        }

        .search-select {
            padding: 0.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            background: var(--white);
            color: var(--gray-800);
            font-size: 0.875rem;
            min-width: 150px;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-600);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background: #16a34a;
        }

        .stats-bar {
            background: var(--white);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .stat-item i {
            color: var(--primary-color);
        }

        .stat-item strong {
            color: var(--gray-900);
            font-weight: 700;
        }

        .table-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--gray-100);
        }

        th {
            padding: 1rem;
            text-align: <?= $lang == 'ar' ? 'right' : 'left' ?>;
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--gray-200);
            white-space: nowrap;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-800);
            font-size: 0.875rem;
        }

        tbody tr {
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: var(--gray-50);
        }

        .book-title {
            font-weight: 600;
            color: var(--gray-900);
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .book-subtitle {
            color: var(--gray-600);
            font-size: 0.75rem;
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-available {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }

        .status-limited {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-unavailable {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding: 1.5rem;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.5rem;
            height: 2.5rem;
            padding: 0 0.75rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .pagination a {
            background: var(--gray-100);
            color: var(--gray-600);
            border: 1px solid var(--gray-200);
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
            transform: translateY(-1px);
        }

        .pagination .active {
            background: var(--primary-color);
            color: var(--white);
            border: 1px solid var(--primary-color);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--gray-600);
            margin-bottom: 2rem;
        }

        .search-info {
            background: rgba(79, 70, 229, 0.1);
            border: 1px solid rgba(79, 70, 229, 0.2);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .search-info-text {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .toolbar-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                flex-direction: column;
                min-width: 100%;
            }
            
            .table-wrapper {
                font-size: 0.75rem;
            }
            
            th, td {
                padding: 0.5rem;
            }
            
            .book-title {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-icon">📚</div>
        <h1 class="header-title"><?= $lang == 'ar' ? 'الكتب العامة' : 'Livres Généraux' ?></h1>
        <p><?= $lang == 'ar' ? 'مجموعة الكتب الثقافية والعامة' : 'Collection de livres culturels et généraux' ?></p>
    </div>

    <div class="container">
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-row">
                <!-- Search Box -->
                <form method="get" class="search-box">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    
                    <select name="search_field" class="search-select">
                        <option value="all" <?= $search_field === 'all' ? 'selected' : '' ?>>
                            <?= $lang == 'ar' ? 'جميع الحقول' : 'Tous les champs' ?>
                        </option>
                        <option value="main_title" <?= $search_field === 'main_title' ? 'selected' : '' ?>>
                            <?= $lang == 'ar' ? 'العنوان' : 'Titre' ?>
                        </option>
                        <option value="author" <?= $search_field === 'author' ? 'selected' : '' ?>>
                            <?= $lang == 'ar' ? 'المؤلف' : 'Auteur' ?>
                        </option>
                        <option value="publisher" <?= $search_field === 'publisher' ? 'selected' : '' ?>>
                            <?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?>
                        </option>
                    </select>
                    
                    <input 
                        type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="<?= $lang == 'ar' ? 'ابحث عن كتاب...' : 'Rechercher un livre...' ?>"
                        value="<?= htmlspecialchars($search) ?>"
                    >
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        <?= $lang == 'ar' ? 'بحث' : 'Rechercher' ?>
                    </button>
                    
                    <?php if (!empty($search)): ?>
                        <a href="?lang=<?= $lang ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                        </a>
                    <?php endif; ?>
                </form>
                
                <!-- Action Buttons -->
                <div style="display: flex; gap: 0.5rem;">
                    <a href="add_general_book.php?lang=<?= $lang ?>" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة كتاب' : 'Ajouter' ?>
                    </a>
                    
                    <a href="../../dashboard.php?lang=<?= $lang ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
                        <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Info -->
        <?php if (!empty($search)): ?>
            <div class="search-info">
                <div class="search-info-text">
                    <i class="fas fa-info-circle"></i>
                    <span>
                        <?= $lang == 'ar' ? 'نتائج البحث عن:' : 'Résultats de recherche pour:' ?>
                        <strong>"<?= htmlspecialchars($search) ?>"</strong>
                        (<?= $total_books ?> <?= $lang == 'ar' ? 'نتيجة' : 'résultat(s)' ?>)
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Statistics Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <i class="fas fa-book"></i>
                <span><?= $lang == 'ar' ? 'إجمالي الكتب:' : 'Total des livres:' ?></span>
                <strong><?= $total_books ?></strong>
            </div>
            
            <div class="stat-item">
