<?php
/* modules/administration/delete.php */
require_once '../../config/db.php';
require_once '../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: list.php?lang=$lang&error=no_id");
    exit;
}

// الحصول على بيانات الموظف
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ? AND lang = ?");
    $stmt->execute([$id, $lang]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        header("Location: list.php?lang=$lang&error=not_found");
        exit;
    }
} catch (PDOException $e) {
    header("Location: list.php?lang=$lang&error=db_error");
    exit;
}

// معالجة تأكيد الحذف
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: list.php?lang=$lang&success=deleted");
        exit;
    } catch (PDOException $e) {
        header("Location: list.php?lang=$lang&error=delete_failed");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'حذف الموظف' : 'Supprimer l\'employé' ?> - Library System</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .delete-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .delete-card {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        
        .delete-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 24px;
        }
        
        .delete-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 16px 0;
        }
        
        .delete-message {
            color: var(--text-secondary);
            margin: 0 0 32px 0;
            line-height: 1.6;
        }
        
        .employee-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
            text-align: left;
        }
        
        .employee-info h4 {
            margin: 0 0 12px 0;
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-label {
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .info-value {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .delete-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 32px;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="header-left">
                    <a href="list.php?lang=<?=$lang?>" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        <?= $lang == 'ar' ? 'العودة لقائمة الموظفين' : 'Retour à la liste des employés' ?>
                    </a>
                </div>
                <div>
                    <h1><?= $lang == 'ar' ? 'حذف الموظف' : 'Supprimer l\'employé' ?></h1>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                        <?= $lang == 'ar' ? 'تأكيد حذف الموظف' : 'Confirmer la suppression de l\'employé' ?>
                    </p>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="main-content">
            <div class="delete-container">
                <div class="delete-card">
                    <div class="delete-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    
                    <h2 class="delete-title">
                        <?= $lang == 'ar' ? 'تأكيد الحذف' : 'Confirmer la suppression' ?>
                    </h2>
                    
                    <p class="delete-message">
                        <?= $lang == 'ar' ? 'هل أنت متأكد من أنك تريد حذف هذا الموظف؟ هذا الإجراء لا يمكن التراجع عنه.' : 'Êtes-vous sûr de vouloir supprimer cet employé ? Cette action ne peut pas être annulée.' ?>
                    </p>
                    
                    <div class="employee-info">
                        <h4><?= $lang == 'ar' ? 'معلومات الموظف' : 'Informations de l\'employé' ?></h4>
                        
                        <div class="info-item">
                            <span class="info-label"><?= $lang == 'ar' ? 'الاسم:' : 'Nom:' ?></span>
                            <span class="info-value"><?= htmlspecialchars($employee['name']) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><?= $lang == 'ar' ? 'الرقم:' : 'Numéro:' ?></span>
                            <span class="info-value"><?= htmlspecialchars($employee['number']) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><?= $lang == 'ar' ? 'الوظيفة:' : 'Fonction:' ?></span>
                            <span class="info-value"><?= htmlspecialchars($employee['function']) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><?= $lang == 'ar' ? 'حقوق الوصول:' : 'Droits d\'accès:' ?></span>
                            <span class="info-value"><?= htmlspecialchars($employee['access_rights']) ?></span>
                        </div>
                    </div>
                    
                    <div class="delete-actions">
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="confirm_delete" class="btn-danger">
                                <i class="fas fa-trash"></i>
                                <?= $lang == 'ar' ? 'تأكيد الحذف' : 'Confirmer la suppression' ?>
                            </button>
                        </form>
                        
                        <a href="list.php?lang=<?=$lang?>" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            <?= $lang == 'ar' ? 'إلغاء' : 'Annuler' ?>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../public/assets/js/script.js"></script>
    <script>
        // إعادة التوجيه التلقائي بعد 10 ثوانٍ
        setTimeout(function() {
            window.location.href = 'list.php?lang=<?=$lang?>';
        }, 10000);
    </script>
</body>
</html> 