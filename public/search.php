<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'ar';
if (in_array($lang, ['ar', 'fr'])) $_SESSION['lang'] = $lang;

$q = trim($_GET['q'] ?? '');
$section = $_GET['section'] ?? 'all';
$allowed = ['all','books','magazines','newspapers','research'];
if (!in_array($section, $allowed)) $section = 'all';
$results = ['items' => [], 'research' => []];

if ($q !== '') {
    require_once __DIR__ . '/../../config/db.php';
    $param = "%{$q}%";

    // Search items (books / magazines / newspapers)
    try {
        $sql = "SELECT id, title, author, type, lang, available_copies, copies, publisher, classification, year_pub FROM items WHERE (title LIKE ? OR author LIKE ? OR publisher LIKE ? OR classification LIKE ?)";
        $params = [$param, $param, $param, $param];
        if ($section === 'books') {
            $sql .= " AND type = 'book'";
        } elseif ($section === 'magazines') {
            $sql .= " AND type = 'magazine'";
        } elseif ($section === 'newspapers') {
            $sql .= " AND type = 'newspaper'";
        }
        $sql .= " ORDER BY title LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $results['items'] = [];
    }

    // Search student research (only when section isn't restricted to items)
    try {
        if ($section !== 'books' && $section !== 'magazines' && $section !== 'newspapers') {
            $stmt2 = $pdo->prepare("SELECT id, student_name, nni, title, specialization, summary, pdf_file, created_at FROM student_research WHERE (student_name LIKE ? OR nni LIKE ? OR title LIKE ? OR summary LIKE ?) ORDER BY id DESC LIMIT 100");
            $stmt2->execute([$param, $param, $param, $param]);
            $results['research'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $results['research'] = [];
    }
}

function h($s) { return htmlspecialchars($s); }
?>
<!doctype html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $lang == 'ar' ? 'بحث' : 'Recherche' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body{font-family:Segoe UI, Tahoma, sans-serif;padding:2rem;background:#f7fafc}
        .container{max-width:1000px;margin:0 auto;background:white;padding:1.5rem;border-radius:12px}
        .search-bar{display:flex;gap:0.75rem;margin-bottom:1rem}
        .search-input{flex:1;padding:0.75rem;border:1px solid #e5e7eb;border-radius:8px}
        .search-btn{padding:0.75rem 1rem;background:#667eea;color:#fff;border:none;border-radius:8px}
        .section{margin-top:1.25rem}
        .card{border:1px solid #e6edf3;padding:1rem;border-radius:8px;margin-bottom:0.75rem;background:white}
        .meta{color:#6b7280;font-size:0.9rem}
    </style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/professional-theme.css">
</head>
<body>
    <div class="container">
        <form method="GET" class="search-bar" action="search.php">
            <input class="search-input" name="q" value="<?=h($q)?>" placeholder="<?= $lang == 'ar' ? 'ابحث في كل المصادر...' : 'Rechercher dans toutes les ressources...' ?>">
            <button class="search-btn" type="submit"><i class="fas fa-search"></i> <?= $lang == 'ar' ? 'بحث' : 'Rechercher' ?></button>
        </form>

        <?php if ($q === ''): ?>
            <p class="meta"><?= $lang == 'ar' ? 'أدخل نص البحث للبدء.' : 'Entrez un terme de recherche pour commencer.' ?></p>
        <?php else: ?>
            <h2><?= $lang == 'ar' ? 'نتائج البحث' : 'Résultats de recherche' ?> «<?= h($q) ?>»</h2>

            <style>
                .filters {display:flex;gap:0.5rem;margin:0.75rem 0 1.25rem 0}
                .filter-btn{display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;border-radius:8px;background:#eef2ff;color:#1f2937;text-decoration:none;border:1px solid #e6e8ff}
                .filter-btn.active{background:#667eea;color:white;border-color:#5a67d8}
                .filter-icon{font-size:1.05rem}
                .list-link{float:right;font-size:0.9rem}
            </style>

            <div class="filters">
                <?php
                    $base = 'search.php?q='.urlencode($q).'&lang='.urlencode($lang);
                    $sections = ['all' => ['icon'=>'fas fa-list','label'=> $lang=='ar' ? 'الكل' : 'Tous'],
                                 'books' => ['icon'=>'fas fa-book','label'=> $lang=='ar' ? 'الكتب' : 'Livres'],
                                 'magazines' => ['icon'=>'fas fa-book-open','label'=> $lang=='ar' ? 'المجلات' : 'Revues'],
                                 'newspapers' => ['icon'=>'fas fa-newspaper','label'=> $lang=='ar' ? 'الجرائد' : 'Journaux'],
                                 'research' => ['icon'=>'fas fa-file-pdf','label'=> $lang=='ar' ? 'الأبحاث' : 'Recherches']];
                    foreach ($sections as $key => $meta) {
                        $active = $section === $key ? 'active' : '';
                        echo "<a class=\"filter-btn $active\" href=\"{$base}&section={$key}\"><i class=\"{$meta['icon']} filter-icon\"></i> {$meta['label']}</a>";
                    }
                ?>
            </div>

            <div class="section">
                <h3><?= $lang == 'ar' ? 'الكتب والمجلات والجرائد' : 'Livres, revues & journaux' ?> (<?= count($results['items']) ?>)
                    <span class="list-link">
                        <?php if ($section === 'books'): ?>
                            <a href="modules/items/list_general_books.php?lang=<?= urlencode($lang) ?>"><?= $lang == 'ar' ? 'عرض القائمة الكاملة للكتب' : 'Voir la liste complète (Livres)' ?></a>
                        <?php elseif ($section === 'magazines'): ?>
                            <a href="modules/items/list_magazines_specialization.php?category=general&lang=<?= urlencode($lang) ?>"><?= $lang == 'ar' ? 'عرض القائمة الكاملة للمجلات' : 'Voir la liste complète (Revues)' ?></a>
                        <?php elseif ($section === 'newspapers'): ?>
                            <a href="modules/items/list_newspapers.php?lang=<?= urlencode($lang) ?>"><?= $lang == 'ar' ? 'عرض القائمة الكاملة للجرائد' : 'Voir la liste complète (Journaux)' ?></a>
                        <?php else: ?>
                            <a href="modules/items/list_general_books.php?lang=<?= urlencode($lang) ?>"><?= $lang == 'ar' ? 'الكتب' : 'Livres' ?></a>
                            &nbsp;|&nbsp;
                            <a href="modules/items/list_magazines_specialization.php?category=general&lang=<?= urlencode($lang) ?>"><?= $lang == 'ar' ? 'المجلات' : 'Revues' ?></a>
                            &nbsp;|&nbsp;
                            <a href="modules/items/list_newspapers.php?lang=<?= urlencode($lang) ?>"><?= $lang == 'ar' ? 'الجرائد' : 'Journaux' ?></a>
                        <?php endif; ?>
                    </span>
                </h3>
                <?php if (empty($results['items'])): ?>
                    <div class="meta"><?= $lang == 'ar' ? 'لا توجد نتائج.' : 'Aucun résultat.' ?></div>
                <?php else: foreach ($results['items'] as $it): ?>
                    <div class="card">
                        <div style="font-weight:600"><?= h($it['title']) ?></div>
                        <div class="meta"><?= h($it['author'] ?? '') ?> • <?= h($it['publisher'] ?? '') ?> • <?= h($it['year_pub'] ?? '') ?></div>
                        <div class="meta">Type: <?= h($it['type']) ?> • Lang: <?= h($it['lang']) ?> • Copies: <?= (int)$it['available_copies'] ?>/<?= (int)$it['copies'] ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <div class="section">
                <h3><?= $lang == 'ar' ? 'أبحاث الطلبة' : 'Recherches étudiantes' ?> (<?= count($results['research']) ?>)
                    <span class="list-link">
                        <a href="modules/items/listrecherche.php?lang=<?= urlencode($lang) ?>"><?= $lang == 'ar' ? 'عرض القائمة الكاملة' : 'Voir la liste complète' ?></a>
                    </span>
                </h3>
                <?php if (empty($results['research'])): ?>
                    <div class="meta"><?= $lang == 'ar' ? 'لا توجد نتائج.' : 'Aucun résultat.' ?></div>
                <?php else: foreach ($results['research'] as $r): ?>
                    <div class="card">
                        <div style="font-weight:600"><?= h($r['title']) ?></div>
                        <div class="meta"><?= $lang == 'ar' ? 'المؤلف' : 'Auteur' ?>: <?= h($r['student_name']) ?> (<?= h($r['nni']) ?>)</div>
                        <div class="meta"><?= $lang == 'ar' ? 'تخصص' : 'Spécialisation' ?>: <?= h($r['specialization']) ?> • <?= date('Y-m-d', strtotime($r['created_at'])) ?></div>
                        <?php if (!empty($r['pdf_file'])): ?>
                            <div style="margin-top:0.5rem"><a href="../../uploads/research/<?= h($r['pdf_file']) ?>" target="_blank"><?= $lang == 'ar' ? 'عرض PDF' : 'Voir le PDF' ?></a></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        <?php endif; ?>
    </div>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>
