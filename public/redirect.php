<?php
/**
 * Fichier de redirection pour corriger l'accès au système
 * Ce fichier redirige automatiquement vers la page appropriée
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Déterminer la page de destination
$destination = 'login.php';

// Si l'utilisateur est déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['uid'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'employee') {
        $destination = 'employee_dashboard.php';
    } else {
        $destination = 'dashboard.php';
    }
}

// Redirection
header("Location: $destination");
exit;
?> 