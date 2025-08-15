<?php
/* public/improved_router.php - نظام مسارات محسن */
require_once __DIR__ . '/../includes/auth.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

// الحصول على المعاملات
$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? '';
$lang = $_GET['lang'] ?? 'ar';
$type = $_GET['type'] ?? '';

// تعريف الوحدات المسموحة
$allowed_modules = [
    'items' => [
        'actions' => ['add_book', 'add_magazine', 'add_newspaper', 'add_edit', 'edit', 'delete', 'list', 'list_grouped'],
        'description' => ['ar' => 'إدارة المواد', 'fr' => 'Gestion des matériaux'],
        'icon' => 'fas fa-book'
    ],
    'acquisitions' => [
        'actions' => ['add', 'edit', 'delete', 'list'],
        'description' => ['ar' => 'إدارة الاستحواذات', 'fr' => 'Gestion des acquisitions'],
        'icon' => 'fas fa-shopping-cart'
    ],
    'loans' => [
        'actions' => ['add', 'borrow', 'return', 'list'],
        'description' => ['ar' => 'إدارة الإعارات', 'fr' => 'Gestion des prêts'],
        'icon' => 'fas fa-handshake'
    ],
    'borrowers' => [
        'actions' => ['add', 'edit', 'list'],
        'description' => ['ar' => 'إدارة المستعيرين', 'fr' => 'Gestion des emprunteurs'],
        'icon' => 'fas fa-user-graduate'
    ],
    'administration' => [
        'actions' => ['add', 'edit', 'delete', 'list', 'permissions_guide'],
        'description' => ['ar' => 'إدارة الموظفين', 'fr' => 'Gestion des employés'],
        'icon' => 'fas fa-user-cog'
    ],
    'members' => [
        'actions' => ['add', 'edit', 'list'],
        'description' => ['ar' => 'إدارة الأعضاء', 'fr' => 'Gestion des membres'],
        'icon' => 'fas fa-users'
    ]
];

// التحقق من صحة الوحدة
if (!array_key_exists($module, $allowed_modules)) {
    $error_message = $lang == 'ar' ? 'الوحدة غير موجودة' : 'Module non trouvé';
    header("Location: error_handler.php?code=404&message=" . urlencode($error_message) . "&lang=" . $lang);
    exit;
}

// التحقق من صحة الإجراء
if (!in_array($action, $allowed_modules[$module]['actions'])) {
    $error_message = $lang == 'ar' ? 'الإجراء غير مسموح به' : 'Action non autorisée';
    header("Location: error_handler.php?code=403&message=" . urlencode($error_message) . "&lang=" . $lang);
    exit;
}

// بناء مسار الملف
$file_path = __DIR__ . '/../modules/' . $module . '/' . $action . '.php';

// التحقق من وجود الملف
if (!file_exists($file_path)) {
    $error_message = $lang == 'ar' ? 'الملف المطلوب غير موجود' : 'Le fichier demandé n\'existe pas';
    header("Location: error_handler.php?code=404&message=" . urlencode($error_message) . "&lang=" . $lang);
    exit;
}

// التحقق من إمكانية قراءة الملف
if (!is_readable($file_path)) {
    $error_message = $lang == 'ar' ? 'لا يمكن قراءة الملف المطلوب' : 'Le fichier demandé ne peut pas être lu';
    header("Location: error_handler.php?code=403&message=" . urlencode($error_message) . "&lang=" . $lang);
    exit;
}

// تسجيل الوصول
$log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - ACCESS to {$module}/{$action} - IP: {$_SERVER['REMOTE_ADDR']}\n";
file_put_contents(__DIR__ . '/../logs/access.log', $log_entry, FILE_APPEND | LOCK_EX);

// إعداد متغيرات إضافية
$module_info = $allowed_modules[$module];
$module_description = $module_info['description'][$lang];
$module_icon = $module_info['icon'];

// إضافة معلومات إضافية للجلسة
$_SESSION['current_module'] = $module;
$_SESSION['current_action'] = $action;
$_SESSION['current_lang'] = $lang;

// تضمين الملف
try {
    // إعداد متغيرات إضافية قبل التضمين
    $current_module = $module;
    $current_action = $action;
    $current_lang = $lang;
    $current_type = $type;
    
    // تضمين الملف
    include $file_path;
    
} catch (Exception $e) {
    // تسجيل الخطأ
    $error_log = date('Y-m-d H:i:s') . " - ERROR in {$module}/{$action}: " . $e->getMessage() . " - User: {$_SESSION['uid']}\n";
    file_put_contents(__DIR__ . '/../logs/error.log', $error_log, FILE_APPEND | LOCK_EX);
    
    // توجيه إلى صفحة الخطأ
    $error_message = $lang == 'ar' ? 'حدث خطأ أثناء تحميل الصفحة' : 'Une erreur s\'est produite lors du chargement de la page';
    header("Location: error_handler.php?code=500&message=" . urlencode($error_message) . "&lang=" . $lang);
    exit;
} catch (Error $e) {
    // تسجيل الخطأ
    $error_log = date('Y-m-d H:i:s') . " - FATAL ERROR in {$module}/{$action}: " . $e->getMessage() . " - User: {$_SESSION['uid']}\n";
    file_put_contents(__DIR__ . '/../logs/error.log', $error_log, FILE_APPEND | LOCK_EX);
    
    // توجيه إلى صفحة الخطأ
    $error_message = $lang == 'ar' ? 'حدث خطأ خطير في النظام' : 'Une erreur fatale s\'est produite dans le système';
    header("Location: error_handler.php?code=500&message=" . urlencode($error_message) . "&lang=" . $lang);
    exit;
}
?> 