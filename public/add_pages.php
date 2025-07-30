<?php
/**
 * Page d'accès aux fonctionnalités d'ajout
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'ar';
?>
<!DOCTYPE html>
<html lang="<?=$lang?>" dir="<?=($lang=='ar')?'rtl':'ltr'?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang == 'ar' ? 'صفحات الإضافة' : 'Pages d\'Ajout' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f8fafc;
            color: #1e293b;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .header h1 {
            font-size: 2.5rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .header p {
            color: #64748b;
            font-size: 1.1rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
        }
        .card h3 {
            color: #1e293b;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .card p {
            color: #64748b;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #64748b;
        }
        .btn-secondary:hover {
            background: #475569;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            text-decoration: none;
            margin-bottom: 2rem;
        }
        .back-link:hover {
            color: #3b82f6;
        }
        .user-info {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Informations utilisateur -->
        <div class="user-info">
            <i class="fas fa-user"></i>
            <?= $lang == 'ar' ? 'المستخدم: ' : 'Utilisateur: ' ?>
            <?= $_SESSION['username'] ?? $_SESSION['uid'] ?>
            | 
            <?= $lang == 'ar' ? 'الوصول: كامل' : 'Accès: Complet' ?>
        </div>

        <!-- Lien de retour -->
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <?= $lang == 'ar' ? 'العودة للوحة التحكم' : 'Retour au tableau de bord' ?>
        </a>

        <!-- En-tête -->
        <div class="header">
            <h1><?= $lang == 'ar' ? 'صفحات الإضافة' : 'Pages d\'Ajout' ?></h1>
            <p><?= $lang == 'ar' ? 'اختر الوظيفة التي تريد إضافة عنصر جديد إليها' : 'Choisissez la fonctionnalité où vous voulez ajouter un nouvel élément' ?></p>
        </div>

        <!-- Grille des fonctionnalités -->
        <div class="grid">
            <!-- Items - Livres -->
            <div class="card">
                <h3>
                    <i class="fas fa-book" style="color: #3b82f6;"></i>
                    <?= $lang == 'ar' ? 'إضافة كتاب' : 'Ajouter un livre' ?>
                </h3>
                <p><?= $lang == 'ar' ? 'إضافة كتاب جديد إلى المكتبة مع جميع المعلومات المطلوبة' : 'Ajouter un nouveau livre à la bibliothèque avec toutes les informations requises' ?></p>
                <a href="simple_router.php?module=items&action=add_book&lang=<?=$lang?>" class="btn">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة كتاب' : 'Ajouter un livre' ?>
                </a>
            </div>

            <!-- Items - Magazines -->
            <div class="card">
                <h3>
                    <i class="fas fa-newspaper" style="color: #10b981;"></i>
                    <?= $lang == 'ar' ? 'إضافة مجلة' : 'Ajouter une revue' ?>
                </h3>
                <p><?= $lang == 'ar' ? 'إضافة مجلة جديدة إلى المكتبة مع تفاصيل النشر والتكرار' : 'Ajouter une nouvelle revue à la bibliothèque avec les détails de publication et fréquence' ?></p>
                <a href="simple_router.php?module=items&action=add_magazine&lang=<?=$lang?>" class="btn">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة مجلة' : 'Ajouter une revue' ?>
                </a>
            </div>

            <!-- Items - Journaux -->
            <div class="card">
                <h3>
                    <i class="fas fa-newspaper" style="color: #f59e0b;"></i>
                    <?= $lang == 'ar' ? 'إضافة جريدة' : 'Ajouter un journal' ?>
                </h3>
                <p><?= $lang == 'ar' ? 'إضافة جريدة جديدة إلى المكتبة مع معلومات النشر والتوزيع' : 'Ajouter un nouveau journal à la bibliothèque avec les informations de publication et distribution' ?></p>
                <a href="simple_router.php?module=items&action=add_newspaper&lang=<?=$lang?>" class="btn">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة جريدة' : 'Ajouter un journal' ?>
                </a>
            </div>

            <!-- Acquisitions -->
            <div class="card">
                <h3>
                    <i class="fas fa-truck" style="color: #8b5cf6;"></i>
                    <?= $lang == 'ar' ? 'إضافة تزويد' : 'Ajouter une acquisition' ?>
                </h3>
                <p><?= $lang == 'ar' ? 'إضافة عملية تزويد جديدة للمكتبة مع تفاصيل المورد والتكلفة' : 'Ajouter une nouvelle acquisition pour la bibliothèque avec les détails du fournisseur et coût' ?></p>
                <a href="simple_router.php?module=acquisitions&action=add&lang=<?=$lang?>" class="btn">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة تزويد' : 'Ajouter une acquisition' ?>
                </a>
            </div>

            <!-- Emprunteurs -->
            <div class="card">
                <h3>
                    <i class="fas fa-users" style="color: #ef4444;"></i>
                    <?= $lang == 'ar' ? 'إضافة مستعير' : 'Ajouter un emprunteur' ?>
                </h3>
                <p><?= $lang == 'ar' ? 'إضافة مستعير جديد إلى النظام مع معلومات الاتصال والتفاصيل الشخصية' : 'Ajouter un nouvel emprunteur au système avec les informations de contact et détails personnels' ?></p>
                <a href="simple_router.php?module=borrowers&action=add&lang=<?=$lang?>" class="btn">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة مستعير' : 'Ajouter un emprunteur' ?>
                </a>
            </div>

            <!-- Prêts -->
            <div class="card">
                <h3>
                    <i class="fas fa-handshake" style="color: #06b6d4;"></i>
                    <?= $lang == 'ar' ? 'إعارة جديدة' : 'Nouvel emprunt' ?>
                </h3>
                <p><?= $lang == 'ar' ? 'إنشاء إعارة جديدة بين مستعير وعنصر من المكتبة مع تاريخ الإرجاع' : 'Créer un nouvel emprunt entre un emprunteur et un élément de la bibliothèque avec date de retour' ?></p>
                <a href="simple_router.php?module=loans&action=borrow&lang=<?=$lang?>" class="btn">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إعارة جديدة' : 'Nouvel emprunt' ?>
                </a>
            </div>

            <!-- Membres -->
            <div class="card">
                <h3>
                    <i class="fas fa-user-plus" style="color: #84cc16;"></i>
                    <?= $lang == 'ar' ? 'إضافة عضو' : 'Ajouter un membre' ?>
                </h3>
                <p><?= $lang == 'ar' ? 'إضافة عضو جديد إلى النظام مع معلومات العضوية والصلاحيات' : 'Ajouter un nouveau membre au système avec les informations d\'adhésion et permissions' ?></p>
                <a href="simple_router.php?module=members&action=add&lang=<?=$lang?>" class="btn">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة عضو' : 'Ajouter un membre' ?>
                </a>
            </div>

            <!-- Administration - Employés -->
            <div class="card">
                <h3>
                    <i class="fas fa-user-cog" style="color: #7c3aed;"></i>
                    <?= $lang == 'ar' ? 'إضافة موظف' : 'Ajouter un employé' ?>
                </h3>
                <p><?= $lang == 'ar' ? 'إضافة موظف جديد إلى النظام مع تحديد الوظيفة والصلاحيات' : 'Ajouter un nouvel employé au système avec définition du poste et permissions' ?></p>
                <a href="simple_router.php?module=administration&action=add&lang=<?=$lang?>" class="btn">
                    <i class="fas fa-plus"></i>
                    <?= $lang == 'ar' ? 'إضافة موظف' : 'Ajouter un employé' ?>
                </a>
            </div>
        </div>

        <!-- Liens rapides -->
        <div style="text-align: center; margin-top: 3rem;">
            <h3><?= $lang == 'ar' ? 'روابط سريعة' : 'Liens rapides' ?></h3>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="simple_router.php?module=items&action=list&type=book&lang=<?=$lang?>" class="btn btn-secondary">
                    <i class="fas fa-list"></i>
                    <?= $lang == 'ar' ? 'قائمة الكتب' : 'Liste des livres' ?>
                </a>
                <a href="simple_router.php?module=items&action=list&type=magazine&lang=<?=$lang?>" class="btn btn-secondary">
                    <i class="fas fa-list"></i>
                    <?= $lang == 'ar' ? 'قائمة المجلات' : 'Liste des revues' ?>
                </a>
                <a href="simple_router.php?module=acquisitions&action=list&lang=<?=$lang?>" class="btn btn-secondary">
                    <i class="fas fa-list"></i>
                    <?= $lang == 'ar' ? 'قائمة التزويد' : 'Liste des acquisitions' ?>
                </a>
                <a href="simple_router.php?module=borrowers&action=list&lang=<?=$lang?>" class="btn btn-secondary">
                    <i class="fas fa-list"></i>
                    <?= $lang == 'ar' ? 'قائمة المستعيرين' : 'Liste des emprunteurs' ?>
                </a>
            </div>
        </div>
    </div>
</body>
</html> 