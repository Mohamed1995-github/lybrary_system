<?php
/* modules/items/list_books_specialization.php - Liste des livres par spécialisation */
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';
$specialization = $_GET['specialization'] ?? 'administration';
$category = $_GET['category'] ?? 'specialized';
$search = trim($_GET['search'] ?? '');

// Mapper les spécialisations
$specialization_map = [
    'administration' => [
        'ar' => 'الإدارة العامة',
        'fr' => 'Administration Publique',
        'icon' => 'fas fa-building'
    ],
    'law' => [
        'ar' => 'القانون',
        'fr' => 'Droit',
        'icon' => 'fas fa-gavel'
    ],
    'economics' => [
        'ar' => 'الاقتصاد',
        'fr' => 'Économie',
        'icon' => 'fas fa-chart-line'
    ],
    'diplomacy' => [
        'ar' => 'الدبلوماسية',
        'fr' => 'Diplomatie',
        'icon' => 'fas fa-handshake'
    ]
];

$current_specialization = $specialization_map[$specialization] ?? $specialization_map['administration'];

// Paramètres de pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Récupérer les livres de cette spécialisation
try {
    $field_name = ucfirst($specialization);
    
    // Construire la clause WHERE avec recherche
    $where_conditions = "type = 'book' AND field = ? AND lang = ?";
    $params = [$field_name, $lang];
    
    if (!empty($search)) {
        $where_conditions .= " AND (title LIKE ? OR author LIKE ? OR id = ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search;
    }
    
    // Compter le total pour cette spécialisation
    $count_sql = "SELECT COUNT(*) as total FROM items WHERE {$where_conditions}";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_books = $count_stmt->fetch()['total'];
    
    // Récupérer les livres avec pagination
    $sql = "
        SELECT id, title, author, publisher, publication_year, copies, available_copies, created_at, isbn 
        FROM items 
        WHERE {$where_conditions}
        ORDER BY title ASC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll();
    
    $total_pages = ceil($total_books / $limit);
    
} catch (PDOException $e) {
    $books = [];
    $total_books = 0;
    $total_pages = 0;
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'قائمة الكتب - ' . $current_specialization['ar'] : 'Liste des Livres - ' . $current_specialization['fr'] ?> - Library System</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    background-color: white !important;
    background: white !important;
}

html {
    background-color: white !important;
    background: white !important;
}

* {
    box-sizing: border-box;
}

.container, .list-container, .stats-bar, .books-grid, .book-card {
    background-color: white !important;
    background: white !important;
}

.list-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    background-color: white;
}

.list-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    text-align: center;
}

.list-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
}

.list-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.stats-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.search-bar {
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.search-form {
    display: flex;
    justify-content: center;
}

.search-input-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    max-width: 500px;
    width: 100%;
}

.search-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
}

.search-btn {
    padding: 0.75rem 1rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.search-btn:hover {
    background: #5a67d8;
}

.clear-btn {
    padding: 0.75rem 1rem;
    background: #6b7280;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.2s;
}

.clear-btn:hover {
    background: #4b5563;
}

.stats-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stats-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
}

.stats-label {
    color: #6b7280;
    font-size: 0.9rem;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.book-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}

.book-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.book-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.book-author {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.book-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.85rem;
    color: #6b7280;
}

.book-detail {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.book-availability {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    background: #f8fafc;
    border-radius: 6px;
    font-size: 0.85rem;
}

.availability-text {
    color: #6b7280;
}

.availability-number {
    font-weight: 600;
    color: #16a34a;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.pagination a, .pagination span {
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination a {
    background: #f3f4f6;
    color: #374151;
}

.pagination a:hover {
    background: #667eea;
    color: white;
}

.pagination .current {
    background: #667eea;
    color: white;
}

.pagination .disabled {
    background: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #6b7280;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    margin-bottom: 2rem;
}

.back-button:hover {
    background: #4b5563;
    color: white;
    text-decoration: none;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

.empty-state h3 {
    margin: 0 0 0.5rem 0;
    color: #374151;
}

.empty-state p {
    margin: 0;
}

@media (max-width: 768px) {
    .books-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-bar {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .book-details {
        grid-template-columns: 1fr;
    }
}
</style>
<link rel="stylesheet" href="assets/css/professional-theme.css">
</head>
<body>
    <div class="list-container">
        <a href="manage_books.php?specialization=<?=$specialization?>&category=<?=$category?>&lang=<?=$lang?>" class="back-button">
            <i class="fas fa-arrow-left"></i>
            <?= $lang == 'ar' ? 'العودة لإدارة الكتب' : 'Retour à la gestion des livres' ?>
        </a>

        <div class="list-header">
            <div style="font-size: 3rem; margin-bottom: 1rem;">
                <i class="<?= $current_specialization['icon'] ?>"></i>
            </div>
            <h1><?= $current_specialization[$lang] ?></h1>
            <p><?= $lang == 'ar' ? 'قائمة الكتب المتخصصة' : 'Liste des livres spécialisés' ?></p>
        </div>

        <div class="stats-bar">
            <div class="stats-info">
                <div class="stats-number"><?= $total_books ?></div>
                <div class="stats-label">
                    <?= $lang == 'ar' ? 'إجمالي الكتب' : 'Total des livres' ?>
                </div>
            </div>
            
            <div class="stats-info">
                <a href="add_book_simple.php?specialization=<?=$specialization?>&category=<?=$category?>&lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة كتاب' : 'Ajouter un livre' ?>
                </a>
            </div>
        </div>

        <!-- شريط البحث -->
        <div class="search-bar">
            <form method="GET" class="search-form">
                <input type="hidden" name="specialization" value="<?= $specialization ?>">
                <input type="hidden" name="category" value="<?= $category ?>">
                <input type="hidden" name="lang" value="<?= $lang ?>">
                <div class="search-input-group">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="<?= $lang == 'ar' ? 'البحث بالاسم أو رقم التسلسل...' : 'Rechercher par nom ou numéro...' ?>"
                           class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="?specialization=<?= $specialization ?>&category=<?= $category ?>&lang=<?= $lang ?>" class="clear-btn">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if (empty($books)): ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3><?= $lang == 'ar' ? 'لا توجد كتب' : 'Aucun livre trouvé' ?></h3>
                <p><?= $lang == 'ar' ? 'لم يتم إضافة أي كتب في هذا التخصص بعد' : 'Aucun livre n\'a été ajouté dans cette spécialisation pour le moment' ?></p>
                <a href="add_book_simple.php?specialization=<?=$specialization?>&category=<?=$category?>&lang=<?=$lang?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة أول كتاب' : 'Ajouter le premier livre' ?>
                </a>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                        
                        <div class="book-author">
                            <i class="fas fa-user"></i>
                            <?= htmlspecialchars($book['author']) ?>
                        </div>
                        
                        <div class="book-details">
                            <div class="book-detail">
                                <i class="fas fa-tag"></i>
                                <span><?= $lang == 'ar' ? 'رقم التصنيف: ' : 'N° Classification: ' ?><?= htmlspecialchars($book['isbn'] ?? 'غير محدد') ?></span>
                            </div>
                            
                            <div class="book-detail">
                                <i class="fas fa-building"></i>
                                <span><?= htmlspecialchars($book['publisher']) ?></span>
                            </div>
                            
                            <div class="book-detail">
                                <i class="fas fa-calendar"></i>
                                <span><?= htmlspecialchars($book['publication_year']) ?></span>
                            </div>
                            
                            <div class="book-detail">
                                <i class="fas fa-hashtag"></i>
                                <span>ID: <?= htmlspecialchars($book['id']) ?></span>
                            </div>
                            
                            <div class="book-detail">
                                <i class="fas fa-clock"></i>
                                <span><?= date('Y-m-d', strtotime($book['created_at'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="book-availability">
                            <span class="availability-text">
                                <?= $lang == 'ar' ? 'متاح' : 'Disponible' ?>
                            </span>
                            <span class="availability-number">
                                <?= $book['available_copies'] ?> / <?= $book['copies'] ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?specialization=<?=$specialization?>&category=<?=$category?>&lang=<?=$lang?>&page=<?=$page-1?>">
                            <i class="fas fa-chevron-left"></i>
                            <?= $lang == 'ar' ? 'السابق' : 'Précédent' ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?specialization=<?=$specialization?>&category=<?=$category?>&lang=<?=$lang?>&page=<?=$i?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?specialization=<?=$specialization?>&category=<?=$category?>&lang=<?=$lang?>&page=<?=$page+1?>">
                            <?= $lang == 'ar' ? 'التالي' : 'Suivant' ?>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Animation des cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.book-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>
