<?php
/* modules/items/list_general_books.php */
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: ../../login.php');
    exit;
}

try {
    require_once __DIR__ . '/../../../config/db.php';
    $db_connected = true;
} catch (Exception $e) {
    $db_connected = false;
}

$lang         = $_GET['lang']         ?? 'ar';
$page         = max(1, (int)($_GET['page'] ?? 1));
$limit        = 20;
$offset       = ($page - 1) * $limit;
$search       = trim($_GET['search']       ?? '');
$search_field = $_GET['search_field']      ?? 'all';

$books       = [];
$total_books = 0;

if ($db_connected) {
    try {
        $where  = ["type = 'book'", "(book_type = 'general' OR book_type IS NULL OR book_type = '')"];
        $params = [];

        if (!empty($search)) {
            if ($search_field === 'all') {
                $where[]           = "(main_title LIKE :search OR subtitle LIKE :search OR author LIKE :search OR publisher LIKE :search OR isbn LIKE :search)";
                $params[':search'] = "%$search%";
            } else {
                $where[]           = "$search_field LIKE :search";
                $params[':search'] = "%$search%";
            }
        }

        $where_sql = implode(' AND ', $where);

        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE $where_sql");
        $count_stmt->execute($params);
        $total_books = (int)$count_stmt->fetchColumn();

        $sql = "SELECT id, main_title, subtitle, short_title, author, publisher,
                       publication_place, publication_year, copies, available_copies,
                       isbn, classification_number, shelf_number, drawer_number,
                       parts, registration_date, modification_date, lang, created_at
                FROM items WHERE $where_sql
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $db_error = $e->getMessage();
    }
}

$total_pages = max(1, (int)ceil($total_books / $limit));

function pager_qs(string $lang, int $p, string $search, string $sf): string {
    $q = "lang=$lang&page=$p";
    if ($search) $q .= '&search=' . urlencode($search) . '&search_field=' . $sf;
    return '?' . $q;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'الكتب العامة' : 'Livres Généraux' ?> - نظام المكتبة</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:       #4f46e5;
            --primary-dark:  #3730a3;
            --primary-light: #4f46e522;
            --gray-50:  #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --white:    #ffffff;
            --shadow-sm: 0 1px 2px rgba(0,0,0,.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,.10);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.10);
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ── Header ── */
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
            content:''; position:absolute; inset:0;
            background:url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.05)"/></svg>');
            opacity:.3;
        }
        .header-content  { position:relative; z-index:1; }
        .header-icon     { font-size:3.5rem; margin-bottom:1rem; animation:float 3s ease-in-out infinite; display:block; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
        .header-title    { font-size:2.2rem; font-weight:800; margin-bottom:.5rem; text-shadow:0 2px 4px rgba(0,0,0,.1); }
        .header-subtitle { font-size:1rem; opacity:.95; }

        /* ── Container ── */
        .container { max-width:1400px; margin:0 auto; padding:2rem; }

        /* ── Back link ── */
        .back-link {
            display: inline-flex; align-items:center; gap:.5rem;
            padding:.75rem 1.5rem; background:var(--white); color:var(--gray-600);
            text-decoration:none; border-radius:10px; font-weight:600;
            transition:all .3s ease; box-shadow:var(--shadow-sm); margin-bottom:2rem;
        }
        .back-link:hover {
            background:var(--gray-100); color:var(--gray-800);
            transform:translateX(<?= $lang=='ar'?'5px':'-5px'?>);
            box-shadow:var(--shadow-md);
        }

        /* ── Search section ── */
        .search-section { background:var(--white); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem; box-shadow:var(--shadow-md); }
        .search-form    { display:flex; gap:1rem; align-items:center; flex-wrap:wrap; }
        .search-select  { padding:.875rem 1rem; border:2px solid var(--gray-200); border-radius:10px; font-size:.875rem; background:var(--white); color:var(--gray-800); min-width:160px; transition:border-color .2s; }
        .search-select:focus { outline:none; border-color:var(--primary); }
        .search-input   { flex:1; padding:.875rem 1.25rem; border:2px solid var(--gray-200); border-radius:10px; font-size:1rem; transition:all .2s ease; min-width:220px; }
        .search-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(79,70,229,.12); }

        /* ── Buttons ── */
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.875rem 1.5rem; border:none; border-radius:10px; font-weight:600; cursor:pointer; transition:all .2s ease; text-decoration:none; font-size:.875rem; white-space:nowrap; }
        .btn-primary   { background:var(--primary); color:var(--white); }
        .btn-primary:hover { background:var(--primary-dark); transform:translateY(-2px); box-shadow:var(--shadow-md); }
        .btn-secondary { background:var(--gray-100); color:var(--gray-600); border:1.5px solid var(--gray-200); }
        .btn-secondary:hover { background:var(--gray-200); }

        /* ── Search info ── */
        .search-info { background:var(--primary-light); border:1px solid rgba(79,70,229,.2); border-radius:8px; padding:.75rem 1rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem; color:var(--primary); font-size:.875rem; }

        /* ── Stats bar ── */
        .stats-bar { display:flex; justify-content:space-between; align-items:center; background:var(--white); padding:1.25rem 1.5rem; border-radius:12px; margin-bottom:2rem; box-shadow:var(--shadow-sm); flex-wrap:wrap; gap:1rem; }
        .stat-item  { display:flex; align-items:center; gap:.75rem; }
        .stat-icon  { font-size:2rem; color:var(--primary); }
        .stat-info h4 { font-size:.875rem; color:var(--gray-600); font-weight:500; }
        .stat-info p  { font-size:1.5rem; font-weight:700; color:var(--gray-900); }

        /* ── Table ── */
        .table-card { background:var(--white); border-radius:12px; overflow:hidden; box-shadow:var(--shadow-md); }
        .table-header { background:var(--primary-light); padding:1.25rem 1.5rem; border-bottom:2px solid var(--primary); }
        .table-header h3 { color:var(--primary); font-size:1.1rem; font-weight:700; display:flex; align-items:center; gap:.5rem; }
        .table-wrapper { overflow-x:auto; }
        table  { width:100%; border-collapse:collapse; }
        thead  { background:var(--gray-50); }
        th {
            padding:1rem 1.25rem;
            text-align:<?= $lang=='ar'?'right':'left'?>;
            font-weight:700; color:var(--gray-700);
            font-size:.8rem; text-transform:uppercase; letter-spacing:.5px;
            border-bottom:2px solid var(--gray-200); white-space:nowrap;
        }
        td { padding:1.1rem 1.25rem; border-bottom:1px solid var(--gray-100); color:var(--gray-700); font-size:.875rem; }
        tbody tr { transition:background .2s ease; }
        tbody tr:hover { background:var(--gray-50); }

        .book-title  { font-weight:600; color:var(--gray-900); }
        .book-author { color:var(--gray-600); font-size:.8rem; margin-top:.2rem; }

        .badge-lang { display:inline-flex; align-items:center; padding:.2rem .65rem; border-radius:20px; font-size:.72rem; font-weight:600; background:var(--primary-light); color:var(--primary); }

        /* ── Details expand ── */
        .details-btn { border:1px solid var(--primary); background:var(--primary-light); color:var(--primary); border-radius:8px; padding:.4rem .75rem; font-size:.8rem; font-weight:600; cursor:pointer; transition:all .2s ease; }
        .details-btn:hover { background:var(--primary); color:var(--white); }
        .details-row      { display:none; background:var(--gray-50); }
        .details-row.open { display:table-row; }
        .details-card { background:var(--white); border:1px solid var(--gray-200); border-radius:10px; padding:1rem 1.25rem; }
        .details-title { color:var(--gray-900); font-size:.9rem; font-weight:700; margin-bottom:.75rem; }
        .details-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:.75rem 1.25rem; font-size:.875rem; }
        .detail-item strong { color:var(--gray-700); display:block; margin-bottom:.15rem; font-size:.78rem; text-transform:uppercase; letter-spacing:.03em; }
        .detail-item span   { color:var(--gray-900); }

        /* ── Empty ── */
        .empty-state { text-align:center; padding:4rem 2rem; color:var(--gray-600); }
        .empty-icon  { font-size:4rem; margin-bottom:1rem; opacity:.45; }

        /* ── Pagination ── */
        .pagination { display:flex; justify-content:center; align-items:center; gap:.5rem; margin-top:2rem; padding:1.25rem; background:var(--white); border-radius:12px; box-shadow:var(--shadow-sm); flex-wrap:wrap; }
        .pagination a, .pagination span { display:inline-flex; align-items:center; justify-content:center; min-width:2.4rem; height:2.4rem; padding:0 .65rem; border-radius:8px; text-decoration:none; font-weight:500; font-size:.875rem; transition:all .2s ease; }
        .pagination a       { background:var(--gray-100); color:var(--gray-600); border:1px solid var(--gray-200); }
        .pagination a:hover { background:var(--primary); color:var(--white); border-color:var(--primary); }
        .pagination .active { background:var(--primary); color:var(--white); border:1px solid var(--primary); }
        .pagination .dots   { background:transparent; border:none; color:var(--gray-400); }

        /* ── Fade in ── */
        .fade-in { animation:fadeIn .6s ease-out both; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

        @media (max-width:768px) {
            .container { padding:1rem; }
            .header-title { font-size:1.5rem; }
            .search-form { flex-direction:column; align-items:stretch; }
            .search-input,.search-select,.btn { width:100%; justify-content:center; }
            .stats-bar { flex-direction:column; }
            th,td { padding:.65rem .75rem; }
        }
    </style>
</head>
<body>

<!-- ── Header ── -->
<div class="page-header">
    <div class="header-content">
        <span class="header-icon">📚</span>
        <h1 class="header-title"><?= $lang == 'ar' ? 'الكتب العامة' : 'Livres Généraux' ?></h1>
        <p class="header-subtitle"><?= $lang == 'ar' ? 'مجموعة الكتب الثقافية والعامة' : 'Collection de livres culturels et généraux' ?></p>
    </div>
</div>

<div class="container">

    <!-- ── Back link ── -->
    <a href="../../dashboard.php?lang=<?= $lang ?>" class="back-link fade-in">
        <i class="fas fa-arrow-<?= $lang == 'ar' ? 'right' : 'left' ?>"></i>
        <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
    </a>

    <!-- ── Search ── -->
    <div class="search-section fade-in" style="animation-delay:.1s">
        <form method="GET" class="search-form">
            <input type="hidden" name="lang" value="<?= $lang ?>">

            <select name="search_field" class="search-select">
                <option value="all"       <?= $search_field==='all'       ?'selected':'' ?>><?= $lang=='ar'?'جميع الحقول':'Tous les champs' ?></option>
                <option value="main_title"<?= $search_field==='main_title'?'selected':'' ?>><?= $lang=='ar'?'العنوان':'Titre' ?></option>
                <option value="author"    <?= $search_field==='author'    ?'selected':'' ?>><?= $lang=='ar'?'المؤلف':'Auteur' ?></option>
                <option value="publisher" <?= $search_field==='publisher' ?'selected':'' ?>><?= $lang=='ar'?'الناشر':'Éditeur' ?></option>
                <option value="isbn"      <?= $search_field==='isbn'      ?'selected':'' ?>>ISBN</option>
            </select>

            <input type="text" name="search" class="search-input"
                   placeholder="<?= $lang=='ar'?'🔍 ابحث عن كتاب...':'🔍 Rechercher un livre...' ?>"
                   value="<?= htmlspecialchars($search) ?>">

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
                <?= $lang=='ar'?'بحث':'Rechercher' ?>
            </button>

            <?php if (!empty($search)): ?>
                <a href="?lang=<?= $lang ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <?= $lang=='ar'?'إلغاء':'Annuler' ?>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- ── Search info ── -->
    <?php if (!empty($search)): ?>
        <div class="search-info fade-in" style="animation-delay:.15s">
            <i class="fas fa-info-circle"></i>
            <?= $lang=='ar'?'نتائج البحث عن:':'Résultats pour:' ?>
            <strong>"<?= htmlspecialchars($search) ?>"</strong>
            &nbsp;(<?= $total_books ?> <?= $lang=='ar'?'نتيجة':'résultat(s)' ?>)
        </div>
    <?php endif; ?>

    <!-- ── Stats bar ── -->
    <div class="stats-bar fade-in" style="animation-delay:.2s">
        <div class="stat-item">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <h4><?= $lang=='ar'?'إجمالي الكتب':'Total des livres' ?></h4>
                <p><?= $total_books ?></p>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">📄</div>
            <div class="stat-info">
                <h4><?= $lang=='ar'?'في هذه الصفحة':'Sur cette page' ?></h4>
                <p><?= count($books) ?></p>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon"><?= $lang=='ar'?'🇸🇦':'🇫🇷' ?></div>
            <div class="stat-info">
                <h4><?= $lang=='ar'?'الصفحة':'Page' ?></h4>
                <p><?= $page ?> / <?= $total_pages ?></p>
            </div>
        </div>
    </div>

    <!-- ── Table ── -->
    <div class="table-card fade-in" style="animation-delay:.3s">
        <div class="table-header">
            <h3>
                <i class="fas fa-list"></i>
                <?= $lang=='ar'?'قائمة الكتب':'Liste des livres' ?>
                <?php if (!empty($search)): ?>
                    <span style="font-weight:400;font-size:.85rem;">(<?= $lang=='ar'?'بحث:':'Recherche:' ?> "<?= htmlspecialchars($search) ?>")</span>
                <?php endif; ?>
            </h3>
        </div>

        <?php if (empty($books)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3 style="color:var(--gray-700);margin-bottom:.5rem;">
                    <?= $lang=='ar'?'لا توجد كتب':'Aucun livre trouvé' ?>
                </h3>
                <p><?= !empty($search)
                    ? ($lang=='ar'?'لم يتم العثور على نتائج لبحثك':'Aucun résultat pour votre recherche')
                    : ($lang=='ar'?'لم يتم إضافة أي كتب عامة بعد':"Aucun livre général n'a été ajouté pour le moment") ?></p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= $lang=='ar'?'العنوان':'Titre' ?></th>
                            <th><?= $lang=='ar'?'المؤلف':'Auteur' ?></th>
                            <th><?= $lang=='ar'?'دار النشر':'Éditeur' ?></th>
                            <th><?= $lang=='ar'?'سنة النشر':'Année' ?></th>
                            <th>ISBN</th>
                            <th><?= $lang=='ar'?'النسخ':'Copies' ?></th>
                            <th><?= $lang=='ar'?'اللغة':'Langue' ?></th>
                            <th><?= $lang=='ar'?'تفاصيل':'Détails' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $idx => $b):
                            $row_id = 'gb-det-' . (int)$b['id'] . '-' . $idx;
                        ?>
                        <tr>
                            <td style="font-weight:600;color:var(--primary);"><?= $offset + $idx + 1 ?></td>
                            <td>
                                <div class="book-title"><?= htmlspecialchars($b['main_title'] ?? '-') ?></div>
                                <?php if (!empty($b['subtitle'])): ?>
                                    <div class="book-author"><?= htmlspecialchars($b['subtitle']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($b['author'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($b['publisher'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($b['publication_year'] ?? '-') ?></td>
                            <td style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($b['isbn'] ?? '-') ?></td>
                            <td>
                                <span style="font-weight:600;color:var(--primary);"><?= htmlspecialchars($b['available_copies'] ?? '0') ?></span>
                                / <?= htmlspecialchars($b['copies'] ?? '0') ?>
                            </td>
                            <td><span class="badge-lang"><?= ($b['lang'] ?? 'ar') == 'ar' ? 'AR' : 'FR' ?></span></td>
                            <td>
                                <button type="button" class="details-btn" data-target="<?= $row_id ?>">
                                    <i class="fas fa-eye"></i>
                                    <?= $lang=='ar'?'عرض':'Voir' ?>
                                </button>
                            </td>
                        </tr>
                        <tr id="<?= $row_id ?>" class="details-row">
                            <td colspan="9">
                                <div class="details-card">
                                    <div class="details-title">
                                        <i class="fas fa-book" style="color:var(--primary);margin-<?= $lang=='ar'?'left':'right' ?>:.4rem;"></i>
                                        <?= $lang=='ar'?'المعلومات التفصيلية للكتاب':'Informations détaillées du livre' ?>
                                    </div>
                                    <div class="details-grid">
                                        <div class="detail-item">
                                            <strong><?= $lang=='ar'?'العنوان المختصر':'Titre abrégé' ?></strong>
                                            <span><?= htmlspecialchars($b['short_title'] ?? '-') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong><?= $lang=='ar'?'مكان النشر':'Lieu de publication' ?></strong>
                                            <span><?= htmlspecialchars($b['publication_place'] ?? '-') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong><?= $lang=='ar'?'رقم التصنيف':'Classification' ?></strong>
                                            <span><?= htmlspecialchars($b['classification_number'] ?? '-') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong><?= $lang=='ar'?'رقم الرف':'N° étagère' ?></strong>
                                            <span><?= htmlspecialchars($b['shelf_number'] ?? '-') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong><?= $lang=='ar'?'رقم الدرج':'N° tiroir' ?></strong>
                                            <span><?= htmlspecialchars($b['drawer_number'] ?? '-') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong><?= $lang=='ar'?'عدد الأجزاء':'Nombre de parties' ?></strong>
                                            <span><?= htmlspecialchars($b['parts'] ?? '-') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong><?= $lang=='ar'?'تاريخ التسجيل':'Date d\'enregistrement' ?></strong>
                                            <span><?= htmlspecialchars($b['registration_date'] ?? '-') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong><?= $lang=='ar'?'تاريخ التعديل':'Date de modification' ?></strong>
                                            <span><?= htmlspecialchars($b['modification_date'] ?? '-') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong><?= $lang=='ar'?'تاريخ الإضافة':'Date d\'ajout' ?></strong>
                                            <span><?= htmlspecialchars($b['created_at'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Pagination ── -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination fade-in" style="animation-delay:.4s">
            <?php if ($page > 1): ?>
                <a href="<?= pager_qs($lang, $page-1, $search, $search_field) ?>">
                    <i class="fas fa-chevron-<?= $lang=='ar'?'right':'left' ?>"></i>
                </a>
            <?php endif; ?>

            <?php
                $sp = max(1, $page-2);
                $ep = min($total_pages, $page+2);
                if ($sp > 1): ?>
                    <a href="<?= pager_qs($lang, 1, $search, $search_field) ?>">1</a>
                    <?php if ($sp > 2): ?><span class="dots">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $sp; $i <= $ep; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= pager_qs($lang, $i, $search, $search_field) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($ep < $total_pages): ?>
                <?php if ($ep < $total_pages-1): ?><span class="dots">…</span><?php endif; ?>
                <a href="<?= pager_qs($lang, $total_pages, $search, $search_field) ?>"><?= $total_pages ?></a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="<?= pager_qs($lang, $page+1, $search, $search_field) ?>">
                    <i class="fas fa-chevron-<?= $lang=='ar'?'left':'right' ?>"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const si = document.querySelector('.search-input');
    if (si) {
        if (!si.value) si.focus();
        let timer;
        si.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { si.closest('form').submit(); }, 500);
        });
    }

    document.querySelectorAll('.details-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.getElementById(btn.dataset.target);
            if (!target) return;
            const isOpen = target.classList.contains('open');
            document.querySelectorAll('.details-row.open').forEach(r => r.classList.remove('open'));
            if (!isOpen) target.classList.add('open');
        });
    });
});
</script>
</body>
</html>
