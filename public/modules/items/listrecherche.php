<?php
session_start();
require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__, 3) . '/includes/auth.php';

if (!isset($_SESSION['uid'])) {
    header('Location: ../../login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'ar';
$msg  = $_GET['msg'] ?? '';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$translations = [
  'ar' => [
    'title' => 'بحوث الطلاب',
    'subtitle' => 'إدارة ومشاركة البحوث والمشاريع العلمية',
    'add' => '➕ إضافة بحث جديد',
    'student' => 'الطالب',
    'nni' => 'رقم البطاقة',
    'spec' => 'التخصص',
    'rtitle' => 'عنوان البحث',
    'summary' => 'الملخص',
    'notes' => 'ملاحظات',
    'pdf' => 'ملف PDF',
    'date' => 'التاريخ',
    'no' => 'لا توجد بحوث مسجلة حالياً',
    'success' => '✅ تمت الإضافة بنجاح',
    'view_pdf' => '📄 عرض',
    'edit' => '✏️ تعديل',
    'delete' => '🗑️ حذف',
    'admin' => '🏛️ إدارة',
    'law' => '⚖️ قانون',
    'economics' => '💰 اقتصاد',
    'diplomacy' => '🤝 دبلوماسية',
    'media' => '📡 إعلام'
  ],
  'fr' => [
    'title' => 'Recherches Étudiants',
    'subtitle' => 'Gestion et partage des recherches et projets scientifiques',
    'add' => '➕ Ajouter une Recherche',
    'student' => 'Étudiant',
    'nni' => 'Numéro de carte',
    'spec' => 'Spécialité',
    'rtitle' => 'Titre',
    'summary' => 'Résumé',
    'notes' => 'Notes',
    'pdf' => 'PDF',
    'date' => 'Date',
    'no' => 'Aucune recherche enregistrée',
    'success' => '✅ Ajout réussi',
    'view_pdf' => '📄 Voir',
    'edit' => '✏️ Modifier',
    'delete' => '🗑️ Supprimer',
    'admin' => '🏛️ Administration',
    'law' => '⚖️ Droit',
    'economics' => '💰 Économie',
    'diplomacy' => '🤝 Diplomatie',
    'media' => '📡 Médias'
  ]
];
$t = $translations[$lang];

$total_stmt = $pdo->query("SELECT COUNT(*) FROM student_research");
$total = (int)$total_stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT id, student_name, nni as student_id, title as research_title, specialization, summary as notes, pdf_file, created_at
  FROM student_research
  ORDER BY id DESC
  LIMIT {$limit} OFFSET {$offset}
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_pages = max(1, (int)ceil($total / $limit));
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $t['title'] ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
      font-family: 'Segoe UI', Tahoma, Arial, sans-serif; 
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 2rem;
    }
    .container { max-width: 1200px; margin: 0 auto; }
    .header {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .header h1 { color: #1f2937; font-size: 2rem; margin-bottom: 0.5rem; }
    .header p { color: #6b7280; margin-bottom: 1.5rem; }
    .header-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
    .btn {
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 600;
      transition: all 0.2s ease;
    }
    .btn-primary { background: #2563eb; color: white; }
    .btn-primary:hover { background: #1d4ed8; transform: translateY(-2px); }
    .btn-secondary { background: #6b7280; color: white; }
    .btn-secondary:hover { background: #4b5563; }
    .btn-sm { padding: 0.5rem 1rem; font-size: 0.875rem; }
    .btn-icon { padding: 0.5rem 0.75rem; }
    
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .stat-card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .stat-number { font-size: 2rem; font-weight: 700; color: #2563eb; }
    .stat-label { color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem; }
    
    .search-bar { margin-bottom: 2rem; }
    .search-bar input {
      width: 100%;
      max-width: 400px;
      padding: 0.75rem 1rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 1rem;
    }
    .search-bar input:focus { outline: none; border-color: #2563eb; }
    
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
    .card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.2s ease;
    }
    .card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
    
    .card-header { margin-bottom: 1rem; }
    .card-title { font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; }
    .card-badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .badge-admin { background: #dbeafe; color: #1e40af; }
    .badge-law { background: #fef3c7; color: #92400e; }
    .badge-economics { background: #d1fae5; color: #065f46; }
    .badge-diplomacy { background: #e0e7ff; color: #3730a3; }
    .badge-media { background: #fce7f3; color: #9f1239; }
    
    .card-meta {
      display: grid;
      gap: 0.75rem;
      margin: 1rem 0;
      padding: 1rem 0;
      border-top: 1px solid #e5e7eb;
      border-bottom: 1px solid #e5e7eb;
      font-size: 0.875rem;
    }
    .meta-item { display: flex; gap: 0.5rem; }
    .meta-icon { width: 20px; color: #6b7280; }
    .meta-label { font-weight: 600; color: #6b7280; }
    .meta-value { color: #1f2937; }
    
    .card-summary { color: #4b5563; font-size: 0.875rem; line-height: 1.5; margin: 1rem 0; }
    
    .card-actions {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
      flex-wrap: wrap;
    }
    
    .empty-state {
      background: white;
      border-radius: 12px;
      padding: 3rem;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .empty-icon { font-size: 3rem; margin-bottom: 1rem; }
    .empty-text { color: #6b7280; font-size: 1.125rem; margin-bottom: 1.5rem; }
    
    .pagination {
      display: flex;
      gap: 0.5rem;
      justify-content: center;
      margin-top: 2rem;
      flex-wrap: wrap;
    }
    .pagination a, .pagination span {
      padding: 0.5rem 0.75rem;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      text-decoration: none;
      color: #374151;
    }
    .pagination .current { background: #2563eb; color: white; border-color: #2563eb; }
    .pagination a:hover { background: #e5e7eb; }
    
    .alert { 
      background: #dcfce7; 
      color: #166534; 
      padding: 1rem; 
      border-radius: 8px; 
      margin-bottom: 1.5rem;
      border-left: 4px solid #22c55e;
    }
    
    @media (max-width: 768px) {
      .grid { grid-template-columns: 1fr; }
      .header h1 { font-size: 1.5rem; }
      .header-actions { flex-direction: column; }
      .btn { width: 100%; justify-content: center; }
    }
  </style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/professional-theme.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h1>🎓 <?= $t['title'] ?></h1>
    <p><?= $t['subtitle'] ?></p>
    <div class="header-actions">
      <a class="btn btn-primary" href="addrecherche.php?lang=<?= $lang ?>">
        <?= $t['add'] ?>
      </a>
      <a class="btn btn-secondary" href="../../dashboard.php?lang=<?= $lang ?>">
        🏠 <?= $lang == 'ar' ? 'العودة للرئيسية' : 'Retour au Tableau de Bord' ?>
      </a>
    </div>
  </div>

  <?php if ($msg === 'success'): ?>
    <div class="alert">✅ <?= $t['success'] ?></div>
  <?php endif; ?>

  <div class="stats">
    <div class="stat-card">
      <div class="stat-number"><?= $total ?></div>
      <div class="stat-label"><?= $lang == 'ar' ? 'إجمالي البحوث' : 'Total' ?></div>
    </div>
  </div>

  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="<?= $lang == 'ar' ? 'ابحث عن بحث...' : 'Rechercher...' ?>" onkeyup="filterCards()">
  </div>

  <?php if (empty($rows)): ?>
    <div class="empty-state">
      <div class="empty-icon">📚</div>
      <div class="empty-text"><?= $t['no'] ?></div>
      <a class="btn btn-primary" href="addrecherche.php?lang=<?= $lang ?>">
        ➕ <?= $lang == 'ar' ? 'إضافة أول بحث' : 'Ajouter une Recherche' ?>
      </a>
    </div>
  <?php else: ?>
    <div class="grid" id="researchGrid">
      <?php foreach($rows as $r): 
        $spec_badge = 'badge-' . $r['specialization'];
        $spec_label = $t[strtolower($r['specialization'])] ?? $r['specialization'];
      ?>
      <div class="card research-card" data-search="<?= strtolower($r['student_name'] . ' ' . $r['research_title']) ?>">
        <div class="card-header">
          <h3 class="card-title"><?= htmlspecialchars($r['research_title']) ?></h3>
          <span class="card-badge <?= $spec_badge ?>"><?= htmlspecialchars($spec_label) ?></span>
        </div>

        <div class="card-meta">
          <div class="meta-item">
            <span class="meta-icon">👤</span>
            <div>
              <span class="meta-label"><?= $t['student'] ?></span>
              <span class="meta-value"><?= htmlspecialchars($r['student_name']) ?></span>
            </div>
          </div>
          <div class="meta-item">
            <span class="meta-icon">🆔</span>
            <div>
              <span class="meta-label"><?= $t['nni'] ?></span>
              <span class="meta-value"><?= htmlspecialchars($r['student_id'] ?: '-') ?></span>
            </div>
          </div>
          <div class="meta-item">
            <span class="meta-icon">📅</span>
            <div>
              <span class="meta-label"><?= $t['date'] ?></span>
              <span class="meta-value"><?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
            </div>
          </div>
        </div>

        <?php if (!empty($r['notes'])): ?>
          <div class="card-summary">
            <strong><?= $t['summary'] ?>:</strong><br>
            <?= nl2br(htmlspecialchars(substr($r['notes'], 0, 150))) ?>
            <?php if (strlen($r['notes']) > 150): ?>..<?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="card-actions">
          <?php if (!empty($r['pdf_file'])): ?>
            <a href="../../uploads/research/<?= htmlspecialchars($r['pdf_file']) ?>" class="btn btn-sm btn-primary" target="_blank">
              <?= $t['view_pdf'] ?>
            </a>
          <?php endif; ?>
          <a href="edit.php?id=<?= $r['id'] ?>&lang=<?= $lang ?>" class="btn btn-sm btn-secondary">
            <?= $t['edit'] ?>
          </a>
          <a href="delete.php?id=<?= $r['id'] ?>&lang=<?= $lang ?>" class="btn btn-sm btn-secondary" onclick="return confirm('<?= $lang == 'ar' ? 'هل أنت متأكد؟' : 'Êtes-vous sûr ?' ?>')">
            <?= $t['delete'] ?>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <?php for($i=1;$i<=$total_pages;$i++): ?>
          <?php if($i==$page): ?>
            <span class="current"><?= $i ?></span>
          <?php else: ?>
            <a href="?lang=<?= $lang ?>&page=<?= $i ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<script>
function filterCards() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const cards = document.querySelectorAll('.research-card');
  cards.forEach(card => {
    const searchData = card.getAttribute('data-search');
    card.style.display = searchData.includes(searchTerm) ? 'block' : 'none';
  });
}
</script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>
