<?php
/* modules/administration/backup.php */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) { 
    header('Location: ../../public/login.php'); 
    exit; 
}

// الحصول على اللغة
$lang = $_GET['lang'] ?? 'ar';

// معالجة إنشاء النسخة الاحتياطية
$backup_message = '';
$backup_error = '';

if (isset($_POST['create_backup'])) {
    try {
        $backup_dir = __DIR__ . '/../../backups/';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backup_file = $backup_dir . 'library_backup_' . $timestamp . '.sql';
        
        // إنشاء نسخة احتياطية من قاعدة البيانات
        $command = "mysqldump -h localhost -u root --databases library_system > \"$backup_file\"";
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            $backup_message = $lang == 'ar' ? 'تم إنشاء النسخة الاحتياطية بنجاح' : 'Sauvegarde créée avec succès';
        } else {
            $backup_error = $lang == 'ar' ? 'خطأ في إنشاء النسخة الاحتياطية' : 'Erreur lors de la création de la sauvegarde';
        }
    } catch (Exception $e) {
        $backup_error = $lang == 'ar' ? 'خطأ في إنشاء النسخة الاحتياطية: ' . $e->getMessage() : 'Erreur lors de la création de la sauvegarde: ' . $e->getMessage();
    }
}

// الحصول على قائمة النسخ الاحتياطية الموجودة
$backup_files = [];
$backup_dir = __DIR__ . '/../../backups/';
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '*.sql');
    foreach ($files as $file) {
        $backup_files[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => filemtime($file)
        ];
    }
    // ترتيب الملفات حسب التاريخ (الأحدث أولاً)
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// الحصول على معلومات قاعدة البيانات
try {
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total_tables,
        SUM(table_rows) as total_rows,
        SUM(data_length + index_length) as total_size
        FROM information_schema.tables 
        WHERE table_schema = 'library_system'");
    $db_info = $stmt->fetch();
} catch (PDOException $e) {
    $db_info = ['total_tables' => 0, 'total_rows' => 0, 'total_size' => 0];
}

// الترجمات
$translations = [
    'ar' => [
        'title' => 'النسخ الاحتياطي',
        'backup_database' => 'نسخ قاعدة البيانات احتياطياً',
        'backup_files' => 'النسخ الاحتياطية الموجودة',
        'create_backup' => 'إنشاء نسخة احتياطية',
        'download_backup' => 'تحميل النسخة الاحتياطية',
        'delete_backup' => 'حذف النسخة الاحتياطية',
        'backup_info' => 'معلومات النسخة الاحتياطية',
        'database_info' => 'معلومات قاعدة البيانات',
        'total_tables' => 'إجمالي الجداول',
        'total_rows' => 'إجمالي الصفوف',
        'total_size' => 'إجمالي الحجم',
        'backup_date' => 'تاريخ النسخة الاحتياطية',
        'backup_size' => 'حجم النسخة الاحتياطية',
        'no_backups' => 'لا توجد نسخ احتياطية',
        'backup_created' => 'تم إنشاء النسخة الاحتياطية بنجاح',
        'backup_error' => 'خطأ في إنشاء النسخة الاحتياطية',
        'confirm_delete' => 'هل أنت متأكد من حذف هذه النسخة الاحتياطية؟',
        'back_to_dashboard' => 'العودة للوحة التحكم',
        'back_to_administration' => 'العودة للإدارة',
        'bytes' => 'بايت',
        'kb' => 'كيلوبايت',
        'mb' => 'ميجابايت',
        'gb' => 'جيجابايت',
        'format_size' => 'تنسيق الحجم',
        'last_modified' => 'آخر تعديل'
    ],
    'fr' => [
        'title' => 'Sauvegarde',
        'backup_database' => 'Sauvegarder la base de données',
        'backup_files' => 'Sauvegardes existantes',
        'create_backup' => 'Créer une sauvegarde',
        'download_backup' => 'Télécharger la sauvegarde',
        'delete_backup' => 'Supprimer la sauvegarde',
        'backup_info' => 'Informations de sauvegarde',
        'database_info' => 'Informations de la base de données',
        'total_tables' => 'Total des tables',
        'total_rows' => 'Total des lignes',
        'total_size' => 'Taille totale',
        'backup_date' => 'Date de sauvegarde',
        'backup_size' => 'Taille de sauvegarde',
        'no_backups' => 'Aucune sauvegarde trouvée',
        'backup_created' => 'Sauvegarde créée avec succès',
        'backup_error' => 'Erreur lors de la création de la sauvegarde',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cette sauvegarde ?',
        'back_to_dashboard' => 'Retour au Tableau de Bord',
        'back_to_administration' => 'Retour à l\'Administration',
        'bytes' => 'octets',
        'kb' => 'Ko',
        'mb' => 'Mo',
        'gb' => 'Go',
        'format_size' => 'Formater la taille',
        'last_modified' => 'Dernière modification'
    ]
];

$t = $translations[$lang];

// دالة لتنسيق حجم الملف
function formatFileSize($bytes, $lang) {
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' ' . ($lang == 'ar' ? 'جيجابايت' : 'Go');
    } elseif ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' ' . ($lang == 'ar' ? 'ميجابايت' : 'Mo');
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' ' . ($lang == 'ar' ? 'كيلوبايت' : 'Ko');
    } else {
        return $bytes . ' ' . ($lang == 'ar' ? 'بايت' : 'octets');
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?> - Library System</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .backup-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .backup-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .backup-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        
        .backup-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .backup-form {
            margin-bottom: 1.5rem;
        }
        
        .backup-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .backup-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        
        .backup-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .backup-item {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .backup-item-info {
            flex: 1;
        }
        
        .backup-item-name {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .backup-item-details {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .backup-item-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .action-btn.download {
            background: var(--success-color);
            color: white;
        }
        
        .action-btn.delete {
            background: var(--error-color);
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            background: var(--bg-secondary);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        
        .info-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert.success {
            background: var(--success-bg);
            color: var(--success-color);
            border: 1px solid var(--success-border);
        }
        
        .alert.error {
            background: var(--error-bg);
            color: var(--error-color);
            border: 1px solid var(--error-border);
        }
        
        .navigation-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .nav-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        
        .nav-btn.secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .nav-btn.secondary:hover {
            background: var(--bg-primary);
        }
        
        @media (max-width: 768px) {
            .backup-grid {
                grid-template-columns: 1fr;
            }
            
            .backup-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .backup-item-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="backup-container">
        <!-- Navigation -->
        <div class="navigation-buttons">
            <a href="../../public/dashboard.php?lang=<?=$lang?>" class="nav-btn">
                <i class="fas fa-home"></i>
                <?= $t['back_to_dashboard'] ?>
            </a>
            <a href="../../public/router.php?module=administration&action=list&lang=<?=$lang?>" class="nav-btn secondary">
                <i class="fas fa-users"></i>
                <?= $t['back_to_administration'] ?>
            </a>
        </div>
        
        <h1><?= $t['title'] ?></h1>
        
        <!-- Messages -->
        <?php if ($backup_message): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= $backup_message ?>
            </div>
        <?php endif; ?>
        
        <?php if ($backup_error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= $backup_error ?>
            </div>
        <?php endif; ?>
        
        <div class="backup-grid">
            <!-- Database Information -->
            <div class="backup-card">
                <div class="backup-header">
                    <i class="fas fa-database"></i>
                    <span><?= $t['database_info'] ?></span>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-number"><?= $db_info['total_tables'] ?></div>
                        <div class="info-label"><?= $t['total_tables'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-number"><?= number_format($db_info['total_rows']) ?></div>
                        <div class="info-label"><?= $t['total_rows'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-number"><?= formatFileSize($db_info['total_size'], $lang) ?></div>
                        <div class="info-label"><?= $t['total_size'] ?></div>
                    </div>
                </div>
                
                <form method="POST" class="backup-form">
                    <button type="submit" name="create_backup" class="backup-btn">
                        <i class="fas fa-download"></i>
                        <?= $t['create_backup'] ?>
                    </button>
                </form>
            </div>
            
            <!-- Existing Backups -->
            <div class="backup-card">
                <div class="backup-header">
                    <i class="fas fa-folder-open"></i>
                    <span><?= $t['backup_files'] ?></span>
                </div>
                
                <?php if (empty($backup_files)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        <i class="fas fa-info-circle"></i>
                        <p><?= $t['no_backups'] ?></p>
                    </div>
                <?php else: ?>
                    <ul class="backup-list">
                        <?php foreach ($backup_files as $file): ?>
                            <li class="backup-item">
                                <div class="backup-item-info">
                                    <div class="backup-item-name"><?= htmlspecialchars($file['name']) ?></div>
                                    <div class="backup-item-details">
                                        <?= $t['backup_size'] ?>: <?= formatFileSize($file['size'], $lang) ?> | 
                                        <?= $t['last_modified'] ?>: <?= date('Y-m-d H:i:s', $file['date']) ?>
                                    </div>
                                </div>
                                <div class="backup-item-actions">
                                    <a href="../../backups/<?= urlencode($file['name']) ?>" 
                                       class="action-btn download" 
                                       download>
                                        <i class="fas fa-download"></i>
                                        <?= $t['download_backup'] ?>
                                    </a>
                                    <button onclick="if(confirm('<?= $t['confirm_delete'] ?>')) { 
                                        window.location.href='?module=administration&action=backup&lang=<?=$lang?>&delete=<?= urlencode($file['name']) ?>'
                                    }" class="action-btn delete">
                                        <i class="fas fa-trash"></i>
                                        <?= $t['delete_backup'] ?>
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
