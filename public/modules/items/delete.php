<?php
/* modules/items/delete.php */
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($_SESSION['uid'])) { header('Location: ../../public/login.php'); exit; }

$id = $_GET['id'] ?? null;
$lang = $_GET['lang'] ?? 'ar';
$type = $_GET['type'] ?? 'book';

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$id]);
        
        // إعادة التوجيه إلى قائمة العناصر مع الحفاظ على اللغة
        header("Location: ../../public/router.php?module=items&action=list&lang={$lang}&type={$type}&success=deleted");
    } catch (PDOException $e) {
        // في حالة الخطأ، إعادة التوجيه مع رسالة خطأ
        header("Location: ../../public/router.php?module=items&action=list&lang={$lang}&type={$type}&error=delete_failed");
    }
} else {
    // إذا لم يتم توفير ID، إعادة التوجيه إلى القائمة
    header("Location: ../../public/router.php?module=items&action=list&lang={$lang}&type={$type}&error=no_id");
}
exit;
