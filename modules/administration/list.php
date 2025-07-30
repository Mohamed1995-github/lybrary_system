<?php
/* modules/administration/list.php */
require_once '../../config/db.php';
require_once '../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';

// الحصول على قائمة الموظفين
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE lang = ? ORDER BY name ASC");
    $stmt->execute([$lang]);
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    $employees = [];
}

// معالجة حذف موظف
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$delete_id]);
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
    <title><?= $lang == 'ar' ? 'قائمة الموظفين' : 'Liste des employés' ?> - Library System</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .employee-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
        }
        
        .employee-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .employee-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        .employee-number {
            background: var(--primary-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .employee-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }
        
        .detail-value {
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .employee-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .btn-edit {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 16px;
            color: #d1d5db;
        }
        
        .empty-state h3 {
            margin: 0 0 8px 0;
            color: var(--text-primary);
        }
        
        .empty-state p {
            margin: 0 0 24px 0;
        }
        
        .access-rights {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .access-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="header-left">
                    <a href="../../public/dashboard.php?lang=<?=$lang?>" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
                    </a>
                </div>
                <div>
                    <h1><?= $lang == 'ar' ? 'قائمة الموظفين' : 'Liste des employés' ?></h1>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">
                        <?= $lang == 'ar' ? 'إدارة جميع موظفي المكتبة' : 'Gestion de tous les employés de la bibliothèque' ?>
                    </p>
                </div>
            </div>
        </header>

        <!-- Actions -->
        <div class="actions-bar">
            <a href="../../public/router.php?module=administration&action=add&lang=<?=$lang?>" class="action-btn btn-primary">
                <i class="fas fa-plus"></i>
                <?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un employé' ?>
            </a>
            <a href="permissions_guide.php?lang=<?=$lang?>" class="action-btn btn-secondary">
                <i class="fas fa-shield-alt"></i>
                <?= $lang == 'ar' ? 'دليل الصلاحيات' : 'Guide des permissions' ?>
            </a>
            <a href="../../public/dashboard.php?lang=<?=$lang?>" class="action-btn btn-secondary">
                <i class="fas fa-home"></i>
                <?= $lang == 'ar' ? 'الرئيسية' : 'Accueil' ?>
            </a>
        </div>

        <!-- Content -->
        <main class="main-content">
            <?php
            // عرض رسائل النجاح والخطأ
            if (isset($_GET['success'])) {
                if ($_GET['success'] == 'added') {
                    echo '<div class="alert alert-success">';
                    echo '<i class="fas fa-check-circle"></i>';
                    echo '<div>';
                    echo '<h4>' . ($lang == 'ar' ? 'تم إضافة الموظف بنجاح!' : 'Employé ajouté avec succès!') . '</h4>';
                    echo '<p>' . ($lang == 'ar' ? 'كلمة المرور الافتراضية: <strong>123456</strong>' : 'Mot de passe par défaut: <strong>123456</strong>') . '</p>';
                    echo '</div>';
                    echo '</div>';
                } elseif ($_GET['success'] == 'deleted') {
                    echo '<div class="alert alert-success">';
                    echo '<i class="fas fa-check-circle"></i>';
                    echo '<div>';
                    echo '<h4>' . ($lang == 'ar' ? 'تم حذف الموظف بنجاح!' : 'Employé supprimé avec succès!') . '</h4>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            
            if (isset($_GET['error'])) {
                if ($_GET['error'] == 'delete_failed') {
                    echo '<div class="alert alert-error">';
                    echo '<i class="fas fa-exclamation-triangle"></i>';
                    echo '<div>';
                    echo '<h4>' . ($lang == 'ar' ? 'خطأ في حذف الموظف' : 'Erreur lors de la suppression') . '</h4>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
            <?php if (empty($employees)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3><?= $lang == 'ar' ? 'لا يوجد موظفون' : 'Aucun employé' ?></h3>
                    <p><?= $lang == 'ar' ? 'لم يتم إضافة أي موظف بعد' : 'Aucun employé n\'a été ajouté pour le moment' ?></p>
                    <a href="../../public/router.php?module=administration&action=add&lang=<?=$lang?>" class="action-btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <?= $lang == 'ar' ? 'إضافة موظف جديد' : 'Ajouter un employé' ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="employees-grid">
                    <?php foreach ($employees as $employee): ?>
                        <div class="employee-card">
                            <div class="employee-header">
                                <h3 class="employee-name"><?= htmlspecialchars($employee['name']) ?></h3>
                                <span class="employee-number"><?= htmlspecialchars($employee['number']) ?></span>
                            </div>
                            
                            <div class="employee-details">
                                <div class="detail-item">
                                    <span class="detail-label"><?= $lang == 'ar' ? 'الوظيفة' : 'Fonction' ?></span>
                                    <span class="detail-value"><?= htmlspecialchars($employee['function']) ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label"><?= $lang == 'ar' ? 'حقوق الوصول' : 'Droits d\'accès' ?></span>
                                    <div class="access-rights">
                                        <?php 
                                        $rights = explode(',', $employee['access_rights']);
                                        foreach ($rights as $right): 
                                            $right = trim($right);
                                            if (!empty($right)):
                                        ?>
                                            <span class="access-badge"><?= htmlspecialchars($right) ?></span>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="employee-actions">
                                <a href="../../public/router.php?module=administration&action=edit&id=<?= $employee["id'] ?>&lang=<?=$lang?>" class="btn-edit">
                                    <i class="fas fa-edit"></i>
                                    <?= $lang == 'ar' ? 'تعديل' : 'Modifier' ?>
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('<?= $lang == 'ar' ? 'هل أنت متأكد من حذف هذا الموظف؟' : 'Êtes-vous sûr de vouloir supprimer cet employé ?' ?>')">
                                    <input type="hidden" name="delete_id" value="<?= $employee['id'] ?>">
                                    <button type="submit" class="btn-delete">
                                        <i class="fas fa-trash"></i>
                                        <?= $lang == 'ar' ? 'حذف' : 'Supprimer' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../../public/assets/js/script.js"></script>
</body>
</html> 