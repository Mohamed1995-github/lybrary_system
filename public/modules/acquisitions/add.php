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
        $resource_type = $_POST['resource_type'] ?? '';
        $acquisition_method = $_POST['acquisition_method'] ?? '';
        $provider_type = $_POST['provider_type'] ?? '';
        $provider_name = trim($_POST['provider_name'] ?? '');
        $provider_phone = trim($_POST['provider_phone'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        
        // Validation
        if (empty($resource_type)) {
            $error = $lang == 'ar' ? 'نوع المصدر مطلوب' : 'Le type de ressource est requis';
        } elseif (empty($acquisition_method)) {
            $error = $lang == 'ar' ? 'طريقة التزويد مطلوبة' : 'La méthode d\'acquisition est requise';
        } elseif (empty($provider_type)) {
            $error = $lang == 'ar' ? 'جهة التزويد مطلوبة' : 'Le type de fournisseur est requis';
        } elseif (empty($provider_name)) {
            $error = $lang == 'ar' ? 'اسم المزود مطلوب' : 'Le nom du fournisseur est requis';
        } elseif ($quantity <= 0) {
            $error = $lang == 'ar' ? 'الكمية يجب أن تكون أكبر من صفر' : 'La quantité doit être supérieure à zéro';
        } else {
            // Insérer le nouveau تزويد
            $stmt = $pdo->prepare("
                INSERT INTO acquisitions (resource_type, acquisition_method, provider_type, provider_name, provider_phone, quantity, lang, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $resource_type, 
                $acquisition_method, 
                $provider_type, 
                $provider_name, 
                $provider_phone, 
                $quantity, 
                $lang
            ]);
            
            $success = $lang == 'ar' ? 'تم إضافة التزويد بنجاح' : 'Acquisition ajoutée avec succès';
        }
    } catch (PDOException $e) {
        $error = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
    }
}

// Traductions
$translations = [
    'ar' => [
        'title' => 'إضافة تزويد جديد',
        'resource_type' => 'نوع المصدر',
        'acquisition_method' => 'طريقة التزويد',
        'provider_type' => 'جهة التزويد',
        'provider_name' => 'اسم المزود',
        'provider_phone' => 'رقم الهاتف',
        'quantity' => 'الكمية',
        'book' => 'كتاب',
        'magazine' => 'مجلة',
        'other' => 'مصدر آخر',
        'purchase' => 'شراء',
        'exchange' => 'تبادل',
        'donation' => 'إهداء',
        'institution' => 'مؤسسة',
        'publisher' => 'دار النشر',
        'person' => 'شخص',
        'add_acquisition' => 'إضافة التزويد',
        'cancel' => 'إلغاء',
        'back_to_dashboard' => 'العودة للوحة التحكم'
    ],
    'fr' => [
        'title' => 'Ajouter une acquisition',
        'resource_type' => 'Type de ressource',
        'acquisition_method' => 'Méthode d\'acquisition',
        'provider_type' => 'Type de fournisseur',
        'provider_name' => 'Nom du fournisseur',
        'provider_phone' => 'Numéro de téléphone',
        'quantity' => 'Quantité',
        'book' => 'Livre',
        'magazine' => 'Magazine',
        'other' => 'Autre source',
        'purchase' => 'Achat',
        'exchange' => 'Échange',
        'donation' => 'Don',
        'institution' => 'Institution',
        'publisher' => 'Maison d\'édition',
        'person' => 'Personne',
        'add_acquisition' => 'Ajouter l\'acquisition',
        'cancel' => 'Annuler',
        'back_to_dashboard' => 'Retour au tableau de bord'
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
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus {
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
        .section-title {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
            color: #374151;
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
                <i class="fas fa-plus-circle"></i>
                <?= $t['title'] ?>
            </h1>
        </div>
        
        <div class="form-container">
            <a href="../../dashboard.php?lang=<?=$lang?>" class="back-link">
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
                <div class="section-title">
                    <i class="fas fa-info-circle"></i>
                    <?= $lang == 'ar' ? 'معلومات التزويد' : 'Informations d\'acquisition' ?>
                </div>
                
                <div class="form-group">
                    <label for="resource_type">
                        <i class="fas fa-book"></i>
                        <?= $t['resource_type'] ?> *
                    </label>
                    <select id="resource_type" name="resource_type" required>
                        <option value=""><?= $lang == 'ar' ? 'اختر نوع المصدر' : 'Choisissez le type de ressource' ?></option>
                        <option value="book" <?= ($_POST['resource_type'] ?? '') == 'book' ? 'selected' : '' ?>>
                            <?= $t['book'] ?>
                        </option>
                        <option value="magazine" <?= ($_POST['resource_type'] ?? '') == 'magazine' ? 'selected' : '' ?>>
                            <?= $t['magazine'] ?>
                        </option>
                        <option value="other" <?= ($_POST['resource_type'] ?? '') == 'other' ? 'selected' : '' ?>>
                            <?= $t['other'] ?>
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="acquisition_method">
                        <i class="fas fa-shopping-cart"></i>
                        <?= $t['acquisition_method'] ?> *
                    </label>
                    <select id="acquisition_method" name="acquisition_method" required>
                        <option value=""><?= $lang == 'ar' ? 'اختر طريقة التزويد' : 'Choisissez la méthode d\'acquisition' ?></option>
                        <option value="purchase" <?= ($_POST['acquisition_method'] ?? '') == 'purchase' ? 'selected' : '' ?>>
                            <?= $t['purchase'] ?>
                        </option>
                        <option value="exchange" <?= ($_POST['acquisition_method'] ?? '') == 'exchange' ? 'selected' : '' ?>>
                            <?= $t['exchange'] ?>
                        </option>
                        <option value="donation" <?= ($_POST['acquisition_method'] ?? '') == 'donation' ? 'selected' : '' ?>>
                            <?= $t['donation'] ?>
                        </option>
                    </select>
                </div>
                
                <div class="section-title">
                    <i class="fas fa-building"></i>
                    <?= $lang == 'ar' ? 'معلومات المزود' : 'Informations du fournisseur' ?>
                </div>
                
                <div class="form-group">
                    <label for="provider_type">
                        <i class="fas fa-users"></i>
                        <?= $t['provider_type'] ?> *
                    </label>
                    <select id="provider_type" name="provider_type" required>
                        <option value=""><?= $lang == 'ar' ? 'اختر جهة التزويد' : 'Choisissez le type de fournisseur' ?></option>
                        <option value="institution" <?= ($_POST['provider_type'] ?? '') == 'institution' ? 'selected' : '' ?>>
                            <?= $t['institution'] ?>
                        </option>
                        <option value="publisher" <?= ($_POST['provider_type'] ?? '') == 'publisher' ? 'selected' : '' ?>>
                            <?= $t['publisher'] ?>
                        </option>
                        <option value="person" <?= ($_POST['provider_type'] ?? '') == 'person' ? 'selected' : '' ?>>
                            <?= $t['person'] ?>
                        </option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="provider_name">
                            <i class="fas fa-user"></i>
                            <?= $t['provider_name'] ?> *
                        </label>
                        <input type="text" id="provider_name" name="provider_name" required
                               placeholder="<?= $lang == 'ar' ? 'أدخل اسم المزود' : 'Entrez le nom du fournisseur' ?>"
                               value="<?= htmlspecialchars($_POST['provider_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="provider_phone">
                            <i class="fas fa-phone"></i>
                            <?= $t['provider_phone'] ?>
                        </label>
                        <input type="tel" id="provider_phone" name="provider_phone"
                               placeholder="<?= $lang == 'ar' ? 'أدخل رقم الهاتف' : 'Entrez le numéro de téléphone' ?>"
                               value="<?= htmlspecialchars($_POST['provider_phone'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="quantity">
                        <i class="fas fa-sort-numeric-up"></i>
                        <?= $t['quantity'] ?> *
                    </label>
                    <input type="number" id="quantity" name="quantity" required min="1"
                           placeholder="<?= $lang == 'ar' ? 'أدخل الكمية' : 'Entrez la quantité' ?>"
                           value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>">
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        <i class="fas fa-times"></i>
                        <?= $t['cancel'] ?>
                    </button>
                    <button type="submit" class="btn">
                        <i class="fas fa-plus"></i>
                        <?= $t['add_acquisition'] ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<script src="assets/js/professional-interactions.js"></script>
</body>
</html>


