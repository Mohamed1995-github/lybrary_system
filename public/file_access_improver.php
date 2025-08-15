<?php
/* public/file_access_improver.php - تحسين الوصول للملفات */
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'ar';
$file_type = $_GET['type'] ?? '';
$file_path = $_GET['path'] ?? '';

// تعريف أنواع الملفات المسموحة
$allowed_file_types = [
    'css' => [
        'path' => 'assets/css/',
        'description' => ['ar' => 'ملفات التصميم', 'fr' => 'Fichiers de style'],
        'icon' => 'fas fa-palette'
    ],
    'js' => [
        'path' => 'assets/js/',
        'description' => ['ar' => 'ملفات الجافا سكريبت', 'fr' => 'Fichiers JavaScript'],
        'icon' => 'fas fa-code'
    ],
    'image' => [
        'path' => 'assets/images/',
        'description' => ['ar' => 'الصور', 'fr' => 'Images'],
        'icon' => 'fas fa-image'
    ],
    'document' => [
        'path' => 'documents/',
        'description' => ['ar' => 'المستندات', 'fr' => 'Documents'],
        'icon' => 'fas fa-file-alt'
    ]
];

// التحقق من نوع الملف
if (!array_key_exists($file_type, $allowed_file_types)) {
    $error_message = $lang == 'ar' ? 'نوع الملف غير مسموح به' : 'Type de fichier non autorisé';
    header("Location: error_handler.php?code=403&message=" . urlencode($error_message) . "&lang=" . $lang);
    exit;
}

// بناء المسار الكامل
$base_path = $allowed_file_types[$file_type]['path'];
$full_path = __DIR__ . '/' . $base_path . $file_path;

// التحقق من وجود الملف
if (!file_exists($full_path)) {
    $error_message = $lang == 'ar' ? 'الملف المطلوب غير موجود' : 'Le fichier demandé n\'existe pas';
    header("Location: error_handler.php?code=404&message=" . urlencode($error_message) . "&lang=" . $lang);
    exit;
}

// التحقق من إمكانية قراءة الملف
if (!is_readable($full_path)) {
    $error_message = $lang == 'ar' ? 'لا يمكن قراءة الملف المطلوب' : 'Le fichier demandé ne peut pas être lu';
    header("Location: error_handler.php?code=403&message=" . urlencode($error_message) . "&lang=" . $lang);
    exit;
}

// الحصول على معلومات الملف
$file_info = pathinfo($full_path);
$file_size = filesize($full_path);
$file_modified = filemtime($full_path);

// تحديد نوع المحتوى
$content_types = [
    'css' => 'text/css',
    'js' => 'application/javascript',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'pdf' => 'application/pdf',
    'txt' => 'text/plain',
    'html' => 'text/html',
    'php' => 'text/plain'
];

$extension = strtolower($file_info['extension'] ?? '');
$content_type = $content_types[$extension] ?? 'application/octet-stream';

// تسجيل الوصول
$log_entry = date('Y-m-d H:i:s') . " - User: {$_SESSION['uid']} - FILE ACCESS: {$file_type}/{$file_path} - IP: {$_SERVER['REMOTE_ADDR']}\n";
file_put_contents(__DIR__ . '/../logs/file_access.log', $log_entry, FILE_APPEND | LOCK_EX);

// إرسال الملف
header('Content-Type: ' . $content_type);
header('Content-Length: ' . $file_size);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $file_modified) . ' GMT');
header('Cache-Control: public, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

// قراءة وإرسال الملف
readfile($full_path);
exit;
?> 