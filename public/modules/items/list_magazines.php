<?php
/**
 * صفحة عرض الدوريات - عرض قائمة مع بحث
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
$field_filter = $_GET['field'] ?? '';

// جلب الدوريات
$magazines = [];
$total_magazines = 0;

if ($db_connected) {
    try {
        // بناء استعلام البحث
        $where_conditions = ["type = 'magazine'"];
        $params = [];
        
        if (!empty($search)) {
            if ($search_field === 'all') {
                $where_conditions[] = "(title LIKE :search OR magazine_type LIKE :search OR publisher LIKE :search OR issue_number LIKE :search)";
                $params[':search'] = "%$search%";
            } else {
                $where_conditions[] = "$search_field LIKE :search";
                $params[':search'] = "%$search%";
            }
        }
        
        if (!empty($field_filter)) {
            $where_conditions[] = "field = :field";
            $params[':field'] = $field_filter;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // عد إجمالي الدوريات
        $count_sql = "SELECT COUNT(*) FROM items WHERE $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_magazines = $count_stmt->fetchColumn();
        
        // جلب الدوريات مع الترقيم
        $sql = "
            SELECT id, title, magazine_type, issue_number, publication_date, publisher, 
                   issn, field, lang, created_at, periodical_file
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
        $magazines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

// حساب عدد الصفحات
$total_pages = ceil($total_magazines / $limit);

// التخصصات المتاحة
$fields = [
    'Administration' => ['ar' => 'الإدارة العامة', 'fr' => 'Administration Publique'],
    'Law' => ['ar' => 'القانون', 'fr' => 'Droit'],
    'Economics' => ['ar' => 'الاقتصاد', 'fr' => 'Économie'],
    'Diplomacy' => ['ar' => 'الدبلوماسية', 'fr' => 'Diplomatie'],
    'Media' => ['ar' => 'الإعلام والاتصالات', 'fr' => 'Médias et Communications']
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'الدوريات' : 'Revues' ?> - نظام المكتبة</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #059669;
            --primary-dark: #047857;
            --primary-light: #10b981;
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
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
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

        .filter-section {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid var(--gray-200);
        }

        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
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

        .magazine-title {
            font-weight: 600;
            color: var(--gray-900);
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .field-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .field-administration {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .field-law {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .field-economics {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .field-diplomacy {
            background: rgba(139, 92, 246, 0.1);
            color: #7c3aed;
        }

        .field-media {
            background: rgba(236, 72, 153, 0.1);
            color: #db2777;
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
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid rgba(5, 150, 105, 0.2);
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

        .action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .action-view {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .action-view:hover {
            background: #2563eb;
            color: white;
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
            
            .magazine-title {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-icon">📰</div>
        <h1 class="header-title"><?= $lang == 'ar' ? 'الدوريات' : 'Revues' ?></h1>
        <p><?= $lang == 'ar' ? 'إدارة وعرض الدوريات والمجلات المتخصصة' : 'Gestion et affichage des revues et magazines spécialisés' ?></p>
    </div>

    <div class="container">
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-row">
                <!-- Search Box -->
                <form method="get" class="search-box">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    <?php if (!empty($field_filter)): ?>
                        <input type="hidden" name="field" value="<?= htmlspecialchars($field_filter) ?>">
                    <?php endif; ?>
                    
                    <select name="search_field" class="search-select">
                        <option value="all" <?= $search_field === 'all' ? 'selected' : '' ?>>
                            <?= $lang == 'ar' ? 'جميع الحقول' : 'Tous les champs' ?>
                        </option>
                        <option value="title" <?= $search_field === 'title' ? 'selected' : '' ?>>
                            <?= $lang == 'ar' ? 'العنوان' : 'Titre' ?>
                        </option>
                        <option value="magazine_type" <?= $search_field === 'magazine_type' ? 'selected' : '' ?>>
                            <?= $lang == 'ar' ? 'النوع' : 'Type' ?>
                        </option>
                        <option value="publisher" <?= $search_field === 'publisher' ? 'selected' : '' ?>>
                            <?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?>
                        </option>
                        <option value="issue_number" <?= $search_field === 'issue_number' ? 'selected' : '' ?>>
                            <?= $lang == 'ar' ? 'العدد' : 'Numéro' ?>
                        </option>
                    </select>
                    
                    <input 
                        type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="<?= $lang == 'ar' ? 'ابحث عن دورية...' : 'Rechercher une revue...' ?>"
                        value="<?= htmlspecialchars($search) ?>"
                    >
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        <?= $lang == 'ar' ? 'بحث' : 'Rechercher' ?>
                    </button>
                    
                    <?php if (!empty($search) || !empty($field_filter)): ?>
                        <a href="?lang=<?= $lang ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                        </a>
                    <?php endif; ?>
                </form>
                
                <!-- Action Buttons -->
                <div style="display: flex; gap: 0.5rem;">
                    <a href="add_magazine.php?lang=<?= $lang ?>" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة دورية' : 'Ajouter' ?>
                    </a>
                    
                    <a href="../../dashboard.php?lang=<?= $lang ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
                        <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="get" class="filter-row">
                <input type="hidden" name="lang" value="<?= $lang ?>">
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="search_field" value="<?= htmlspecialchars($search_field) ?>">
                <?php endif; ?>
                
                <label style="font-weight: 600; color: var(--gray-800);">
                    <i class="fas fa-filter"></i>
                    <?= $lang == 'ar' ? 'تصفية حسب التخصص:' : 'Filtrer par spécialisation:' ?>
                </label>
                
                <select name="field" class="search-select" onchange="this.form.submit()">
                    <option value=""><?= $lang == 'ar' ? 'جميع التخصصات' : 'Toutes les spécialisations' ?></option>
                    <?php foreach ($fields as $field_key => $field_names): ?>
                        <option value="<?= $field_key ?>" <?= $field_filter === $field_key ? 'selected' : '' ?>>
                            <?= $field_names[$lang] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <?php if (!empty($field_filter)): ?>
                    <a href="?lang=<?= $lang ?><?= !empty($search) ? '&search=' . urlencode($search) . '&search_field=' . $search_field : '' ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        <?= $lang == 'ar' ? 'إلغاء الفلتر' : 'Annuler le filtre' ?>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Search Info -->
        <?php if (!empty($search) || !empty($field_filter)): ?>
            <div class="search-info">
                <div class="search-info-text">
                    <i class="fas fa-info-circle"></i>
                    <span>
                        <?php if (!empty($search)): ?>
                            <?= $lang == 'ar' ? 'نتائج البحث عن:' : 'Résultats de recherche pour:' ?>
                            <strong>"<?= htmlspecialchars($search) ?>"</strong>
                        <?php endif; ?>
                        <?php if (!empty($field_filter)): ?>
                            <?php if (!empty($search)): ?> - <?php endif; ?>
                            <?= $lang == 'ar' ? 'التخصص:' : 'Spécialisation:' ?>
                            <strong><?= $fields[$field_filter][$lang] ?? $field_filter ?></strong>
                        <?php endif; ?>
                        (<?= $total_magazines ?> <?= $lang == 'ar' ? 'نتيجة' : 'résultat(s)' ?>)
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Statistics Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <i class="fas fa-newspaper"></i>
                <span><?= $lang == 'ar' ? 'إجمالي الدوريات:' : 'Total des revues:' ?></span>
                <strong><?= $total_magazines ?></strong>
            </div>
            
            <div class="stat-item">
                <i class="fas fa-file-alt"></i>
                <span><?= $lang == 'ar' ? 'الصفحة:' : 'Page:' ?></span>
                <strong><?= $page ?> / <?= max(1, $total_pages) ?></strong>
            </div>
            
            <div class="stat-item">
                <i class="fas fa-list"></i>
                <span><?= $lang == 'ar' ? 'عرض:' : 'Affichage:' ?></span>
                <strong><?= count($magazines) ?> <?= $lang == 'ar' ? 'دورية' : 'revue(s)' ?></strong>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <?php if (empty($magazines)): ?>
                <div class="empty-state">
                    <i class="fas fa-newspaper"></i>
                    <h3><?= $lang == 'ar' ? 'لا توجد نتائج' : 'Aucun résultat' ?></h3>
                    <p>
                        <?php if (!empty($search) || !empty($field_filter)): ?>
                            <?= $lang == 'ar' ? 'لم يتم العثور على دوريات تطابق بحثك' : 'Aucune revue ne correspond à votre recherche' ?>
                        <?php else: ?>
                            <?= $lang == 'ar' ? 'لم يتم إضافة أي دوريات بعد' : 'Aucune revue n\'a été ajoutée pour le moment' ?>
                        <?php endif; ?>
                    </p>
                    <?php if (empty($search) && empty($field_filter)): ?>
                        <a href="add_magazine.php?lang=<?= $lang ?>" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                            <?= $lang == 'ar' ? 'إضافة أول دورية' : 'Ajouter la première revue' ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= $lang == 'ar' ? 'العنوان' : 'Titre' ?></th>
                                <th><?= $lang == 'ar' ? 'النوع' : 'Type' ?></th>
                                <th><?= $lang == 'ar' ? 'العدد' : 'Numéro' ?></th>
                                <th><?= $lang == 'ar' ? 'الناشر' : 'Éditeur' ?></th>
                                <th><?= $lang == 'ar' ? 'التخصص' : 'Spécialisation' ?></th>
                                <th><?= $lang == 'ar' ? 'تاريخ النشر' : 'Date' ?></th>
                                <th><?= $lang == 'ar' ? 'الإجراءات' : 'Actions' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($magazines as $index => $magazine): ?>
                                <tr>
                                    <td><?= $offset + $index + 1 ?></td>
                                    <td>
                                        <div class="magazine-title" title="<?= htmlspecialchars($magazine['title']) ?>">
                                            <?= htmlspecialchars($magazine['title']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($magazine['magazine_type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($magazine['issue_number'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($magazine['publisher'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($magazine['field'])): ?>
                                            <span class="field-badge field-<?= strtolower($magazine['field']) ?>">
                                                <?= $fields[$magazine['field']][$lang] ?? $magazine['field'] ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $magazine['publication_date'] ? date('Y-m-d', strtotime($magazine['publication_date'])) : '-' ?></td>
                                    <td>
                                        <?php if (!empty($magazine['periodical_file'])): ?>
                                            <a href="../../uploads/magazines/<?= htmlspecialchars($magazine['periodical_file']) ?>" 
                                               class="action-icon action-view" 
                                               title="<?= $lang == 'ar' ? 'عرض الملف' : 'Voir le fichier' ?>"
                                               target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--gray-400);">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?lang=<?= $lang ?>&page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) . '&search_field=' . $search_field : '' ?><?= !empty($field_filter) ? '&field=' . urlencode($field_filter) : '' ?>">
                        <i class="fas fa-chevron-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
                    </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?lang=<?= $lang ?>&page=1<?= !empty($search) ? '&search=' . urlencode($search) . '&search_field=' . $search_field : '' ?><?= !empty($field_filter) ? '&field=' . urlencode($field_filter) : '' ?>">1</a>
                    <?php if ($start_page > 2): ?>
                        <span>...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?lang=<?= $lang ?>&page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) . '&search_field=' . $search_field : '' ?><?= !empty($field_filter) ? '&field=' . urlencode($field_filter) : '' ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span>...</span>
                    <?php endif; ?>
                    <a href="?lang=<?= $lang ?>&page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) . '&search_field=' . $search_field : '' ?><?= !empty($field_filter) ? '&field=' . urlencode($field_filter) : '' ?>"><?= $total_pages ?></a>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?lang=<?= $lang ?>&page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) . '&search_field=' . $search_field : '' ?><?= !empty($field_filter) ? '&field=' . urlencode($field_filter) : '' ?>">
                        <i class="fas fa-chevron-<?= $lang == 'ar' ? 'left' : 'right' ?>"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // تركيز على حقل البحث
            const searchInput = document.querySelector('.search-input');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
            
            console.log('✅ صفحة الدوريات محملة');
            console.log('📰 عدد الدوريات:', <?= count($magazines) ?>);
            console.log('🔍 البحث:', '<?= htmlspecialchars($search) ?>');
        });
    </script>
</body>
</html>
