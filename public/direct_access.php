<?php
/**
 * Accès direct aux pages d'ajout
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

// Obtenir les paramètres
$page = $_GET['page'] ?? 'add_book';
$lang = $_GET['lang'] ?? 'ar';

// Définir les pages autorisées
$allowed_pages = [
    'add_book' => '../modules/items/add_book.php',
    'add_magazine' => '../modules/items/add_magazine.php',
    'add_newspaper' => '../modules/items/add_newspaper.php',
    'list_books' => '../modules/items/list.php',
    'list_magazines' => '../modules/items/list.php',
    'list_newspapers' => '../modules/items/list.php'
];

// Vérifier si la page est autorisée
if (!isset($allowed_pages[$page])) {
    echo "<h1>Page non autorisée</h1>";
    echo "<p>Page demandée: $page</p>";
    echo "<p>Pages disponibles: " . implode(', ', array_keys($allowed_pages)) . "</p>";
    echo "<p><a href='dashboard.php'>Retour au tableau de bord</a></p>";
    exit;
}

// Construire le chemin du fichier
$file_path = __DIR__ . '/' . $allowed_pages[$page];

// Vérifier si le fichier existe
if (!file_exists($file_path)) {
    echo "<h1>Fichier non trouvé</h1>";
    echo "<p>Fichier demandé: $file_path</p>";
    echo "<p><a href='dashboard.php'>Retour au tableau de bord</a></p>";
    exit;
}

// Inclure le fichier
include $file_path;
?> 