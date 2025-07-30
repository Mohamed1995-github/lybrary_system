<?php
require_once '../../includes/auth.php';

$lang = $_SESSION['lang'] ?? 'ar';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    $borrowers_id = isset($_POST['borrower_id']) ? (int)$_POST['borrower_id'] : 0;
    $errors = [];

    // Validate input
    if (!$item_id) {
        $errors[] = $lang == 'ar' ? 'يجب اختيار المادة.' : 'Veuillez sélectionner un matériau.';
    }
    if (!$borrowers_id) {
        $errors[] = $lang == 'ar' ? 'يجب اختيار المستعير.' : 'Veuillez sélectionner un emprunteur.';
    }

    // Check item availability
    $item = $pdo->prepare("SELECT * FROM items WHERE id = ? AND copies_in > 0");
    $item->execute([$item_id]);
    $item = $item->fetch();
    if (!$item) {
        $errors[] = $lang == 'ar' ? 'المادة غير متوفرة أو غير موجودة.' : 'Le matériau n\'est pas disponible ou n\'existe pas.';
    }

    // Check borrower exists
    $borrower = $pdo->prepare("SELECT * FROM borrowers WHERE id = ?");
    $borrower->execute([$borrowers_id]);
    $borrower = $borrower->fetch();
    if (!$borrower) {
        $errors[] = $lang == 'ar' ? 'المستعير غير موجود.' : 'L\'emprunteur n\'existe pas.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            // Insert loan
            $stmt = $pdo->prepare("INSERT INTO loans (item_id, borrowers_id, borrow_date, due_date) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY))");
            $stmt->execute([$item_id, $borrowers_id]);
            // Decrease item copies
            $pdo->prepare("UPDATE items SET copies_in = copies_in - 1 WHERE id = ?")->execute([$item_id]);
            $pdo->commit();
            header('Location: list.php?success=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = $lang == 'ar' ? 'حدث خطأ أثناء إضافة الإعارة: ' . $e->getMessage() : 'Erreur lors de la création de l\'emprunt: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $lang == 'ar' ? 'إضافة إعارة' : 'Nouvel emprunt' ?> - Library System</title>
<link rel="stylesheet" href="../../public/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="page-container">
    <div class="page-header fade-in">
        <div class="page-title">
            <div class="page-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div>
                <h1><?= $lang == 'ar' ? 'إضافة إعارة' : 'Nouvel emprunt' ?></h1>
            </div>
        </div>
        <div class="page-actions">
            <a href="borrow.php" class="action-btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $lang == 'ar' ? 'العودة' : 'Retour' ?>
            </a>
        </div>
    </div>
    <div class="form-card fade-in">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <a href="borrow.php" class="action-btn btn-primary">
            <i class="fas fa-plus"></i>
            <?= $lang == 'ar' ? 'محاولة مرة أخرى' : 'Réessayer' ?>
        </a>
    </div>
</div>
</body>
</html> 