<?php
/**
 * Routeur simple pour tester l'accès aux fichiers
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtenir les paramètres
$module = $_GET['module'] ?? 'items';
$action = $_GET['action'] ?? 'list';
$lang = $_GET['lang'] ?? 'ar';
$type = $_GET['type'] ?? 'book';

echo "<h1>Routeur Simple - Test</h1>";
echo "<p>Module: $module</p>";
echo "<p>Action: $action</p>";
echo "<p>Lang: $lang</p>";
echo "<p>Type: $type</p>";

// Construire le chemin du fichier
$file_path = __DIR__ . "/../modules/{$module}/{$action}.php";

echo "<p>Chemin du fichier: $file_path</p>";
echo "<p>Fichier existe: " . (file_exists($file_path) ? 'OUI' : 'NON') . "</p>";

// Si le fichier existe, l'inclure
if (file_exists($file_path)) {
    echo "<hr><h2>Contenu du fichier :</h2>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
    
    try {
        // Inclure le fichier
        include $file_path;
    } catch (Exception $e) {
        echo "<p style='color: red;'>Erreur lors de l'inclusion: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
} else {
    echo "<p style='color: red;'>Le fichier n'existe pas !</p>";
}

// Liens de test
echo "<hr><h2>Liens de Test :</h2>";
echo '<a href="simple_router.php?module=items&action=list&type=book&lang=ar">Liste Livres</a><br>';
echo '<a href="simple_router.php?module=items&action=add_magazine&lang=ar">Ajout Magazine</a><br>';
echo '<a href="simple_router.php?module=items&action=add_book&lang=ar">Ajout Livre</a><br>';
echo '<a href="simple_router.php?module=acquisitions&action=list&lang=ar">Liste Acquisitions</a><br>';
echo '<a href="simple_router.php?module=borrowers&action=list&lang=ar">Liste Emprunteurs</a><br>';
?> 