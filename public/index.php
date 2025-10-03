<?php
/**
 * Page d'accueil principale - Redirection automatique
 * Main index page - Auto redirect
 */

session_start();

// Si l'utilisateur est déjà connecté, aller au dashboard
if (isset($_SESSION['uid'])) {
    $lang = $_SESSION['lang'] ?? 'ar';
    header('Location: dashboard.php?lang=' . $lang);
    exit;
}

// Sinon, aller à la page de connexion
header('Location: employee_login.php');
exit;
?>