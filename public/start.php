<?php
/**
 * FICHIER DE DÉMARRAGE - SYSTÈME DE GESTION DE BIBLIOTHÈQUE
 * Redirection automatique vers la page appropriée
 */

// Configuration de base
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Redirection automatique
header("Location: $destination");
exit;
?> 