<?php
require_once '../../../../config/db.php';
require_once '../../../../includes/auth.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../../../public/login.php'); 
    exit; 
}

$lang = $_GET['lang'] ?? 'ar';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $id_number = trim($_POST['id_number'] ?? '');
        
        // Validation
        if (empty($name)) {
            $error = $lang == 'ar' ? 'الإسم مطلوب' : 'Le nom est requis';
        } elseif (empty($phone)) {
            $error = $lang == 'ar' ? 'رقم الهاتف مطلوب' : 'Le numéro de téléphone est requis';
        } elseif (empty($id_number)) {
            $error = $lang == 'ar' ? 'رقم بطاقة التعريف مطلوب' : 'Le numéro de carte d\'identité est requis';
        } else {
            // Vérifier si le numéro de carte d'identité existe déjà
            $check_stmt = $pdo->prepare("SELECT id FROM borrowers WHERE id_number = ?");
            $check_stmt->execute([$id_number]);
            
            if ($check_stmt->fetch()) {
                $error = $lang == 'ar' ? 'رقم بطاقة التعريف موجود بالفعل' : 'Le numéro de carte d\'identité existe déjà';
            } else {
                // Insérer le nouveau emprunteur
                $stmt = $pdo->prepare("
                    INSERT INTO borrowers (name, phone, id_number, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $phone, $id_number]);
                
                $success = $lang == 'ar' ? 'تم إضافة المستعير بنجاح' : 'Emprunteur ajouté avec succès';
            }
        }
    } catch (PDOException $e) {
        $error = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
    }
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'إضافة مستعير جديد',
        'name' => 'الإسم',
        'phone' => 'رقم الهاتف',
        'id_number' => 'رقم بطاقة التعريف',
        'add_borrower' => 'إضافة المستعير',
        'cancel' => 'إلغاء',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'name_placeholder' => 'أدخل اسم المستعير',
        'phone_placeholder' => 'أدخل رقم الهاتف',
        'id_placeholder' => 'أدخل رقم بطاقة التعريف'
    ],
    'fr' => [
        'title' => 'Ajouter un emprunteur',
        'name' => 'Nom',
        'phone' => 'Numéro de téléphone',
        'id_number' => 'Numéro de carte d\'identité',
        'add_borrower' => 'Ajouter l\'emprunteur',
        'cancel' => 'Annuler',
        'back_to_dashboard' => 'Retour au tableau de bord',
        'name_placeholder' => 'Entrez le nom de l\'emprunteur',
        'phone_placeholder' => 'Entrez le numéro de téléphone',
        'id_placeholder' => 'Entrez le numéro de carte d\'identité'
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
    <link rel="stylesheet" href="../../../assets/css/style.css">
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
            max-width: 600px;
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
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
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
                <i class="fas fa-user-plus"></i>
                <?= $t['title'] ?>
            </h1>
        </div>
        
        <div class="form-container">
            <a href="../dashboard.php?lang=<?=$lang?>" class="back-link">
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
            
            <form method="POST">
                <div class="form-section">
                    <h3>
                        <i class="fas fa-user"></i>
                        <?= $lang == 'ar' ? 'معلومات المستعير' : 'Informations de l\'emprunteur' ?>
                    </h3>
                    
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i>
                            <?= $t['name'] ?> *
                        </label>
                        <input type="text" id="name" name="name" required
                               placeholder="<?= $t['name_placeholder'] ?>"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone"></i>
                            <?= $t['phone'] ?> *
                        </label>
                        <input type="tel" id="phone" name="phone" required
                               placeholder="<?= $t['phone_placeholder'] ?>"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="id_number">
                            <i class="fas fa-id-card"></i>
                            <?= $t['id_number'] ?> *
                        </label>
                        <input type="text" id="id_number" name="id_number" required
                               placeholder="<?= $t['id_placeholder'] ?>"
                               value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>">
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        <i class="fas fa-times"></i>
                        <?= $t['cancel'] ?>
                    </button>
                    <button type="submit" class="btn">
                        <i class="fas fa-plus"></i>
                        <?= $t['add_borrower'] ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>


