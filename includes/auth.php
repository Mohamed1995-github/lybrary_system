<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__.'/../config/db.php';

function t($key) {
    static $dict = null;
    if ($dict === null) {
        $lang = $_SESSION['lang'] ?? 'ar';
        $dict = require __DIR__."/../lang/{$lang}.php";
    }
    return $dict[$key] ?? $key;
}
?>
