<?php
/**
 * Module d'acquisitions sécurisé
 * Version sécurisée avec vérifications de permissions et protection CSRF
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/module_security.php';

// Vérifier l'authentification
if (!isset($_SESSION['uid'])) {
    header('Location: ../../public/login.php');
    exit;
}

// Initialiser la sécurité
$security = new ModuleSecurity($pdo, $_SESSION['uid'], $_SESSION['role'] ?? 'guest');

// Vérifier les permissions d'accès
if (!$security->checkModuleAccess('acquisitions', 'add')) {
    $security->logSecurityEvent('UNAUTHORIZED_ACCESS', 'acquisitions/add');
    header('Location: ../../public/error.php?code=403');
    exit;
}

$lang = $_GET['lang'] ?? 'ar';

// Options pour le type de source
$source_types = [
    'book' => ['ar' => 'كتب', 'fr' => 'Livres'],
    'magazine' => ['ar' => 'مجلات', 'fr' => 'Revues / Magazines'],
    'other' => ['ar' => 'مصادر أخرى', 'fr' => 'Autre source']
];

// Options pour la méthode d'approvisionnement
$acquisition_methods = [
    'purchase' => ['ar' => 'شراء', 'fr' => 'Achat'],
    'exchange' => ['ar' => 'تبادل', 'fr' => 'Échange'],
    'donation' => ['ar' => 'إهداء', 'fr' => 'Don']
];

// Options pour le type de fournisseur
$supplier_types = [
    'institution' => ['ar' => 'مؤسسة', 'fr' => 'Institution'],
    'publisher' => ['ar' => 'دار النشر', 'fr' => 'Maison d\'édition'],
    'person' => ['ar' => 'شخص', 'fr' => 'Personne']
];

$acquisition = [
    'source_type' => 'book',
    'acquisition_method' => 'purchase',
    'supplier_type' => 'publisher',
    'supplier_name' => '',
    'supplier_phone' => '',
    'supplier_address' => '',
    'cost' => '',
    'quantity' => 1,
    'acquired_date' => date('Y-m-d'),
    'notes' => ''
];

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!$security->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $security->logSecurityEvent('CSRF_ATTEMPT', 'acquisitions/add');
        $errors[] = $lang == 'ar' ? 'خطأ في التحقق من الأمان' : 'Erreur de vérification de sécurité';
    } else {
        // Nettoyer et valider les données
        $source_type = $security->sanitizeInput($_POST['source_type'] ?? 'book');
        $acquisition_method = $security->sanitizeInput($_POST['acquisition_method'] ?? 'purchase');
        $supplier_type = $security->sanitizeInput($_POST['supplier_type'] ?? 'publisher');
        $supplier_name = $security->sanitizeInput($_POST['supplier_name'] ?? '');
        $supplier_phone = $security->sanitizeInput($_POST['supplier_phone'] ?? '');
        $supplier_address = $security->sanitizeInput($_POST['supplier_address'] ?? '');
        $cost = (float)($_POST['cost'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $acquired_date = $security->sanitizeInput($_POST['acquired_date'] ?? date('Y-m-d'));
        $notes = $security->sanitizeInput($_POST['notes'] ?? '');
        
        // Validation
        if (empty($supplier_name)) {
            $errors[] = $lang == 'ar' ? 'اسم المزود مطلوب' : 'Le nom du fournisseur est requis';
        }
        
        if ($quantity < 1) {
            $errors[] = $lang == 'ar' ? 'الكمية يجب أن تكون 1 على الأقل' : 'La quantité doit être au moins 1';
        }
        
        if ($cost < 0) {
            $errors[] = $lang == 'ar' ? 'التكلفة لا يمكن أن تكون سالبة' : 'Le coût ne peut pas être négatif';
        }
        
        // Valider le numéro de téléphone si fourni
        if (!empty($supplier_phone) && !$security->validatePhone($supplier_phone)) {
            $errors[] = $lang == 'ar' ? 'رقم الهاتف غير صحيح' : 'Numéro de téléphone invalide';
        }
        
        // Si pas d'erreurs, insérer l'acquisition
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO acquisitions (lang, source_type, acquisition_method, supplier_type, supplier_name, supplier_phone, supplier_address, cost, quantity, acquired_date, notes, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([$lang, $source_type, $acquisition_method, $supplier_type, $supplier_name, $supplier_phone, $supplier_address, $cost, $quantity, $acquired_date, $notes, $_SESSION['uid']]);
                
                $success = $lang == 'ar' ? 'تم إضافة الاستحواذ بنجاح!' : 'Acquisition ajoutée avec succès!';
                
                // Logger l'action
                $security->logSecurityEvent('ACQUISITION_ADDED', "ID: " . $pdo->lastInsertId());
                
                // Reset form after successful submission
                $acquisition = [
                    'source_type' => 'book',
                    'acquisition_method' => 'purchase',
                    'supplier_type' => 'publisher',
                    'supplier_name' => '',
                    'supplier_phone' => '',
                    'supplier_address' => '',
                    'cost' => '',
                    'quantity' => 1,
                    'acquired_date' => date('Y-m-d'),
                    'notes' => ''
                ];
            } catch (PDOException $e) {
                $errors[] = $lang == 'ar' ? 'خطأ في قاعدة البيانات' : 'Erreur de base de données';
                $security->logSecurityEvent('DATABASE_ERROR', $e->getMessage());
            }
        }
    }
}

// Générer un nouveau token CSRF
$csrf_token = $security->generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$lang=='ar'?'إضافة استحواذ جديد':'Ajouter une nouvelle acquisition'?> - Système de Bibliothèque</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?=$lang=='ar'?'إضافة استحواذ جديد':'Ajouter une nouvelle acquisition'?></h1>
            <nav>
                <a href=" http://localhost/library_system/public/dashboard.php?lang=<?=$lang?>"><?=$lang=='ar'?'الرئيسية':'Accueil'?></a>
                <a href="../../public/router.php?module=acquisitions&action=list&lang=<?=$lang?>"><?=$lang=='ar'?'قائمة الاستحواذات':'Liste des acquisitions'?></a>
            </nav>
        </header>

        <main>
            <?php if (!empty($success)): ?>
                <div class="alert success">
                    <?=$success?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?=$error?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="form">
                <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                
                <div class="form-group">
                    <label for="source_type"><?=$lang=='ar'?'نوع المصدر':'Type de source'?></label>
                    <select name="source_type" id="source_type" required>
                        <?php foreach ($source_types as $key => $value): ?>
                            <option value="<?=$key?>" <?=$acquisition['source_type']==$key?'selected':''?>>
                                <?=$value[$lang]?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="acquisition_method"><?=$lang=='ar'?'طريقة الاستحواذ':'Méthode d\'acquisition'?></label>
                    <select name="acquisition_method" id="acquisition_method" required>
                        <?php foreach ($acquisition_methods as $key => $value): ?>
                            <option value="<?=$key?>" <?=$acquisition['acquisition_method']==$key?'selected':''?>>
                                <?=$value[$lang]?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="supplier_type"><?=$lang=='ar'?'نوع المزود':'Type de fournisseur'?></label>
                    <select name="supplier_type" id="supplier_type" required>
                        <?php foreach ($supplier_types as $key => $value): ?>
                            <option value="<?=$key?>" <?=$acquisition['supplier_type']==$key?'selected':''?>>
                                <?=$value[$lang]?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="supplier_name"><?=$lang=='ar'?'اسم المزود':'Nom du fournisseur'?> *</label>
                    <input type="text" name="supplier_name" id="supplier_name" value="<?=$acquisition['supplier_name']?>" required>
                </div>

                <div class="form-group">
                    <label for="supplier_phone"><?=$lang=='ar'?'هاتف المزود':'Téléphone du fournisseur'?></label>
                    <input type="tel" name="supplier_phone" id="supplier_phone" value="<?=$acquisition['supplier_phone']?>">
                </div>

                <div class="form-group">
                    <label for="supplier_address"><?=$lang=='ar'?'عنوان المزود':'Adresse du fournisseur'?></label>
                    <textarea name="supplier_address" id="supplier_address" rows="3"><?=$acquisition['supplier_address']?></textarea>
                </div>

                <div class="form-group">
                    <label for="cost"><?=$lang=='ar'?'التكلفة':'Coût'?></label>
                    <input type="number" name="cost" id="cost" value="<?=$acquisition['cost']?>" min="0" step="0.01">
                </div>

                <div class="form-group">
                    <label for="quantity"><?=$lang=='ar'?'الكمية':'Quantité'?> *</label>
                    <input type="number" name="quantity" id="quantity" value="<?=$acquisition['quantity']?>" min="1" required>
                </div>

                <div class="form-group">
                    <label for="acquired_date"><?=$lang=='ar'?'تاريخ الاستحواذ':'Date d\'acquisition'?></label>
                    <input type="date" name="acquired_date" id="acquired_date" value="<?=$acquisition['acquired_date']?>" required>
                </div>

                <div class="form-group">
                    <label for="notes"><?=$lang=='ar'?'ملاحظات':'Notes'?></label>
                    <textarea name="notes" id="notes" rows="4"><?=$acquisition['notes']?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?=$lang=='ar'?'إضافة الاستحواذ':'Ajouter l\'acquisition'?>
                    </button>
                    <a href="../../public/router.php?module=acquisitions&action=list&lang=<?=$lang?>" class="btn btn-secondary">
                        <?=$lang=='ar'?'إلغاء':'Annuler'?>
                    </a>
                </div>
            </form>
        </main>
    </div>

    <script src="../../public/assets/js/script.js"></script>
</body>
</html> 