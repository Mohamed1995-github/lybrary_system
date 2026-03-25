<?php
function require_login(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['uid'])) {
        header('Location: ../login.php');
        exit;
    }
}
