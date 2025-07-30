<?php
/* modules/loans/return.php */
require_once __DIR__.'/../../includes/auth.php';
$id = (int)($_GET['id']??0);

$pdo->beginTransaction();
// اجلب الإعارة مع قفل الصف
$loan = $pdo->prepare("SELECT * FROM loans WHERE id=? FOR UPDATE");
$loan->execute([$id]);
$l = $loan->fetch();
if(!$l || $l['return_date']) { $pdo->rollBack(); die('Invalid loan'); }

// إعادة النسخة إلى المخزون
$pdo->prepare("UPDATE items SET copies_in=copies_in+1 WHERE id=?")
    ->execute([$l['item_id']]);

// تحديث تاريخ الإرجاع
$pdo->prepare("UPDATE loans SET return_date=CURDATE() WHERE id=?")->execute([$id]);
$pdo->commit();
header('Location: list.php');
