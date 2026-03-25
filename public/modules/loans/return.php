<?php
require_once '../../../config/db.php';
require_once '../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';

// Traitement du formulaire de retour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $loan_id = (int)($_POST['loan_id'] ?? 0);
        $return_date = $_POST['return_date'] ?? date('Y-m-d');
        $condition = $_POST['condition'] ?? 'good';
        $notes = trim($_POST['notes'] ?? '');
        
        if ($loan_id <= 0) {
            $error = $lang == 'ar' ? 'معرف الإعارة غير صحيح' : 'ID d\'emprunt invalide';
        } else {
            // Vérifier que l'emprunt existe et est actif
            $check_stmt = $pdo->prepare("SELECT * FROM loans WHERE id = ? AND status = 'active'");
            $check_stmt->execute([$loan_id]);
            $loan = $check_stmt->fetch();
            
            if (!$loan) {
                $error = $lang == 'ar' ? 'الإعارة غير موجودة أو تم إرجاعها مسبقاً' : 'L\'emprunt n\'existe pas ou a déjà été retourné';
            } else {
                // Mettre à jour l'emprunt
                $update_stmt = $pdo->prepare("
                    UPDATE loans 
                    SET status = 'returned', return_date = ?, condition_returned = ?, notes = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $update_stmt->execute([$return_date, $condition, $notes, $loan_id]);
                
                // Mettre à jour le nombre d'exemplaires disponibles
                $update_copies_stmt = $pdo->prepare("
                    UPDATE items 
                    SET available_copies = available_copies + 1 
                    WHERE id = ?
                ");
                $update_copies_stmt->execute([$loan['item_id']]);
                
                $success = $lang == 'ar' ? 'تم إرجاع الإعارة بنجاح' : 'Emprunt retourné avec succès';
            }
        }
    } catch (PDOException $e) {
        $error = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
    }
}

// Récupérer les emprunts actifs pour la recherche
$active_loans = [];
try {
    $stmt = $pdo->prepare("
        SELECT l.id, l.borrower_id, l.item_id, l.loan_date, l.due_date, l.status,
               b.name as borrower_name, b.number as borrower_number,
               i.title as item_title, i.type as item_type
        FROM loans l
        JOIN borrowers b ON l.borrower_id = b.id
        JOIN items i ON l.item_id = i.id
        WHERE l.status = 'active'
        ORDER BY l.due_date ASC
    ");
    $stmt->execute();
    $active_loans = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignorer l'erreur pour l'instant
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'إرجاع إعارة',
        'subtitle' => 'تسجيل إرجاع المواد المستعارة',
        'loan_id' => 'معرف الإعارة',
        'return_date' => 'تاريخ الإرجاع',
        'condition' => 'حالة المادة',
        'notes' => 'ملاحظات',
        'good' => 'جيدة',
        'damaged' => 'تالفة',
        'lost' => 'مفقودة',
        'return_loan' => 'إرجاع الإعارة',
        'cancel' => 'إلغاء',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'search_loan' => 'البحث عن إعارة',
        'loan_details' => 'تفاصيل الإعارة',
        'borrower' => 'المستعير',
        'item' => 'المادة',
        'loan_date' => 'تاريخ الإعارة',
        'due_date' => 'تاريخ الاستحقاق',
        'days_overdue' => 'أيام التأخير'
    ],
    'fr' => [
        'title' => 'Retour d\'emprunt',
        'subtitle' => 'Enregistrer le retour des matériels empruntés',
        'loan_id' => 'ID d\'emprunt',
        'return_date' => 'Date de retour',
        'condition' => 'État du matériel',
        'notes' => 'Notes',
        'good' => 'Bon',
        'damaged' => 'Endommagé',
        'lost' => 'Perdu',
        'return_loan' => 'Retourner l\'emprunt',
        'cancel' => 'Annuler',
        'back_to_dashboard' => 'Retour au tableau de bord',
        'search_loan' => 'Rechercher un emprunt',
        'loan_details' => 'Détails de l\'emprunt',
        'borrower' => 'Emprunteur',
        'item' => 'Matériel',
        'loan_date' => 'Date d\'emprunt',
        'due_date' => 'Date d\'échéance',
        'days_overdue' => 'Jours de retard'
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
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        .loan-search {
            background: #f3f4f6;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .loan-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .loan-item:hover {
            background: #f8fafc;
            border-color: #667eea;
        }
        .loan-item.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .loan-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        .overdue {
            color: #dc2626;
            font-weight: bold;
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
                <i class="fas fa-undo"></i>
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
            
            <div class="loan-search">
                <h3>
                    <i class="fas fa-search"></i>
                    <?= $t['search_loan'] ?>
                </h3>
                <div id="loanList">
                    <?php foreach ($active_loans as $loan): ?>
                        <?php 
                        $is_overdue = strtotime($loan['due_date']) < time();
                        $days_overdue = $is_overdue ? floor((time() - strtotime($loan['due_date'])) / 86400) : 0;
                        ?>
                        <div class="loan-item" onclick="selectLoan(<?= $loan['id'] ?>, '<?= htmlspecialchars($loan['borrower_name']) ?>', '<?= htmlspecialchars($loan['item_title']) ?>')">
                            <div class="loan-details">
                                <div><strong><?= $t['borrower'] ?>:</strong> <?= htmlspecialchars($loan['borrower_name']) ?></div>
                                <div><strong><?= $t['item'] ?>:</strong> <?= htmlspecialchars($loan['item_title']) ?></div>
                                <div><strong><?= $t['loan_date'] ?>:</strong> <?= date('Y-m-d', strtotime($loan['loan_date'])) ?></div>
                                <div class="<?= $is_overdue ? 'overdue' : '' ?>">
                                    <strong><?= $t['due_date'] ?>:</strong> <?= date('Y-m-d', strtotime($loan['due_date'])) ?>
                                    <?php if ($is_overdue): ?>
                                        (<?= $days_overdue ?> <?= $t['days_overdue'] ?>)
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <form method="POST" id="returnForm">
                <input type="hidden" id="selected_loan_id" name="loan_id" value="">
                
                <div class="form-group">
                    <label for="return_date">
                        <i class="fas fa-calendar"></i>
                        <?= $t['return_date'] ?> *
                    </label>
                    <input type="date" id="return_date" name="return_date" required
                           value="<?= date('Y-m-d') ?>">
                </div>
                
                <div class="form-group">
                    <label for="condition">
                        <i class="fas fa-clipboard-check"></i>
                        <?= $t['condition'] ?> *
                    </label>
                    <select id="condition" name="condition" required>
                        <option value="good"><?= $t['good'] ?></option>
                        <option value="damaged"><?= $t['damaged'] ?></option>
                        <option value="lost"><?= $t['lost'] ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">
                        <i class="fas fa-sticky-note"></i>
                        <?= $t['notes'] ?>
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              placeholder="<?= $lang == 'ar' ? 'أدخل أي ملاحظات إضافية' : 'Entrez des notes supplémentaires' ?>"></textarea>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        <i class="fas fa-times"></i>
                        <?= $t['cancel'] ?>
                    </button>
                    <button type="submit" class="btn" id="submitBtn" disabled>
                        <i class="fas fa-undo"></i>
                        <?= $t['return_loan'] ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectLoan(loanId, borrowerName, itemTitle) {
            // Remove previous selection
            document.querySelectorAll('.loan-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Add selection to clicked item
            event.target.closest('.loan-item').classList.add('selected');
            
            // Set the loan ID
            document.getElementById('selected_loan_id').value = loanId;
            
            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
            
            // Show confirmation
            if (confirm('<?= $lang == 'ar' ? 'تأكيد إرجاع هذه الإعارة؟' : 'Confirmer le retour de cet emprunt?' ?>')) {
                // Form will be submitted
            }
        }
    </script>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>