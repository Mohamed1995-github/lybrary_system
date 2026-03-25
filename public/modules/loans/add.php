<?php
require_once '../../../config/db.php';
require_once '../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $borrower_id = (int)($_POST['borrower_id'] ?? 0);
        $item_id = (int)($_POST['item_id'] ?? 0);
        $loan_date = $_POST['loan_date'] ?? date('Y-m-d');
        $due_date = $_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days'));
        $notes = trim($_POST['notes'] ?? '');
        
        // Validation
        if ($borrower_id <= 0) {
            $error = $lang == 'ar' ? 'يجب اختيار المستعير' : 'Vous devez sélectionner un emprunteur';
        } elseif ($item_id <= 0) {
            $error = $lang == 'ar' ? 'يجب اختيار المصدر' : 'Vous devez sélectionner une ressource';
        } else {
            // Vérifier que l'item est disponible
            $check_item = $pdo->prepare("SELECT available_copies FROM items WHERE id = ?");
            $check_item->execute([$item_id]);
            $item = $check_item->fetch();
            
            if (!$item || $item['available_copies'] <= 0) {
                $error = $lang == 'ar' ? 'هذا المصدر غير متوفر حالياً' : 'Cette ressource n\'est pas disponible actuellement';
            } else {
                // Vérifier que le borrower n'a pas déjà emprunté cet item
                $check_loan = $pdo->prepare("SELECT id FROM loans WHERE borrower_id = ? AND item_id = ? AND status = 'active'");
                $check_loan->execute([$borrower_id, $item_id]);
                
                if ($check_loan->fetch()) {
                    $error = $lang == 'ar' ? 'هذا المستعير لديه إعارة نشطة لهذا المصدر' : 'Cet emprunteur a déjà un emprunt actif pour cette ressource';
                } else {
                    // Créer l'emprunt
                    $stmt = $pdo->prepare("
                        INSERT INTO loans (borrower_id, item_id, loan_date, due_date, status, notes, created_at) 
                        VALUES (?, ?, ?, ?, 'active', ?, NOW())
                    ");
                    $stmt->execute([$borrower_id, $item_id, $loan_date, $due_date, $notes]);
                    
                    // Mettre à jour le nombre d'exemplaires disponibles
                    $update_copies = $pdo->prepare("
                        UPDATE items 
                        SET available_copies = available_copies - 1 
                        WHERE id = ?
                    ");
                    $update_copies->execute([$item_id]);
                    
                    $success = $lang == 'ar' ? 'تم إضافة الإعارة بنجاح' : 'Emprunt ajouté avec succès';
                }
            }
        }
    } catch (PDOException $e) {
        $error = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
    }
}

// Récupérer les emprunteurs
$borrowers = [];
try {
    $stmt = $pdo->query("SELECT id, name, phone FROM borrowers ORDER BY name ASC");
    $borrowers = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignorer l'erreur
}

// Récupérer les items disponibles
$items = [];
try {
    $stmt = $pdo->query("
        SELECT id, title, type, author, available_copies, lang 
        FROM items 
        WHERE available_copies > 0 
        ORDER BY title ASC
    ");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignorer l'erreur
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'إضافة إعارة جديدة',
        'subtitle' => 'تسجيل إعارة جديدة للمستعيرين',
        'borrower' => 'المستعير',
        'item' => 'المصدر',
        'loan_date' => 'تاريخ الإعارة',
        'due_date' => 'تاريخ الاستحقاق',
        'notes' => 'ملاحظات',
        'add_loan' => 'إضافة الإعارة',
        'cancel' => 'إلغاء',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'select_borrower' => 'اختر المستعير',
        'select_item' => 'اختر المصدر',
        'available_copies' => 'النسخ المتاحة',
        'book' => 'كتاب',
        'magazine' => 'مجلة',
        'newspaper' => 'جريدة',
        'other' => 'مصدر آخر'
    ],
    'fr' => [
        'title' => 'Ajouter un emprunt',
        'subtitle' => 'Enregistrer un nouvel emprunt pour les emprunteurs',
        'borrower' => 'Emprunteur',
        'item' => 'Ressource',
        'loan_date' => 'Date d\'emprunt',
        'due_date' => 'Date d\'échéance',
        'notes' => 'Notes',
        'add_loan' => 'Ajouter l\'emprunt',
        'cancel' => 'Annuler',
        'back_to_dashboard' => 'Retour au tableau de bord',
        'select_borrower' => 'Choisir un emprunteur',
        'select_item' => 'Choisir une ressource',
        'available_copies' => 'Exemplaires disponibles',
        'book' => 'Livre',
        'magazine' => 'Magazine',
        'newspaper' => 'Journal',
        'other' => 'Autre ressource'
    ]
];

$t = $translations[$lang];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .container, .form-container {
            background-color: white !important;
            background: white !important;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .form-container {
            padding: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #5a67d8;
        }
        .btn-secondary {
            background: #6b7280;
            margin-right: 1rem;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #667eea;
            text-decoration: none;
            margin-bottom: 1rem;
        }
        .back-link:hover {
            color: #5a67d8;
        }
        .form-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .form-section h3 {
            margin: 0 0 1rem 0;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .item-info {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #6b7280;
        }
        .borrower-info {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #6b7280;
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
            <h1>
                <i class="fas fa-book-medical"></i>
                <?= $t['title'] ?>
            </h1>
        </div>
        
        <div class="form-container">
            <a href="dashboard.php?lang=<?=$lang?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <?= $t['back_to_dashboard'] ?>
            </a>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loanForm">
                <div class="form-section">
                    <h3>
                        <i class="fas fa-user"></i>
                        <?= $lang == 'ar' ? 'اختيار المستعير' : 'Sélectionner l\'emprunteur' ?>
                    </h3>
                    
                    <div class="form-group">
                        <label for="borrower_id">
                            <i class="fas fa-users"></i>
                            <?= $t['borrower'] ?> *
                        </label>
                        <select id="borrower_id" name="borrower_id" required onchange="showBorrowerInfo()">
                            <option value=""><?= $t['select_borrower'] ?></option>
                            <?php foreach ($borrowers as $borrower): ?>
                                <option value="<?= $borrower['id'] ?>" 
                                        data-name="<?= htmlspecialchars($borrower['name']) ?>"
                                        data-phone="<?= htmlspecialchars($borrower['phone']) ?>">
                                    <?= htmlspecialchars($borrower['name']) ?> - <?= htmlspecialchars($borrower['phone']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="borrowerInfo" class="borrower-info" style="display: none;">
                            <!-- معلومات المستعير ستظهر هنا -->
            </div>
            </div>
        </div>
                
                <div class="form-section">
                    <h3>
                        <i class="fas fa-book"></i>
                        <?= $lang == 'ar' ? 'اختيار المصدر' : 'Sélectionner la ressource' ?>
                    </h3>
                    
                    <div class="form-group">
                        <label for="item_id">
                            <i class="fas fa-list"></i>
                            <?= $t['item'] ?> *
                        </label>
                        <select id="item_id" name="item_id" required onchange="showItemInfo()">
                            <option value=""><?= $t['select_item'] ?></option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item['id'] ?>" 
                                        data-title="<?= htmlspecialchars($item['title']) ?>"
                                        data-type="<?= $item['type'] ?>"
                                        data-author="<?= htmlspecialchars($item['author'] ?? '') ?>"
                                        data-copies="<?= $item['available_copies'] ?>"
                                        data-lang="<?= $item['lang'] ?>">
                                    <?= htmlspecialchars($item['title']) ?> 
                                    (<?= $t[$item['type']] ?>) 
                                    - <?= $t['available_copies'] ?>: <?= $item['available_copies'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="itemInfo" class="item-info" style="display: none;">
                            <!-- معلومات المصدر ستظهر هنا -->
                        </div>
        </div>
    </div>
    
                <div class="form-section">
                    <h3>
                        <i class="fas fa-calendar"></i>
                        <?= $lang == 'ar' ? 'تفاصيل الإعارة' : 'Détails de l\'emprunt' ?>
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="loan_date">
                                <i class="fas fa-calendar-plus"></i>
                                <?= $t['loan_date'] ?> *
                            </label>
                            <input type="date" id="loan_date" name="loan_date" required
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="due_date">
                                <i class="fas fa-calendar-check"></i>
                                <?= $t['due_date'] ?> *
                            </label>
                            <input type="date" id="due_date" name="due_date" required
                                   value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
        </div>
    </div>
    
                    <div class="form-group">
                        <label for="notes">
                            <i class="fas fa-sticky-note"></i>
                            <?= $t['notes'] ?>
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="<?= $lang == 'ar' ? 'أدخل أي ملاحظات إضافية' : 'Entrez des notes supplémentaires' ?>"></textarea>
                </div>
            </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        <i class="fas fa-times"></i>
                        <?= $t['cancel'] ?>
                    </button>
                    <button type="submit" class="btn">
                    <i class="fas fa-plus"></i>
                        <?= $t['add_loan'] ?>
                    </button>
            </div>
            </form>
    </div>
</div>

<script>
        function showBorrowerInfo() {
            const select = document.getElementById('borrower_id');
            const info = document.getElementById('borrowerInfo');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                info.innerHTML = `
                    <strong><?= $lang == 'ar' ? 'المستعير:' : 'Emprunteur:' ?></strong> ${option.dataset.name}<br>
                    <strong><?= $lang == 'ar' ? 'الهاتف:' : 'Téléphone:' ?></strong> ${option.dataset.phone}
                `;
                info.style.display = 'block';
            } else {
                info.style.display = 'none';
            }
        }
        
        function showItemInfo() {
            const select = document.getElementById('item_id');
            const info = document.getElementById('itemInfo');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                let infoHtml = `
                    <strong><?= $lang == 'ar' ? 'العنوان:' : 'Titre:' ?></strong> ${option.dataset.title}<br>
                    <strong><?= $lang == 'ar' ? 'النوع:' : 'Type:' ?></strong> ${option.dataset.type}<br>
                    <strong><?= $t['available_copies'] ?>:</strong> ${option.dataset.copies}
                `;
                
                if (option.dataset.author) {
                    infoHtml += `<br><strong><?= $lang == 'ar' ? 'المؤلف:' : 'Auteur:' ?></strong> ${option.dataset.author}`;
                }
                
                info.innerHTML = infoHtml;
                info.style.display = 'block';
            } else {
                info.style.display = 'none';
            }
        }
</script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html> 